<?php
error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar errores en HTML
ini_set('log_errors', 1); // Loguear errores

require_once("include/db.php");
require_once("include/configuracionesAD.php");

$db = new Database();
$conexion = $db->getConexion();

// Obtener el ID del producto a editar
$producto_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($producto_id <= 0) {
    echo "<script>alert('Producto no encontrado'); window.location.href='index.php?mod=GestionarProductos';</script>";
    exit;
}

// Obtener datos del producto
$sqlProducto = "SELECT p.*, c.nombre as categoria_nombre FROM productos p 
                LEFT JOIN caracteristicas c ON p.id_categoria = c.id 
                WHERE p.id = :id";
$stmtProducto = $conexion->prepare($sqlProducto);
$stmtProducto->execute([':id' => $producto_id]);
$producto = $stmtProducto->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    echo "<script>alert('Producto no encontrado'); window.location.href='index.php?mod=GestionarProductos';</script>";
    exit;
}

// Obtener imágenes adicionales
$sqlImagenes = "SELECT id, imagen FROM producto_imagenes WHERE producto_id = :id";
$stmtImagenes = $conexion->prepare($sqlImagenes);
$stmtImagenes->execute([':id' => $producto_id]);
$imagenes_adicionales = $stmtImagenes->fetchAll(PDO::FETCH_ASSOC);

// Obtener categorías
$sqlCategorias = "SELECT id, nombre FROM caracteristicas WHERE activo = 1 ORDER BY nombre";
$categorias = $conexion->query($sqlCategorias)->fetchAll(PDO::FETCH_ASSOC);

// Obtener tallas y colores
$resultado = $conexion->query("SELECT id, nombre FROM c_tallas");
$tallas = $resultado->fetchAll(PDO::FETCH_ASSOC);

$resultado = $conexion->query("SELECT id, nombre FROM c_colores");
$colores = $resultado->fetchAll(PDO::FETCH_ASSOC);

// Obtener variantes existentes
$sqlVariantes = "SELECT id, id_talla, id_color, precio, stock FROM productos_variantes WHERE id_producto = :id";
$stmtVariantes = $conexion->prepare($sqlVariantes);
$stmtVariantes->execute([':id' => $producto_id]);
$variantes_existentes = $stmtVariantes->fetchAll(PDO::FETCH_ASSOC);

// Procesar formulario de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'editar') {
    
    $titulo       = $_POST['titulo'];
    $descripcion  = $_POST['descripcion'];
    $precio       = floatval($_POST['precio']);
    $descuento    = isset($_POST['descuento']) ? intval($_POST['descuento']) : 0;
    $stock        = isset($_POST['stock']) ? intval($_POST['stock']) : 0;
    $id_categoria = isset($_POST['id_categoria']) ? intval($_POST['id_categoria']) : 0;
    $activo       = $_POST['activo'];

    try {
        $conexion->beginTransaction();

        // Validación básica
        if (empty($titulo) || $precio <= 0) {
            throw new Exception("El título y precio son requeridos.");
        }

        // Actualizar datos básicos del producto
        $sqlUpdate = "UPDATE productos SET titulo = :titulo, descripcion = :descripcion, precio = :precio, 
                      descuento = :descuento, stock = :stock, id_categoria = :id_categoria, activo = :activo 
                      WHERE id = :id";
        $stmtUpdate = $conexion->prepare($sqlUpdate);
        $stmtUpdate->execute([
            ':titulo'       => $titulo,
            ':descripcion'  => $descripcion,
            ':precio'       => $precio,
            ':descuento'    => $descuento,
            ':stock'        => $stock,
            ':id_categoria' => $id_categoria,
            ':activo'       => $activo,
            ':id'           => $producto_id
        ]);

        // Procesar imagen principal (si se carga una nueva)
        $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $tamañoMaximo = 2 * 1024 * 1024; // 2MB
        $rutaBase = "../assets/img/";

        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $tipo = $_FILES['imagen']['type'];
            $tamano = $_FILES['imagen']['size'];

            if (!in_array($tipo, $tiposPermitidos)) {
                throw new Exception("Tipo de imagen no permitido. Solo se aceptan: JPG, PNG, GIF, WEBP");
            }
            
            if ($tamano > $tamañoMaximo) {
                throw new Exception("La imagen es demasiado grande. Máximo 2MB.");
            }

            // Eliminar imagen anterior si existe
            if (!empty($producto['imagen']) && file_exists($rutaBase . $producto['imagen'])) {
                @unlink($rutaBase . $producto['imagen']);
            }

            // Guardar nueva imagen
            $nombreOriginal = basename($_FILES['imagen']['name']);
            $nombreUnico = uniqid('main_') . '-' . $nombreOriginal;
            $rutaFinal = $rutaBase . $nombreUnico;

            if (@move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaFinal)) {
                $sqlImg = "UPDATE productos SET imagen = :imagen WHERE id = :id";
                $stmtImg = $conexion->prepare($sqlImg);
                $stmtImg->execute([':imagen' => $nombreUnico, ':id' => $producto_id]);
            } else {
                throw new Exception("Error al guardar la imagen principal. Verifica los permisos de la carpeta.");
            }
        }

        // Procesar imágenes adicionales (si se cargan nuevas)
        if (isset($_FILES['imagenes']) && is_array($_FILES['imagenes']['tmp_name'])) {
            foreach ($_FILES['imagenes']['tmp_name'] as $key => $tmpName) {
                if (empty($tmpName) || $_FILES['imagenes']['error'][$key] !== UPLOAD_ERR_OK) {
                    continue;
                }
                
                $nombreOriginal = basename($_FILES['imagenes']['name'][$key]);
                $tipo           = $_FILES['imagenes']['type'][$key];
                $tamano         = $_FILES['imagenes']['size'][$key];

                if (!in_array($tipo, $tiposPermitidos)) continue;
                if ($tamano > $tamañoMaximo) continue;

                $nombreUnico = uniqid('extra_') . '-' . $nombreOriginal;
                $rutaFinal   = $rutaBase . $nombreUnico;

                if (@move_uploaded_file($tmpName, $rutaFinal)) {
                    $sqlImg = "INSERT INTO producto_imagenes (producto_id, imagen) VALUES (:producto_id, :imagen)";
                    $stmtImg = $conexion->prepare($sqlImg);
                    $stmtImg->execute([
                        ':producto_id' => $producto_id,
                        ':imagen'      => $nombreUnico
                    ]);
                }
            }
        }

        // Procesar variantes
        $talla = isset($_POST['talla']) && is_array($_POST['talla']) ? $_POST['talla'] : [];
        $color = isset($_POST['color']) && is_array($_POST['color']) ? $_POST['color'] : [];
        $precioVariante = isset($_POST['precio_variante']) && is_array($_POST['precio_variante']) ? $_POST['precio_variante'] : [];
        $stockVariante = isset($_POST['stock_variante']) && is_array($_POST['stock_variante']) ? $_POST['stock_variante'] : [];
        $idVariante = isset($_POST['id_variante']) && is_array($_POST['id_variante']) ? $_POST['id_variante'] : [];

        // Obtener IDs de variantes existentes para actualizaciones
        $sizeTalla = count($talla);
        
        if ($sizeTalla > 0) {
            for ($i = 0; $i < $sizeTalla; $i++) {
                $varianteId = !empty($idVariante[$i]) ? intval($idVariante[$i]) : 0;
                $tallaId = !empty($talla[$i]) ? intval($talla[$i]) : 0;
                $colorId = !empty($color[$i]) ? intval($color[$i]) : 0;
                $precioV = !empty($precioVariante[$i]) ? floatval($precioVariante[$i]) : 0;
                $stockV = !empty($stockVariante[$i]) ? intval($stockVariante[$i]) : 0;

                if ($tallaId > 0 && $colorId > 0) {
                    if ($varianteId > 0) {
                        // Actualizar variante existente
                        $sqlUpVar = "UPDATE productos_variantes SET id_talla = :id_talla, id_color = :id_color, 
                                     precio = :precio, stock = :stock WHERE id = :id";
                        $stmtUpVar = $conexion->prepare($sqlUpVar);
                        $stmtUpVar->execute([
                            ':id_talla'  => $tallaId,
                            ':id_color'  => $colorId,
                            ':precio'    => $precioV,
                            ':stock'     => $stockV,
                            ':id'        => $varianteId
                        ]);
                    } else {
                        // Insertar nueva variante
                        $sqlNewVar = "INSERT INTO productos_variantes (id_producto, id_talla, id_color, precio, stock) 
                                      VALUES (:id_producto, :id_talla, :id_color, :precio, :stock)";
                        $stmtNewVar = $conexion->prepare($sqlNewVar);
                        $stmtNewVar->execute([
                            ':id_producto' => $producto_id,
                            ':id_talla'    => $tallaId,
                            ':id_color'    => $colorId,
                            ':precio'      => $precioV,
                            ':stock'       => $stockV
                        ]);
                    }
                }
            }
        }

        // Eliminar variantes marcadas para eliminar
        $variantesEliminar = isset($_POST['variante_eliminar']) && is_array($_POST['variante_eliminar']) ? $_POST['variante_eliminar'] : [];
        foreach ($variantesEliminar as $idVar) {
            $sqlDelVar = "DELETE FROM productos_variantes WHERE id = :id";
            $stmtDelVar = $conexion->prepare($sqlDelVar);
            $stmtDelVar->execute([':id' => intval($idVar)]);
        }

        $conexion->commit();
        echo "<script>alert('Producto actualizado correctamente'); window.location.href='index.php?mod=GestionarProductos';</script>";
        exit;

    } catch (Exception $e) {
        $conexion->rollBack();
        $msg = "Error al actualizar: " . $e->getMessage();
        error_log($msg); // Loguear en el archivo de log de PHP
        echo "<script>alert(" . json_encode($msg) . "); window.history.back();</script>";
        exit;
    }
}

// Procesar eliminación de imagen adicional
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'eliminar_imagen') {
    $id_imagen = intval($_POST['id_imagen']);
    
    // Obtener la imagen a eliminar
    $sqlImg = "SELECT imagen FROM producto_imagenes WHERE id = :id";
    $stmtImg = $conexion->prepare($sqlImg);
    $stmtImg->execute([':id' => $id_imagen]);
    $imagen = $stmtImg->fetchColumn();
    
    if ($imagen && file_exists("../assets/img/" . $imagen)) {
        unlink("../assets/img/" . $imagen);
    }
    
    $sqlDel = "DELETE FROM producto_imagenes WHERE id = :id";
    $stmtDel = $conexion->prepare($sqlDel);
    $stmtDel->execute([':id' => $id_imagen]);
    
    // Responder OK para la petición AJAX
    http_response_code(200);
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Editar Producto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- CKEditor 5 CDN -->
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <!-- Spartan Multi Image Picker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/spartan-multi-image-picker/dist/css/spartan-multi-image-picker.min.css">
    <script src="https://cdn.jsdelivr.net/npm/spartan-multi-image-picker/dist/js/spartan-multi-image-picker.min.js"></script>

    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding-bottom: 50px;
        }

        .header-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 10px;
            margin-bottom: 40px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .header-section h1 {
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 2.5rem;
        }

        .header-section p {
            margin-bottom: 0;
            font-size: 1.05rem;
            opacity: 0.95;
        }

        .form-container {
            background: white;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .form-section {
            margin-bottom: 35px;
            padding-bottom: 25px;
            border-bottom: 2px solid #f0f0f0;
        }

        .form-section:last-child {
            border-bottom: none;
        }

        .form-section-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 10px;
            display: block;
        }

        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 15px;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .form-text {
            font-size: 0.85rem;
            color: #7f8c8d;
            margin-top: 5px;
        }

        .row {
            margin-bottom: 15px;
        }

        .row .col {
            margin-bottom: 0;
        }

        /* Contenedor horizontal con scroll para las imágenes */
        #multi_image_picker {
            display: flex !important;
            overflow-x: auto;
            gap: 10px;
            padding: 15px;
            max-width: 100%;
            background: #f8f9fa;
            border-radius: 8px;
            border: 2px dashed #667eea;
            min-height: 140px;
            align-items: flex-start;
        }

        /* Para que cada imagen tenga un tamaño fijo y se vea bien */
        #multi_image_picker .spartan_item {
            flex: 0 0 auto;
            width: 120px;
            height: 120px;
        }
        
        .ck-editor__editable[role="textbox"] {
            min-height: 250px;
            border-radius: 8px;
        }
        
        /* Contenedor para imágenes existentes */
        .imagenes-existentes {
            display: flex;
            overflow-x: auto;
            gap: 10px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 2px solid #e9ecef;
        }
        
        .imagen-item {
            position: relative;
            flex: 0 0 auto;
            width: 120px;
            height: 120px;
        }
        
        .imagen-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #dee2e6;
            transition: transform 0.3s ease;
        }

        .imagen-item img:hover {
            transform: scale(1.05);
        }
        
        .imagen-item .btn-eliminar {
            position: absolute;
            top: -8px;
            right: -8px;
            width: 30px;
            height: 30px;
            padding: 0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            background: #e74c3c;
            border: none;
            color: white;
            transition: all 0.3s ease;
        }

        .imagen-item .btn-eliminar:hover {
            background: #c0392b;
            transform: scale(1.1);
        }
        
        .preview-principal {
            max-width: 200px;
            margin: 10px 0;
            border-radius: 8px;
            border: 2px solid #dee2e6;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .btn-primary, .btn-success, .btn-secondary {
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            margin-right: 10px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .btn-secondary {
            background: #95a5a6;
            color: white;
        }

        .btn-secondary:hover {
            background: #7f8c8d;
            transform: translateY(-3px);
            color: white;
        }

        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            color: #667eea;
            font-weight: 600;
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 2px solid #f0f0f0;
        }

        .variant-item {
            border-left: 4px solid #667eea !important;
            transition: all 0.3s ease;
        }

        .variant-item:hover {
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.2);
        }

        #contenido-variantes {
            min-height: 80px;
            max-height: 600px;
            overflow-y: auto;
        }

        #contenido-variantes .variant-item {
            margin-bottom: 0;
        }

        @media (max-width: 768px) {
            .header-section {
                padding: 25px;
            }

            .header-section h1 {
                font-size: 1.8rem;
            }

            .form-container {
                padding: 20px;
            }

            .form-section-title {
                font-size: 1.1rem;
            }

            .button-group {
                flex-direction: column;
            }

            .button-group .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="container mt-5 mb-5">
    
    <!-- HEADER -->
    <div class="header-section">
        <h1><i class="bi bi-pencil-square"></i> Editar Producto</h1>
        <p>Actualiza los datos del producto: <strong><?= htmlspecialchars($producto['titulo']) ?></strong></p>
    </div>

    <!-- FORMULARIO -->
    <div class="form-container">
        <form method="POST" enctype="multipart/form-data" id="formProducto" autocomplete="off">
            <input type="hidden" name="accion" value="editar">
            
            <!-- INFORMACIÓN BÁSICA -->
            <div class="form-section">
                <h6 class="form-section-title"><i class="bi bi-info-circle"></i> Información Básica</h6>
                
                <label class="form-label">Título del Producto *</label>
                <input type="text" class="form-control" name="titulo" id="titulo" value="<?= htmlspecialchars($producto['titulo']) ?>" required />
                 
                <label class="form-label mt-3">Descripción</label>
                <textarea class="form-control" name="descripcion"><?= htmlspecialchars($producto['descripcion']) ?></textarea>
            </div>

            <!-- PRECIOS Y STOCK -->
            <div class="form-section">
                <h6 class="form-section-title"><i class="bi bi-cash-coin"></i> Precios y Stock</h6>
                
                <div class="row">
                    <div class="col mb-3">
                        <label class="form-label">Precio *</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" name="precio" id="precio" value="<?= $producto['precio'] ?>" step="0.01" required />
                        </div>
                    </div>
                    <div class="col mb-3">
                        <label class="form-label">Descuento (%)</label>
                        <input type="number" class="form-control" name="descuento" id="descuento" value="<?= $producto['descuento'] ?>" max="100" />
                        <small class="form-text">Máximo 100%</small>
                    </div>
                </div>

                <label class="form-label">Stock</label>
                <input type="number" class="form-control" name="stock" id="stock" value="<?= $producto['stock'] ?>" />
            </div>

            <!-- IMÁGENES -->
            <div class="form-section">
                <h6 class="form-section-title"><i class="bi bi-image"></i> Imágenes</h6>
                
                <!-- Imagen principal actual -->
                <label class="form-label">Imagen Principal Actual:</label>
                <?php if ($producto['imagen'] && file_exists("../assets/img/" . $producto['imagen'])): ?>
                    <div>
                        <img src="../assets/img/<?= $producto['imagen'] ?>" class="preview-principal" alt="Principal">
                    </div>
                <?php else: ?>
                    <p class="text-muted"><i class="bi bi-exclamation-circle"></i> Sin imagen principal</p>
                <?php endif; ?>

                <!-- Cambiar imagen principal -->
                <label class="form-label mt-3">Cambiar Imagen Principal (opcional)</label>
                <input type="file" class="form-control" name="imagen" id="imagen" accept="image/*" />
                <small class="form-text">Formatos: JPG, PNG, GIF, WEBP. Máximo: 2MB</small>

                <!-- Imágenes adicionales actuales -->
                <?php if (!empty($imagenes_adicionales)): ?>
                <div class="mt-4">
                    <label class="form-label">Imágenes Adicionales Actuales:</label>
                    <div class="imagenes-existentes">
                        <?php foreach ($imagenes_adicionales as $img): ?>
                        <div class="imagen-item">
                            <img src="../assets/img/<?= htmlspecialchars($img['imagen']) ?>" alt="Extra">
                            <button type="button" class="btn btn-eliminar" onclick="eliminarImagenExtra(<?= $img['id'] ?>)" title="Eliminar imagen">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Agregar más imágenes adicionales -->
                <label class="form-label mt-3">Agregar Más Imágenes Adicionales (opcional)</label>
                <div id="multi_image_picker"></div>
                <small class="form-text">Arrastra o haz clic para agregar más imágenes</small>
            </div>

            <!-- CATEGORÍA Y ESTADO -->
            <div class="form-section">
                <h6 class="form-section-title"><i class="bi bi-tag"></i> Categoría y Estado</h6>
                
                <label class="form-label">Categoría</label>
                <select class="form-select" name="id_categoria" id="id_categoria">
                    <option value="">-- Seleccionar Categoría --</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $producto['id_categoria'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

            </div>

            <!-- VARIANTES -->
            <div class="form-section">
                <h6 class="form-section-title"><i class="bi bi-diagram-3"></i> Variantes Producto</h6>
                <p class="text-muted">Agrega o edita combinaciones de talla y color con sus precios y stocks</p>
                
                <button type="button" class="btn btn-primary mb-3" id="agregarVariante">
                    <i class="bi bi-plus-circle"></i> Agregar Variante
                </button>

                <div id="contenido-variantes" class="bg-light p-3 rounded">
                    <!-- Variantes existentes -->
                    <?php if (!empty($variantes_existentes)): ?>
                        <?php foreach ($variantes_existentes as $variante): ?>
                        <div class="row mb-3 p-3 bg-white border rounded variant-item">
                            <input type="hidden" name="id_variante[]" value="<?= $variante['id'] ?>">
                            <div class="col-lg-3">
                                <label class="form-label">Talla *</label>
                                <select class="form-select" name="talla[]" required>
                                   <option value="">-- Seleccionar Talla --</option>
                                    <?php foreach ($tallas as $talla): ?>
                                        <option value="<?= $talla['id'] ?>" <?= $talla['id'] == $variante['id_talla'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($talla['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-lg-3">
                                <label class="form-label">Color *</label>
                                <select class="form-select" name="color[]" required>
                                    <option value="">-- Seleccionar Color --</option>
                                        <?php foreach ($colores as $color): ?>
                                            <option value="<?= $color['id'] ?>" <?= $color['id'] == $variante['id_color'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($color['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-lg-2">
                                <label class="form-label">Precio ($)</label>
                                <input type="number" class="form-control" name="precio_variante[]" min="0" step="0.01" placeholder="0.00" value="<?= $variante['precio'] ?>" required />
                            </div>
                            <div class="col-lg-2">
                                <label class="form-label">Stock</label>
                                <input type="number" class="form-control" name="stock_variante[]" min="0" placeholder="0" value="<?= $variante['stock'] ?>" required />
                            </div>
                            <div class="col-lg-2 d-flex align-items-end">
                                <button type="button" class="btn btn-danger btn-sm w-100" onclick="marcarParaEliminar(this, <?= $variante['id'] ?>)">
                                    <i class="bi bi-trash"></i> Eliminar
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <template id="plantilla_Variante">
                    <div class="row mb-3 p-3 bg-white border rounded variant-item">
                        <input type="hidden" name="id_variante[]" value="">
                        <div class="col-lg-3">
                            <label class="form-label">Talla *</label>
                            <select class="form-select" name="talla[]" required>
                               <option value="">-- Seleccionar Talla --</option>
                                <?php foreach ($tallas as $talla): ?>
                                    <option value="<?= $talla['id'] ?>"><?= htmlspecialchars($talla['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label">Color *</label>
                            <select class="form-select" name="color[]" required>
                                <option value="">-- Seleccionar Color --</option>
                                    <?php foreach ($colores as $color): ?>
                                        <option value="<?= $color['id'] ?>"><?= htmlspecialchars($color['nombre']) ?></option>
                                    <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-lg-2">
                            <label class="form-label">Precio ($)</label>
                            <input type="number" class="form-control" name="precio_variante[]" min="0" step="0.01" placeholder="0.00" value="0" required />
                        </div>
                        <div class="col-lg-2">
                            <label class="form-label">Stock</label>
                            <input type="number" class="form-control" name="stock_variante[]" min="0" placeholder="0" value="0" required />
                        </div>
                        <div class="col-lg-2 d-flex align-items-end">
                            <button type="button" class="btn btn-danger btn-sm w-100" onclick="this.closest('.variant-item').remove()">
                                <i class="bi bi-trash"></i> Eliminar
                            </button>
                        </div>
                    </div>    
                </template>
            </div>

             <label class="form-label mt-3">Estado del Producto</label>
                <select class="form-select" name="activo" id="activo" required>
                    <option value="1" <?= $producto['activo'] == 1 ? 'selected' : '' ?>>Disponible para la venta</option>
                    <option value="0" <?= $producto['activo'] == 0 ? 'selected' : '' ?>>Agotado</option>
                </select>

            <!-- BOTONES -->
            <div class="button-group">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Actualizar Producto
                </button>
                <a href="index.php?mod=GestionarProductos" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Cancelar
                </a>
            </div>
        </form>
    </div>

</div>

<script>
    function eliminarImagenExtra(idImagen) {
        if (!confirm('¿Eliminar esta imagen?')) return;
        
        const formData = new FormData();
        formData.append('accion', 'eliminar_imagen');
        formData.append('id_imagen', idImagen);
        
        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (response.ok) {
                alert('Imagen eliminada');
                location.reload();
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function marcarParaEliminar(btn, idVariante) {
        const row = btn.closest('.variant-item');
        const input = row.querySelector('input[type="hidden"]');
        
        // Crear input oculto para marcar eliminación
        const inputEliminar = document.createElement('input');
        inputEliminar.type = 'hidden';
        inputEliminar.name = 'variante_eliminar[]';
        inputEliminar.value = idVariante;
        document.getElementById('formProducto').appendChild(inputEliminar);
        
        // Remover fila
        row.remove();
    }

    $(document).ready(function() {
        $("#multi_image_picker").spartanMultiImagePicker({
            fieldName: 'imagenes[]',
            maxCount: 10,
            rowHeight: '120px',
            groupClassName: 'spartan_item',
            allowedExt: 'png|jpg|jpeg',
            dropFileLabel: "Arrastra o haz clic aquí para agregar más imágenes",
        });

        // Validar formulario antes de enviar
        document.getElementById('formProducto').addEventListener('submit', function(e) {
            const variantItems = document.querySelectorAll('#contenido-variantes .variant-item');
            let variantsValid = false;

            variantItems.forEach(item => {
                const talla = item.querySelector('select[name="talla[]"]').value;
                const color = item.querySelector('select[name="color[]"]').value;
                const precio = parseFloat(item.querySelector('input[name="precio_variante[]"]').value) || 0;
                const stock = parseInt(item.querySelector('input[name="stock_variante[]"]').value) || 0;

                if (talla && color && (precio > 0 || stock > 0)) {
                    variantsValid = true;
                }
            });

            if (variantItems.length > 0 && !variantsValid) {
                e.preventDefault();
                alert('⚠️ Por favor completa al menos una variante con Talla, Color y (Precio o Stock)');
                return false;
            }
        });

        // Agregar variante
        const btnVariante = document.getElementById('agregarVariante');
        if (btnVariante) {
            btnVariante.addEventListener('click', function() {
                const plantilla = document.getElementById('plantilla_Variante');
                const contenido = document.getElementById('contenido-variantes');
                const clone = plantilla.content.cloneNode(true);
                contenido.appendChild(clone);
            });
        }
    });
</script>

</body>
</html>
