<?php
require_once("include/db.php");
require_once("include/configuracionesAD.php");
require_once(dirname(dirname(__DIR__)) . '/include/validaciones.php');

$db = new Database();
$conexion = $db->getConexion();
  
// Obtener todas las categorías activas
$sql = "SELECT id, nombre FROM caracteristicas WHERE activo = 1";
$resultado = $conexion->query($sql);
$categorias = $resultado->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Datos del formulario - Sanitización y validación
    $titulo       = ValidadorFormularios::limpiar($_POST['titulo'] ?? '');
    $descripcion  = ValidadorFormularios::limpiar($_POST['descripcion'] ?? '');
    $precio       = floatval($_POST['precio'] ?? 0);
    $descuento    = intval($_POST['descuento'] ?? 0);
    $stock        = intval($_POST['stock'] ?? 0);
    $id_categoria = intval($_POST['categoria'] ?? 0);
    $activo       = intval($_POST['activo'] ?? 1);
    
    // Validaciones del lado servidor
    $errores = [];
    
    // Validar título
    $val_titulo = ValidadorFormularios::validar($titulo, 'titulo');
    if ($val_titulo !== true) {
        $errores[] = $val_titulo['mensaje'];
    }
    
    // Validar descripción si existe
    if (!empty($descripcion)) {
        $val_desc = ValidadorFormularios::validar($descripcion, 'descripcion');
        if ($val_desc !== true) {
            $errores[] = $val_desc['mensaje'];
        }
    }
    
    // Validar precio
    if ($precio <= 0) {
        $errores[] = "El precio debe ser mayor a 0";
    } else {
        $val_precio = ValidadorFormularios::validar($precio, 'precio');
        if ($val_precio !== true) {
            $errores[] = $val_precio['mensaje'];
        }
    }
    
    // Validar descuento
    if ($descuento < 0 || $descuento > 100) {
        $errores[] = "El descuento debe estar entre 0 y 100";
    }
    
    // Validar stock
    if ($stock < 0) {
        $errores[] = "El stock no puede ser negativo";
    }
    
    // Validar categoría
    if ($id_categoria <= 0) {
        $errores[] = "Debe seleccionar una categoría";
    }
    
    if (!empty($errores)) {
        echo "<script>alert('Error de validación:\\n" . implode("\\n", $errores) . "'); window.location.href='index.php?mod=CrearProductos';</script>";
        exit;
    }


    try {
        $conexion->beginTransaction();

        // Procesar imagen principal
        $imagenPrincipalNombre = 'noadd.jpg'; // Valor por defecto
        $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $tamañoMaximo = 2 * 1024 * 1024; // 2MB
        $rutaBase = "../assets/img/";

        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
            $tipo = $_FILES['imagen']['type'];
            $tamano = $_FILES['imagen']['size'];

            if (in_array($tipo, $tiposPermitidos) && $tamano <= $tamañoMaximo) {
                $nombreOriginal = $_FILES['imagen']['name'];
                $nombreUnico = uniqid('main_') . '-' . basename($nombreOriginal);
                $rutaFinal = $rutaBase . $nombreUnico;

                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaFinal)) {
                    $imagenPrincipalNombre = $nombreUnico;
                }
            }
        }

        // Insertar producto con imagen principal y descuento
        $sql = "INSERT INTO productos (titulo, descripcion, precio, descuento, stock, id_categoria, activo, imagen) 
                VALUES (:titulo, :descripcion, :precio, :descuento, :stock, :id_categoria, :activo, :imagen)";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':titulo'       => $titulo,
            ':descripcion'  => $descripcion,
            ':precio'       => $precio,
            ':descuento'    => $descuento,
            ':stock'        => $stock,
            ':id_categoria' => $id_categoria,
            ':activo'       => $activo,
            ':imagen'       => $imagenPrincipalNombre
        ]);

        $producto_id = $conexion->lastInsertId(); // ID del nuevo producto

        // Insertar imágenes adicionales
        if (!empty($_FILES['imagenes']['name'][0])) {
            foreach ($_FILES['imagenes']['tmp_name'] as $key => $tmpName) {
                $nombreOriginal = $_FILES['imagenes']['name'][$key];
                $tipo           = $_FILES['imagenes']['type'][$key];
                $tamano         = $_FILES['imagenes']['size'][$key];

                if (!in_array($tipo, $tiposPermitidos)) continue;
                if ($tamano > $tamañoMaximo) continue;

                $nombreUnico = uniqid('ropa_') . '-' . basename($nombreOriginal);
                $rutaFinal   = $rutaBase . $nombreUnico;

                if (move_uploaded_file($tmpName, $rutaFinal)) {
                    $sqlImg = "INSERT INTO producto_imagenes (producto_id, imagen) VALUES (:producto_id, :imagen)";
                    $stmtImg = $conexion->prepare($sqlImg);
                    $stmtImg->execute([
                        ':producto_id' => $producto_id,
                        ':imagen'      => $nombreUnico
                    ]);
                }
            }
        }

        // Insertar variantes
        $talla = isset($_POST['talla']) && is_array($_POST['talla']) ? $_POST['talla'] : [];
        $color = isset($_POST['color']) && is_array($_POST['color']) ? $_POST['color'] : [];
        $precioVariante = isset($_POST['precio_variante']) && is_array($_POST['precio_variante']) ? $_POST['precio_variante'] : [];
        $stockVariante = isset($_POST['stock_variante']) && is_array($_POST['stock_variante']) ? $_POST['stock_variante'] : [];
        
        $sizeTalla = count($talla);
        $variantesInsertadas = 0;
        
        if ($sizeTalla > 0) {
            for ($i = 0; $i < $sizeTalla; $i++) {
                // Obtener valores con validación
                $tallaId = !empty($talla[$i]) ? intval($talla[$i]) : 0;
                $colorId = !empty($color[$i]) ? intval($color[$i]) : 0;
                $precioV = !empty($precioVariante[$i]) ? floatval($precioVariante[$i]) : 0;
                $stockV = !empty($stockVariante[$i]) ? intval($stockVariante[$i]) : 0;

                // Validar que tenga talla y color seleccionados
                if ($tallaId > 0 && $colorId > 0) {
                    try {
                        $sqlVariante = "INSERT INTO productos_variantes (id_producto, id_talla, id_color, precio, stock) 
                                        VALUES (:id_producto, :id_talla, :id_color, :precio, :stock)";
                        $stmtVariante = $conexion->prepare($sqlVariante);
                        $stmtVariante->execute([
                            ':id_producto' => $producto_id,
                            ':id_talla'    => $tallaId,
                            ':id_color'    => $colorId,
                            ':precio'      => $precioV,
                            ':stock'       => $stockV
                        ]);
                        $variantesInsertadas++;
                    } catch (Exception $varianteError) {
                        error_log("Error insertando variante: " . $varianteError->getMessage());
                    }
                }
            }
        }



        $conexion->commit();
        // Notificar al usuario y redirigir
        echo "<script>alert(" . json_encode("Producto creado correctamente") . "); window.location.href='index.php?mod=CrearProductos';</script>";
        exit;
    } catch (Exception $e) {
        $conexion->rollBack();
        $msg = "Error al agregar el producto: " . $e->getMessage();
        echo "<script>alert(" . json_encode($msg) . "); window.location.href='index.php?mod=CrearProductos';</script>";
        exit;
    }
}

 
$resultado = $conexion->query("SELECT id, nombre FROM c_tallas");
$tallas = $resultado->fetchAll(PDO::FETCH_ASSOC);

$resultado = $conexion->query("SELECT id, nombre FROM c_colores");
$colores = $resultado->fetchAll(PDO::FETCH_ASSOC);











    
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Crear Producto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

        .variant-item {
            border-left: 4px solid #667eea !important;
            transition: all 0.3s ease;
        }

        .variant-item:hover {
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.2);
        }

        #contenido {
            min-height: 80px;
            max-height: 600px;
            overflow-y: auto;
        }

        #contenido .variant-item {
            margin-bottom: 0;
        }

        .btn-primary, .btn-secondary {
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
        <h1><i class="bi bi-plus-circle"></i> Crear Producto</h1>
        <p>Agrega nuevos productos a tu tienda de forma rápida y sencilla</p>
    </div>

    <!-- FORMULARIO -->
    <div class="form-container">
        <form action="index.php?mod=CrearProductos" method="POST" enctype="multipart/form-data" id="formProducto" autocomplete="off">
            
            <!-- INFORMACIÓN BÁSICA -->
            <div class="form-section">
                <h6 class="form-section-title"><i class="bi bi-info-circle"></i> Información Básica</h6>
                
                <label class="form-label">Título del Producto *</label>
                <input type="text" class="form-control" name="titulo" id="titulo" placeholder="Ej: Camiseta Azul Premium" data-validation-type="titulo" required />
                <small class="text-muted d-block mt-1">🔒 3-100 caracteres</small>
                 
                <label class="form-label mt-3">Descripción</label>
                <textarea class="form-control" id="descripcion" name="descripcion" placeholder="Describe los detalles del producto..." data-validation-type="descripcion"></textarea>
                <small class="text-muted d-block mt-1">🔒 10-5000 caracteres</small>
            </div>

            <!-- PRECIOS Y STOCK -->
            <div class="form-section">
                <h6 class="form-section-title"><i class="bi bi-cash-coin"></i> Precios y Stock</h6>
                
                <div class="row">
                    <!-- PRECIO -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Precio *</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" name="precio" id="precio" min="0" step="0.01" placeholder="0.00" data-validation-type="precio" required />
                        </div>
                        <small class="text-muted d-block mt-1">🔒 Solo números positivos, máx 2 decimales</small>
                    </div>

                    <!-- DESCUENTO -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Descuento (%)</label>
                        <input type="number" class="form-control" name="descuento" id="descuento" min="0" max="100" placeholder="0" data-validation-type="porcentaje" />
                        <small class="form-text">🔒 Opcional - Máximo 100%</small>
                    </div>
                </div>

                <div class="row">
                    <!-- STOCK -->
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Stock *</label>
                        <input type="number" class="form-control" name="stock" id="stock" placeholder="Cantidad disponible" data-validation-type="stock" required />
                        <small class="text-muted d-block mt-1">🔒 Solo números enteros positivos</small>
                    </div>
                </div>
            </div>

            <!-- IMÁGENES -->
            <div class="form-section">
                <h6 class="form-section-title"><i class="bi bi-image"></i> Imágenes</h6>
                
                <label class="form-label">Imagen Principal *</label>
                <input type="file" class="form-control" name="imagen" id="imagen" accept="image/*" required />
                <small class="form-text">Formatos: JPG, PNG, GIF, WEBP. Máximo: 2MB</small>

                <label class="form-label mt-3">Imágenes Adicionales</label>
                <div id="multi_image_picker"></div>
                <small class="form-text">Arrastra o haz clic para agregar más imágenes</small>
            </div>

            <!-- CATEGORÍA Y ESTADO -->
            <div class="form-section">
                <h6 class="form-section-title"><i class="bi bi-tag"></i> Categoría y Estado</h6>
                
                <label class="form-label">Categoría *</label>
                <select class="form-select" name="categoria" id="categoria" required>
                    <option value="">-- Seleccionar Categoría --</option>
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?= $categoria['id'] ?>"><?= htmlspecialchars($categoria['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- VARIANTES -->
            <div class="form-section">
                <h6 class="form-section-title"><i class="bi bi-diagram-3"></i> Variantes Producto</h6>
                <p class="text-muted">Agrega combinaciones de talla y color con sus precios y stocks</p>
                
                <button type="button" class="btn btn-primary mb-3" id="agregarVariante">
                    <i class="bi bi-plus-circle"></i> Agregar Variante
                </button>

                <div id="contenido" class="bg-light p-3 rounded">
                    <!-- Aquí se agregarán las variantes dinámicamente -->
                </div>

                <template id="plantilla_Variante">
                    <div class="row mb-3 p-3 bg-white border rounded variant-item">
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
                            <input type="number" class="form-control" name="precio_variante[]" min="0" step="0.01" placeholder="0.00" data-validation-type="precio" value="0" required />
                        </div>
                        <div class="col-lg-2">
                            <label class="form-label">Stock</label>
                            <input type="number" class="form-control" name="stock_variante[]" min="0" placeholder="0" data-validation-type="stock" value="0" required />
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
                    <option value="1">Disponible para la venta</option>
                    <option value="0">Agotado</option>
                </select>

            <!-- BOTONES -->
            <div class="button-group">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Crear Producto
                </button>
                <a href="index.php?mod=gestionarProductos" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Cancelar
                </a>
            </div>
        </form>
    </div>

</div>

<script>
    $(document).ready(function() {
        $("#multi_image_picker").spartanMultiImagePicker({
            fieldName: 'imagenes[]',
            maxCount: 10,
            rowHeight: '120px',
            groupClassName: 'spartan_item',
            allowedExt: 'png|jpg|jpeg',
            dropFileLabel: "Arrastra o haz clic aquí para agregar imágenes",
        });

        // Validar formulario antes de enviar
        document.getElementById('formProducto').addEventListener('submit', function(e) {
            const variantItems = document.querySelectorAll('.variant-item');
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
    });
</script>

<script>
    const btnVariante = document.getElementById('agregarVariante');
    btnVariante.addEventListener('click', agregar_Variante);
    
    function agregar_Variante() {
        const plantilla = document.getElementById('plantilla_Variante');
        const contenido = document.getElementById('contenido');
        const clone = plantilla.content.cloneNode(true);
        contenido.appendChild(clone);
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<!-- Sistema de Validaciones -->
<script src="../../assets/js/validaciones.js"></script>

<script>
    const btnVariante = document.getElementById('agregarVariante');
    btnVariante.addEventListener('click', agregar_Variante);
    
    function agregar_Variante() {
        const plantilla = document.getElementById('plantilla_Variante');
        const contenido = document.getElementById('contenido');
        const clone = plantilla.content.cloneNode(true);
        contenido.appendChild(clone);
    }

    // Mejorar validación del formulario
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('formProducto').addEventListener('submit', function(e) {
            const titulo = document.getElementById('titulo').value.trim();
            const descripcion = document.getElementById('descripcion').value.trim();
            const precio = parseFloat(document.getElementById('precio').value) || 0;
            const descuento = parseInt(document.getElementById('descuento').value) || 0;
            const stock = parseInt(document.getElementById('stock').value) || 0;

            // Validar campos requeridos
            if (!titulo) {
                e.preventDefault();
                AlertSystem.error('Título Requerido', '❌ El título del producto es obligatorio');
                return false;
            }

            // Validar rango de título
            const validarTitulo = ValidationSystem.validar(titulo, 'titulo', 'Título');
            if (validarTitulo !== true) {
                e.preventDefault();
                AlertSystem.error('Validación Título', validarTitulo.mensaje);
                return false;
            }

            // Validar descripción si existe
            if (descripcion && descripcion.length > 0) {
                const validarDesc = ValidationSystem.validar(descripcion, 'descripcion', 'Descripción');
                if (validarDesc !== true) {
                    e.preventDefault();
                    AlertSystem.error('Validación Descripción', validarDesc.mensaje);
                    return false;
                }
            }

            // Validar precio
            if (precio <= 0) {
                e.preventDefault();
                AlertSystem.error('Precio Inválido', '❌ El precio debe ser mayor a 0');
                return false;
            }

            const validarPrecio = ValidationSystem.validar(precio.toString(), 'precio', 'Precio');
            if (validarPrecio !== true) {
                e.preventDefault();
                AlertSystem.error('Validación Precio', validarPrecio.mensaje);
                return false;
            }

            // Validar descuento
            if (descuento < 0 || descuento > 100) {
                e.preventDefault();
                AlertSystem.error('Descuento Inválido', '❌ El descuento debe estar entre 0 y 100');
                return false;
            }

            // Validar stock
            if (stock < 0) {
                e.preventDefault();
                AlertSystem.error('Stock Inválido', '❌ El stock no puede ser negativo');
                return false;
            }

            const validarStock = ValidationSystem.validar(stock.toString(), 'stock', 'Stock');
            if (validarStock !== true) {
                e.preventDefault();
                AlertSystem.error('Validación Stock', validarStock.mensaje);
                return false;
            }

            // Validar variantes
            const variantItems = document.querySelectorAll('.variant-item');
            let variantsValid = false;

            variantItems.forEach(item => {
                const talla = item.querySelector('select[name="talla[]"]').value;
                const color = item.querySelector('select[name="color[]"]').value;
                const precioVar = parseFloat(item.querySelector('input[name="precio_variante[]"]').value) || 0;
                const stockVar = parseInt(item.querySelector('input[name="stock_variante[]"]').value) || 0;

                if (talla && color && (precioVar > 0 || stockVar > 0)) {
                    variantsValid = true;
                }
            });

            if (variantItems.length > 0 && !variantsValid) {
                e.preventDefault();
                AlertSystem.warning('Variantes Incompletas', '⚠️ Completa al menos una variante con Talla, Color y (Precio o Stock)');
                return false;
            }

            // Si todo es válido, mostrar confirmación
            AlertSystem.exito('Validación Correcta', '✅ Creando producto...', 1500);
        });
    });
</script>
</body>
</html>

