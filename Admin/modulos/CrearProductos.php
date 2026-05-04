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

<style>
    /* ===== ESTILOS CREAR PRODUCTOS - BLANCO, NEGRO Y DORADO ===== */
    
    :root {
        --primary: #ffd700;
        --primary-dark: #e6c300;
        --secondary: #000000;
        --dark: #1a1a1a;
        --light: #ffffff;
        --gray: #f5f5f5;
        --border: #e0e0e0;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
    }

    * {
        transition: all 0.3s ease;
    }

    body {
        background: var(--light);
        min-height: 100vh;
        font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
    }

    /* Header Section */
    .page-header {
        background: var(--secondary);
        padding: 2.5rem 2rem;
        border-radius: 20px;
        margin-bottom: 2rem;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        border: 1px solid var(--border);
        position: relative;
        overflow: hidden;
    }

    .page-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary), var(--primary-dark), var(--primary));
    }

    .page-header h1 {
        margin: 0 0 0.5rem 0;
        font-size: 2.5rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 1rem;
        color: var(--primary);
    }

    .page-header h1 i {
        color: var(--primary);
        font-size: 2.5rem;
    }

    .page-header p {
        margin: 0;
        color: #e0e0e0;
        font-size: 1rem;
    }

    /* Form Container */
    .form-container {
        background: var(--light);
        border-radius: 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        padding: 2rem;
        margin-bottom: 2rem;
        border: 1px solid var(--border);
        transition: all 0.3s ease;
    }

    .form-container:hover {
        border-color: var(--primary);
        box-shadow: 0 8px 25px rgba(255, 215, 0, 0.1);
    }

    /* Form Sections */
    .form-section {
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid var(--border);
    }

    .form-section:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .form-section-title {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--secondary);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        position: relative;
        display: inline-block;
    }

    .form-section-title i {
        color: var(--primary);
        font-size: 1.3rem;
    }

    .form-section-title::after {
        content: '';
        position: absolute;
        bottom: -8px;
        left: 0;
        width: 50px;
        height: 2px;
        background: linear-gradient(90deg, var(--primary), transparent);
        border-radius: 2px;
    }

    /* Form Labels */
    .form-label {
        font-weight: 600;
        color: var(--secondary);
        margin-bottom: 0.5rem;
        display: block;
        font-size: 0.95rem;
    }

    /* Form Controls */
    .form-control,
    .form-select {
        border: 2px solid var(--border);
        border-radius: 12px;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        background: var(--light);
        color: var(--dark);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .form-control:focus,
    .form-select:focus {
        outline: none;
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.2) !important;
    }

    .form-control:hover,
    .form-select:hover {
        border-color: var(--primary);
    }

    textarea.form-control {
        resize: vertical;
        min-height: 100px;
    }

    /* Input Group */
    .input-group {
        display: flex;
        align-items: stretch;
    }

    .input-group-text {
        background: var(--gray);
        border: 2px solid var(--border);
        border-right: none;
        border-radius: 12px 0 0 12px;
        padding: 0.75rem 1rem;
        font-weight: 600;
        color: var(--primary);
    }

    .input-group .form-control {
        border-radius: 0 12px 12px 0;
        border-left: none;
    }

    .input-group .form-control:focus {
        border-left: none;
    }

    /* Form Text */
    .form-text,
    .text-muted {
        font-size: 0.75rem;
        color: #999999 !important;
        margin-top: 0.5rem;
        display: block;
    }

    /* Multi Image Picker */
    #multi_image_picker {
        display: flex !important;
        overflow-x: auto;
        gap: 1rem;
        padding: 1.5rem;
        max-width: 100%;
        background: var(--gray);
        border-radius: 12px;
        border: 2px dashed var(--primary);
        min-height: 150px;
        align-items: flex-start;
    }
    
    #multi_image_picker .spartan_item {
        flex: 0 0 auto;
        width: 120px;
        height: 120px;
        border-radius: 12px;
        overflow: hidden;
        border: 2px solid var(--border);
    }

    /* Variant Items */
    .variant-item {
        border-left: 3px solid var(--primary) !important;
        border-radius: 12px;
        margin-bottom: 1rem;
        background: var(--light);
        padding: 1.25rem;
        border: 1px solid var(--border);
        transition: all 0.3s ease;
    }

    .variant-item:hover {
        border-color: var(--primary);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    /* ===== ESTILOS DE BOTONES - NEGRO CON HOVER DORADO ===== */
    
    /* Botón primario - Crear Producto */
    .btn-primary {
        background: var(--secondary);
        color: var(--light);
        border: 2px solid var(--primary);
        padding: 0.75rem 2rem;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-primary:hover {
        background: var(--primary);
        color: var(--secondary);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 215, 0, 0.3);
    }

    .btn-primary:active {
        transform: translateY(0);
    }

    /* Botón secundario - Cancelar */
    .btn-secondary {
        background: var(--secondary);
        color: var(--light);
        border: 2px solid var(--primary);
        padding: 0.75rem 2rem;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-secondary:hover {
        background: var(--primary);
        color: var(--secondary);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 215, 0, 0.3);
    }

    .btn-secondary:active {
        transform: translateY(0);
    }

    /* Botón de agregar variante */
    .btn-agregar {
        background: var(--secondary);
        color: var(--light);
        border: 2px solid var(--primary);
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-agregar:hover {
        background: var(--primary);
        color: var(--secondary);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 215, 0, 0.3);
    }

    .btn-agregar:active {
        transform: translateY(0);
    }

    /* Botón de eliminar variante */
    .btn-danger {
        background: var(--secondary);
        color: var(--light);
        border: 2px solid var(--danger);
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        justify-content: center;
    }

    .btn-danger:hover {
        background: var(--danger);
        color: var(--light);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(239, 68, 68, 0.3);
    }

    .btn-danger:active {
        transform: translateY(0);
    }

    /* Button Group */
    .button-group {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid var(--border);
    }

    /* Contenido variantes */
    #contenido {
        min-height: 80px;
        max-height: 500px;
        overflow-y: auto;
        background: var(--gray) !important;
        border-radius: 12px;
        padding: 1rem;
    }

    /* Row spacing */
    .row {
        margin-bottom: 0;
    }
    
    .mb-3 {
        margin-bottom: 1rem;
    }
    
    .mt-3 {
        margin-top: 1rem;
    }

    /* Error Messages */
    .error-messages {
        background: #fff9e6;
        border-left: 4px solid var(--primary);
        color: var(--secondary);
        padding: 1rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        border: 1px solid rgba(255, 215, 0, 0.2);
    }

    .error-messages ul {
        margin: 0;
        padding-left: 1.5rem;
    }

    .error-messages li {
        margin-bottom: 0.5rem;
    }

    /* Placeholder styling */
    .form-control::placeholder,
    textarea::placeholder {
        color: #cccccc;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .page-header {
            padding: 1.5rem;
        }
        
        .page-header h1 {
            font-size: 1.8rem;
        }
        
        .form-container {
            padding: 1.25rem;
        }
        
        .form-section-title {
            font-size: 1rem;
        }
        
        .button-group {
            flex-direction: column;
        }
        
        .button-group .btn-primary,
        .button-group .btn-secondary {
            width: 100%;
            justify-content: center;
        }

        .variant-item .row > div {
            margin-bottom: 1rem;
        }

        .variant-item .row > div:last-child {
            margin-bottom: 0;
        }

        .btn-agregar {
            width: 100%;
            justify-content: center;
        }
    }

    /* Animations */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .form-container {
        animation: fadeIn 0.4s ease;
    }
</style>

<!-- Font Awesome Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<!-- Bootstrap 5 CSS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Spartan Multi Image Picker -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/spartan-multi-image-picker/dist/css/spartan-multi-image-picker.min.css">
<script src="https://cdn.jsdelivr.net/npm/spartan-multi-image-picker/dist/js/spartan-multi-image-picker.min.js"></script>

<main class="container mt-4 mb-5">
    
    <!-- HEADER -->
    <div class="page-header">
        <h1>
            <i class="fas fa-plus-circle"></i> Crear Producto
        </h1>
        <p>Agrega nuevos productos a tu tienda de forma rápida y sencilla</p>
    </div>

    <!-- FORMULARIO -->
    <div class="form-container">
        <form action="index.php?mod=CrearProductos" method="POST" enctype="multipart/form-data" id="formProducto" autocomplete="off">
            
            <!-- INFORMACIÓN BÁSICA -->
            <div class="form-section">
                <h3 class="form-section-title"><i class="fas fa-info-circle"></i> Información Básica</h3>
                
                <div class="form-group">
                    <label class="form-label">Título del Producto *</label>
                    <input type="text" class="form-control" name="titulo" id="titulo" placeholder="Ej: Camiseta Azul Premium" data-validation-type="titulo" required />
                    <small class="form-text">🔒 3-100 caracteres</small>
                </div>
                 
                <div class="form-group mt-3">
                    <label class="form-label">Descripción</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" placeholder="Describe los detalles del producto..." data-validation-type="descripcion" rows="4"></textarea>
                    <small class="form-text">🔒 10-5000 caracteres</small>
                </div>
            </div>

            <!-- PRECIOS Y STOCK -->
            <div class="form-section">
                <h3 class="form-section-title"><i class="fas fa-cash-coin"></i> Precios y Stock</h3>
                
                <div class="row">
                    <!-- PRECIO -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Precio *</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" name="precio" id="precio" min="0" step="0.01" placeholder="0.00" data-validation-type="precio" required />
                        </div>
                        <small class="form-text">🔒 Solo números positivos, máx 2 decimales</small>
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
                        <small class="form-text">🔒 Solo números enteros positivos</small>
                    </div>
                </div>
            </div>

            <!-- IMÁGENES -->
            <div class="form-section">
                <h3 class="form-section-title"><i class="fas fa-image"></i> Imágenes</h3>
                
                <div class="form-group">
                    <label class="form-label">Imagen Principal *</label>
                    <input type="file" class="form-control" name="imagen" id="imagen" accept="image/*" required />
                    <small class="form-text">Formatos: JPG, PNG, GIF, WEBP. Máximo: 2MB</small>
                </div>

                <div class="form-group mt-3">
                    <label class="form-label">Imágenes Adicionales</label>
                    <div id="multi_image_picker"></div>
                    <small class="form-text">Arrastra o haz clic para agregar más imágenes</small>
                </div>
            </div>

            <!-- CATEGORÍA Y ESTADO -->
            <div class="form-section">
                <h3 class="form-section-title"><i class="fas fa-tag"></i> Categoría y Estado</h3>
                
                <div class="form-group">
                    <label class="form-label">Categoría *</label>
                    <select class="form-select" name="categoria" id="categoria" required>
                        <option value="">-- Seleccionar Categoría --</option>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?= $categoria['id'] ?>"><?= htmlspecialchars($categoria['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group mt-3">
                    <label class="form-label">Estado del Producto</label>
                    <select class="form-select" name="activo" id="activo" required>
                        <option value="1">Disponible para la venta</option>
                        <option value="0">Agotado</option>
                    </select>
                </div>
            </div>

            <!-- VARIANTES -->
            <div class="form-section">
                <h3 class="form-section-title"><i class="fas fa-diagram-3"></i> Variantes Producto</h3>
                <p class="form-text" style="margin-bottom: 1rem;">Agrega combinaciones de talla y color con sus precios y stocks</p>
                
                <button type="button" class="btn-agregar mb-3" id="agregarVariante">
                    <i class="fas fa-plus-circle"></i> Agregar Variante
                </button>

                <div id="contenido">
                    <!-- Aquí se agregarán las variantes dinámicamente -->
                </div>

                <template id="plantilla_Variante">
                    <div class="variant-item">
                        <div class="row">
                            <div class="col-lg-3 col-md-6 mb-3 mb-md-0">
                                <label class="form-label">Talla *</label>
                                <select class="form-select" name="talla[]" required>
                                    <option value="">-- Seleccionar Talla --</option>
                                    <?php foreach ($tallas as $talla): ?>
                                        <option value="<?= $talla['id'] ?>"><?= htmlspecialchars($talla['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3 mb-md-0">
                                <label class="form-label">Color *</label>
                                <select class="form-select" name="color[]" required>
                                    <option value="">-- Seleccionar Color --</option>
                                    <?php foreach ($colores as $color): ?>
                                        <option value="<?= $color['id'] ?>"><?= htmlspecialchars($color['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-4 mb-3 mb-md-0">
                                <label class="form-label">Precio ($)</label>
                                <input type="number" class="form-control" name="precio_variante[]" min="0" step="0.01" placeholder="0.00" data-validation-type="precio" value="0" required />
                            </div>
                            <div class="col-lg-2 col-md-4 mb-3 mb-md-0">
                                <label class="form-label">Stock</label>
                                <input type="number" class="form-control" name="stock_variante[]" min="0" placeholder="0" data-validation-type="stock" value="0" required />
                            </div>
                            <div class="col-lg-2 col-md-4 d-flex align-items-end">
                                <button type="button" class="btn-danger w-100" onclick="this.closest('.variant-item').remove()">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </div>
                        </div>
                    </div>    
                </template>
            </div>

            <!-- BOTONES -->
            <div class="button-group">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-check-circle"></i> Crear Producto
                </button>
                <a href="index.php?mod=gestionarProductos" class="btn-secondary">
                    <i class="fas fa-times-circle"></i> Cancelar
                </a>
            </div>
        </form>
    </div>

</main>

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

<!-- Sistema de Validaciones -->
<script src="../../assets/js/validaciones.js"></script>

<script>
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
                alert('❌ El título del producto es obligatorio');
                return false;
            }

            // Validar rango de título
            if (typeof ValidationSystem !== 'undefined') {
                const validarTitulo = ValidationSystem.validar(titulo, 'titulo', 'Título');
                if (validarTitulo !== true) {
                    e.preventDefault();
                    alert(validarTitulo.mensaje);
                    return false;
                }

                // Validar descripción si existe
                if (descripcion && descripcion.length > 0) {
                    const validarDesc = ValidationSystem.validar(descripcion, 'descripcion', 'Descripción');
                    if (validarDesc !== true) {
                        e.preventDefault();
                        alert(validarDesc.mensaje);
                        return false;
                    }
                }

                // Validar precio
                if (precio <= 0) {
                    e.preventDefault();
                    alert('❌ El precio debe ser mayor a 0');
                    return false;
                }

                const validarPrecio = ValidationSystem.validar(precio.toString(), 'precio', 'Precio');
                if (validarPrecio !== true) {
                    e.preventDefault();
                    alert(validarPrecio.mensaje);
                    return false;
                }
            }

            // Validar descuento
            if (descuento < 0 || descuento > 100) {
                e.preventDefault();
                alert('❌ El descuento debe estar entre 0 y 100');
                return false;
            }

            // Validar stock
            if (stock < 0) {
                e.preventDefault();
                alert('❌ El stock no puede ser negativo');
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
                alert('⚠️ Completa al menos una variante con Talla, Color y (Precio o Stock)');
                return false;
            }
        });
    });
</script>