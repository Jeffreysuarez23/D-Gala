<?php
require_once("include/db.php");
require_once("include/configuracionesAD.php");

$db = new Database();
$conexion = $db->getConexion();

// Si hay un ID en la URL, mostrar el editor
if (isset($_GET['id'])) {
    include("editar_producto_modal.php");
    exit;
}

// Variables para búsqueda
$busqueda_productos = isset($_GET['busqueda_productos']) ? trim($_GET['busqueda_productos']) : '';

// Obtener todos los productos con búsqueda
$sql = "SELECT p.id, p.titulo, p.descripcion, p.precio, p.descuento, p.stock, p.imagen, p.id_categoria, p.activo, p.fecha_publicacion, c.nombre as categoria_nombre 
        FROM productos p 
        LEFT JOIN caracteristicas c ON p.id_categoria = c.id";

if ($busqueda_productos) {
    $sql .= " WHERE p.titulo LIKE :busqueda 
              OR p.descripcion LIKE :busqueda 
              OR c.nombre LIKE :busqueda";
}

$sql .= " ORDER BY p.fecha_publicacion DESC";

$stmt = $conexion->prepare($sql);
if ($busqueda_productos) {
    $stmt->execute([':busqueda' => '%' . $busqueda_productos . '%']);
} else {
    $stmt->execute();
}
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesar eliminación
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'eliminar') {
    $idEliminar = intval($_POST['id_eliminar']);
    
    try {
        $conexion->beginTransaction();
        
        // Obtener imágenes asociadas
        $sqlImgs = "SELECT imagen FROM producto_imagenes WHERE producto_id = ?";
        $stmtImgs = $conexion->prepare($sqlImgs);
        $stmtImgs->execute([$idEliminar]);
        $imagenes = $stmtImgs->fetchAll(PDO::FETCH_COLUMN);
        
        // Eliminar archivos de imágenes adicionales
        foreach ($imagenes as $img) {
            if (file_exists("../assets/img/$img")) {
                unlink("../assets/img/$img");
            }
        }
        
        // Obtener imagen principal
        $sqlImg = "SELECT imagen FROM productos WHERE id = ?";
        $stmtImg = $conexion->prepare($sqlImg);
        $stmtImg->execute([$idEliminar]);
        $imgPrincipal = $stmtImg->fetchColumn();
        
        if ($imgPrincipal && file_exists("../assets/img/$imgPrincipal")) {
            unlink("../assets/img/$imgPrincipal");
        }
        
        // Eliminar imágenes de la BD
        $conexion->prepare("DELETE FROM producto_imagenes WHERE producto_id = ?")->execute([$idEliminar]);
        
        // Eliminar el producto
        $conexion->prepare("DELETE FROM productos WHERE id = ?")->execute([$idEliminar]);
        
        $conexion->commit();
        echo "<script>alert('Producto eliminado correctamente'); window.location.href='index.php?mod=GestionarProductos';</script>";
        exit;
        
    } catch (Exception $e) {
        $conexion->rollBack();
        echo "<script>alert('Error al eliminar: " . addslashes($e->getMessage()) . "'); window.location.href='index.php?mod=GestionarProductos';</script>";
        exit;
    }
}
?>

<style>
    /* ===== ESTILOS GESTIONAR PRODUCTOS - MISMO ESTILO QUE GESTIONAR USUARIOS ===== */
    
    .gestionar-productos-container {
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
        --info: #3b82f6;
        padding-top: 2rem;
    }

    /* Page Header */
    .gestionar-productos-container .page-header {
        background: var(--secondary);
        color: var(--primary);
        padding: 2.5rem 2rem;
        border-radius: 20px;
        margin-bottom: 2rem;
        margin-top: -8px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        border: 1px solid var(--border);
        position: relative;
        overflow: hidden;
    }

    .gestionar-productos-container .page-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary), var(--primary-dark), var(--primary));
    }

    .gestionar-productos-container .page-header h1 {
        margin: 0;
        font-size: 2.5rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 1rem;
        color: var(--primary);
    }

    .gestionar-productos-container .page-header h1 i {
        color: var(--primary);
        font-size: 2.5rem;
    }

    .gestionar-productos-container .page-header p {
        margin-top: 0.5rem;
        margin-bottom: 0;
        color: #e0e0e0;
    }

    /* Stats Cards */
    .gestionar-productos-container .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .gestionar-productos-container .stat-card {
        background: var(--light);
        padding: 1.5rem;
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--border);
        transition: all 0.3s ease;
    }

    .gestionar-productos-container .stat-card:hover {
        border-color: var(--primary);
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(255, 215, 0, 0.1);
    }

    .gestionar-productos-container .stat-card h6 {
        color: #666666;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        margin: 0 0 1rem 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .gestionar-productos-container .stat-card h6 i {
        color: var(--primary);
    }

    .gestionar-productos-container .stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--secondary);
    }

    /* Filter Section */
    .gestionar-productos-container .filter-section {
        padding: 1.5rem;
        background: var(--light);
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
        border: 1px solid var(--border);
        transition: all 0.3s ease;
    }

    .gestionar-productos-container .filter-section:hover {
        border-color: var(--primary);
    }

    .gestionar-productos-container .filter-section h5 {
        color: var(--secondary);
        font-weight: 600;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .gestionar-productos-container .filter-section h5 i {
        color: var(--primary);
    }

    .gestionar-productos-container .search-row {
        display: grid;
        grid-template-columns: 1fr auto auto;
        gap: 1rem;
        align-items: flex-end;
    }

    .gestionar-productos-container .search-input {
        position: relative;
    }

    .gestionar-productos-container .search-input input {
        border: 2px solid var(--border);
        border-radius: 12px;
        padding: 0.75rem 1rem 0.75rem 2.75rem;
        width: 100%;
        background: var(--light);
        font-weight: 500;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .gestionar-productos-container .search-input input::placeholder {
        color: #999999;
    }

    .gestionar-productos-container .search-input input:focus {
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.2) !important;
        outline: none;
    }

    .gestionar-productos-container .search-input input:hover {
        border-color: var(--primary);
    }

    .gestionar-productos-container .search-input i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--primary);
    }

    .gestionar-productos-container .btn-search {
        background: var(--secondary);
        color: var(--light);
        border: 2px solid var(--primary);
        border-radius: 12px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
    }

    .gestionar-productos-container .btn-search:hover {
        background: var(--primary);
        color: var(--secondary);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 215, 0, 0.3);
    }

    .gestionar-productos-container .btn-clear {
        background: var(--light);
        color: var(--secondary);
        border: 2px solid var(--border);
        border-radius: 12px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .gestionar-productos-container .btn-clear:hover {
        border-color: var(--primary);
        color: var(--primary);
        transform: translateY(-2px);
        text-decoration: none;
    }

    /* Table Container */
    .gestionar-productos-container .table-container {
        overflow-x: auto;
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--border);
        background: var(--light);
    }

    .gestionar-productos-container .table {
        margin-bottom: 0;
        background: var(--light);
        width: 100%;
        border-collapse: collapse;
    }

    .gestionar-productos-container .table thead {
        background: #f8f9fa;
        border-bottom: 2px solid var(--primary);
    }

    .gestionar-productos-container .table thead th {
        color: var(--secondary);
        font-weight: 700;
        padding: 1.25rem;
        border: none;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        vertical-align: middle;
    }

    .gestionar-productos-container .table thead th i {
        color: var(--primary);
        margin-right: 5px;
    }

    .gestionar-productos-container .table tbody tr {
        border-bottom: 1px solid #f0f0f0;
        transition: all 0.3s ease;
    }

    .gestionar-productos-container .table tbody tr:hover {
        background: rgba(255, 215, 0, 0.05);
    }

    .gestionar-productos-container .table tbody td {
        padding: 1rem 1.25rem;
        vertical-align: middle;
        color: var(--dark);
    }

    .gestionar-productos-container .table tbody td:first-child {
        font-weight: 600;
        vertical-align: middle;
    }

    .gestionar-productos-container .table strong {
        color: var(--secondary);
        font-weight: 700;
    }

    /* Badges */
    .gestionar-productos-container .badge-estatus {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .gestionar-productos-container .badge-activo {
        background: rgba(255, 215, 0, 0.15);
        color: var(--primary-dark);
        border: 1px solid rgba(255, 215, 0, 0.3);
    }

    .gestionar-productos-container .badge-inactivo {
        background: rgba(239, 68, 68, 0.15);
        color: var(--danger);
        border: 1px solid rgba(239, 68, 68, 0.3);
    }

    .gestionar-productos-container .badge-precio {
        background: var(--secondary);
        color: var(--primary);
        border: 1px solid var(--primary);
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.75rem;
        display: inline-block;
        white-space: nowrap;
    }

    .gestionar-productos-container .badge-descuento {
        background: rgba(245, 158, 11, 0.15);
        color: var(--warning);
        border: 1px solid rgba(245, 158, 11, 0.3);
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.75rem;
        display: inline-block;
        white-space: nowrap;
    }

    .gestionar-productos-container .badge-stock {
        background: rgba(16, 185, 129, 0.15);
        color: var(--success);
        border: 1px solid rgba(16, 185, 129, 0.3);
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.75rem;
        display: inline-block;
        white-space: nowrap;
    }

    .gestionar-productos-container .badge-stock-bajo {
        background: rgba(245, 158, 11, 0.15);
        color: var(--warning);
        border: 1px solid rgba(245, 158, 11, 0.3);
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.75rem;
        display: inline-block;
        white-space: nowrap;
    }

    .gestionar-productos-container .badge-agotado {
        background: rgba(239, 68, 68, 0.15);
        color: var(--danger);
        border: 1px solid rgba(239, 68, 68, 0.3);
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.75rem;
        display: inline-block;
        white-space: nowrap;
    }

    /* Action Buttons */
    .gestionar-productos-container .btn-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        border-radius: 10px;
        border: none;
        cursor: pointer;
        font-size: 0.875rem;
        transition: all 0.3s ease;
        margin: 0 0.25rem;
        background: transparent;
        text-decoration: none;
        padding: 0.5rem 1rem;
    }

    .gestionar-productos-container .btn-edit {
        background: var(--secondary);
        color: var(--primary);
        border: 1px solid var(--primary);
    }

    .gestionar-productos-container .btn-edit i {
        color: var(--primary);
    }

    .gestionar-productos-container .btn-edit:hover {
        background: var(--primary);
        color: var(--secondary);
        transform: translateY(-2px);
        text-decoration: none;
    }

    .gestionar-productos-container .btn-edit:hover i {
        color: var(--secondary);
    }

    .gestionar-productos-container .btn-delete {
        background: var(--light);
        color: var(--danger);
        border: 1px solid var(--danger);
    }

    .gestionar-productos-container .btn-delete i {
        color: var(--danger);
    }

    .gestionar-productos-container .btn-delete:hover {
        background: var(--danger);
        color: var(--light);
        transform: translateY(-2px);
    }

    .gestionar-productos-container .btn-delete:hover i {
        color: var(--light);
    }

    /* Empty State */
    .gestionar-productos-container .empty-state {
        text-align: center;
        padding: 3rem 2rem;
        color: #999999;
    }

    .gestionar-productos-container .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        color: var(--primary);
        opacity: 0.5;
    }

    .gestionar-productos-container .empty-state p {
        font-size: 1rem;
        margin: 0;
    }

    /* Modal Styles */
    .gestionar-productos-container .modal-content {
        border: none;
        border-radius: 20px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
        overflow: hidden;
    }

    .gestionar-productos-container .modal-header {
        background: var(--secondary);
        color: var(--primary);
        border: none;
        border-radius: 20px 20px 0 0;
        padding: 1.25rem 1.5rem;
    }

    .gestionar-productos-container .modal-header .modal-title {
        font-weight: 700;
        font-size: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .gestionar-productos-container .modal-header .modal-title i {
        color: var(--primary);
    }

    .gestionar-productos-container .modal-header .btn-close {
        filter: brightness(0) invert(1);
        background-color: var(--primary);
        opacity: 0.8;
        border-radius: 50%;
        padding: 0.5rem;
    }

    .gestionar-productos-container .modal-body {
        padding: 1.5rem;
    }

    .gestionar-productos-container .modal-footer {
        border-top: 1px solid var(--border);
        padding: 1rem 1.5rem;
    }

    /* Form Controls */
    .gestionar-productos-container .form-control {
        border: 2px solid var(--border);
        border-radius: 12px;
        padding: 0.75rem 1rem;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        background: var(--light);
    }

    .gestionar-productos-container .form-control:focus {
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.2) !important;
        outline: none;
    }

    .gestionar-productos-container .form-label {
        font-weight: 600;
        color: var(--secondary);
        margin-bottom: 0.5rem;
    }

    /* Buttons */
    .gestionar-productos-container .btn-primary {
        background: var(--secondary);
        color: var(--light);
        border: 2px solid var(--primary);
        border-radius: 12px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .gestionar-productos-container .btn-primary:hover {
        background: var(--primary);
        color: var(--secondary);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 215, 0, 0.3);
    }

    .gestionar-productos-container .btn-secondary {
        background: var(--light);
        color: var(--secondary);
        border: 2px solid var(--border);
        border-radius: 12px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .gestionar-productos-container .btn-secondary:hover {
        border-color: var(--primary);
        color: var(--primary);
        transform: translateY(-2px);
    }

    .gestionar-productos-container .btn-danger {
        background: var(--light);
        color: var(--danger);
        border: 2px solid var(--danger);
        border-radius: 12px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .gestionar-productos-container .btn-danger:hover {
        background: var(--danger);
        color: var(--light);
        transform: translateY(-2px);
    }

    /* Alerts */
    .gestionar-productos-container .alert-warning {
        background: rgba(255, 215, 0, 0.1);
        border-left: 4px solid var(--primary);
        color: var(--secondary);
        border-radius: 12px;
        padding: 1rem;
    }

    .gestionar-productos-container .text-muted {
        color: #999999 !important;
        font-size: 0.85rem;
        margin-top: 0.5rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .gestionar-productos-container .page-header h1 {
            font-size: 1.75rem;
        }

        .gestionar-productos-container .page-header i {
            font-size: 1.75rem;
        }

        .gestionar-productos-container .search-row {
            grid-template-columns: 1fr;
        }

        .gestionar-productos-container .stats-row {
            grid-template-columns: 1fr;
        }

        .gestionar-productos-container .table {
            font-size: 0.85rem;
        }

        .gestionar-productos-container .table th, 
        .gestionar-productos-container .table td {
            padding: 0.75rem 0.5rem;
        }

        .gestionar-productos-container .btn-icon {
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
        }
        
        .gestionar-productos-container .badge-precio,
        .gestionar-productos-container .badge-descuento,
        .gestionar-productos-container .badge-stock,
        .gestionar-productos-container .badge-stock-bajo,
        .gestionar-productos-container .badge-agotado {
            padding: 0.3rem 0.7rem;
            font-size: 0.7rem;
        }
    }
</style>

<div class="gestionar-productos-container">
    <div class="container-fluid" style="max-width: 1400px; margin: 0 auto; padding: 0 1rem;">
        <div class="page-header">
            <h1>
                <i class="bi bi-box-seam-fill"></i>
                Gestionar Productos
            </h1>
            <p>Administra y controla todos los productos de tu tienda</p>
        </div>

        <!-- Estadísticas rápidas - Productos -->
        <div class="stats-row">
            <div class="stat-card">
                <h6><i class="bi bi-box-seam"></i> Total Productos</h6>
                <div class="stat-value"><?= count($productos) ?></div>
            </div>
            <div class="stat-card">
                <h6><i class="bi bi-check-circle"></i> Productos Activos</h6>
                <div class="stat-value"><?= array_reduce($productos, fn($c, $r) => $c + ($r["activo"] == 1 ? 1 : 0), 0) ?></div>
            </div>
            <div class="stat-card">
                <h6><i class="bi bi-x-circle"></i> Productos Inactivos</h6>
                <div class="stat-value"><?= array_reduce($productos, fn($c, $r) => $c + ($r["activo"] == 0 ? 1 : 0), 0) ?></div>
            </div>
            <div class="stat-card">
                <h6><i class="bi bi-cash-stack"></i> Valor Total Inventario</h6>
                <div class="stat-value">$<?= number_format(array_reduce($productos, fn($c, $r) => $c + ($r["precio"] * $r["stock"]), 0), 2) ?></div>
            </div>
        </div>

        <!-- Filtros y búsqueda -->
        <div class="filter-section">
            <h5><i class="bi bi-funnel"></i> Filtrar y Buscar</h5>
            <form method="GET" action="index.php" id="searchFormProductos">
                <input type="hidden" name="mod" value="GestionarProductos">
                <div class="search-row">
                    <div class="search-input">
                        <i class="bi bi-search"></i>
                        <input type="text" name="busqueda_productos" placeholder="Buscar por título, descripción o categoría..." value="<?= htmlspecialchars($busqueda_productos) ?>" autofocus>
                    </div>
                    <button type="submit" class="btn-search">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                    <?php if ($busqueda_productos): ?>
                        <a href="index.php?mod=GestionarProductos" class="btn-clear">
                            <i class="bi bi-x-circle"></i> Limpiar
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Tabla de productos -->
        <div class="table-container">
            <?php if (count($productos) > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th><i class="bi bi-box"></i> Producto</th>
                            <th><i class="bi bi-cash-coin"></i> Precio</th>
                            <th><i class="bi bi-percent"></i> Descuento</th>
                            <th><i class="bi bi-box-seam"></i> Stock</th>
                            <th><i class="bi bi-tag"></i> Categoría</th>
                            <th><i class="bi bi-info-circle"></i> Estado</th>
                            <th><i class="bi bi-calendar"></i> Fecha</th>
                            <th colspan="2" class="text-center"><i class="bi bi-gear"></i> Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $p): ?>
                            <tr style="<?= $p['activo'] == 0 ? 'background: rgba(239, 68, 68, 0.05);' : '' ?>">
                                <td>
                                    <strong><?= htmlspecialchars($p['titulo']) ?></strong>
                                </td>
                                <td>
                                    <span class="badge-precio">$<?= number_format($p['precio'], 2) ?></span>
                                </td>
                                <td>
                                    <?php if ($p['descuento'] > 0): ?>
                                        <span class="badge-descuento"><?= $p['descuento'] ?>% OFF</span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($p['stock'] > 10): ?>
                                        <span class="badge-stock"><?= $p['stock'] ?> unidades</span>
                                    <?php elseif ($p['stock'] > 0 && $p['stock'] <= 10): ?>
                                        <span class="badge-stock-bajo"><?= $p['stock'] ?> unidades</span>
                                    <?php else: ?>
                                        <span class="badge-agotado">Agotado</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($p['categoria_nombre'] ?? 'Sin categoría') ?></td>
                                <td>
                                    <?php if ($p['activo'] == 1): ?>
                                        <span class="badge-estatus badge-activo"><i class="bi bi-check-circle"></i> Activo</span>
                                    <?php else: ?>
                                        <span class="badge-estatus badge-inactivo"><i class="bi bi-x-circle"></i> Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-muted"><?= date('d/m/Y', strtotime($p['fecha_publicacion'])) ?></small>
                                </td>
                                <td class="text-center">
                                    <a href="index.php?mod=GestionarProductos&id=<?= $p['id'] ?>" class="btn-icon btn-edit">
                                        <i class="bi bi-pencil-square"></i> Editar
                                    </a>
                                </td>
                                <td class="text-center">
                                    <button class="btn-icon btn-delete" data-bs-toggle="modal" data-bs-target="#eliminarModal<?= $p['id'] ?>">
                                        <i class="bi bi-trash"></i> Eliminar
                                    </button>
                                </td>
                            </tr>

                            <!-- Modal Eliminar -->
                            <div class="modal fade" id="eliminarModal<?= $p['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Eliminar Producto</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <div class="alert-warning mb-3">
                                                    <i class="bi bi-info-circle"></i>
                                                    ¿Está seguro de que desea eliminar este producto?
                                                </div>
                                                <p class="fw-bold"><?= htmlspecialchars($p['titulo']) ?></p>
                                                <p class="text-muted">Esta acción eliminará el producto y todas las imágenes asociadas. No se puede deshacer.</p>
                                            </div>
                                            <div class="modal-footer">
                                                <input type="hidden" name="accion" value="eliminar">
                                                <input type="hidden" name="id_eliminar" value="<?= $p['id'] ?>">
                                                <button type="button" class="btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn-danger">
                                                    <i class="bi bi-trash"></i> Sí, Eliminar
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <p>No se encontraron productos<?= $busqueda_productos ? ' con los criterios de búsqueda' : '' ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Búsqueda al presionar Enter
    const busquedaProductos = document.querySelector('.gestionar-productos-container input[name="busqueda_productos"]');
    if (busquedaProductos) {
        busquedaProductos.addEventListener('keyup', function(e) {
            if(e.key === 'Enter') {
                document.getElementById('searchFormProductos').submit();
            }
        });
    }
</script>