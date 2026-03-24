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

// Obtener todos los productos
$sql = "SELECT p.id, p.titulo, p.descripcion, p.precio, p.descuento, p.stock, p.imagen, p.id_categoria, p.activo, p.fecha_publicacion, c.nombre as categoria_nombre 
        FROM productos p 
        LEFT JOIN caracteristicas c ON p.id_categoria = c.id 
        ORDER BY p.fecha_publicacion DESC";
$resultado = $conexion->query($sql);
$productos = $resultado->fetchAll(PDO::FETCH_ASSOC);
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
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Productos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
        }
        
        .header-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .header-section h1 {
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .btn-accion {
            padding: 6px 12px;
            font-size: 0.85rem;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .btn-editar {
            background: #3498db;
            color: white;
            border: none;
        }
        
        .btn-editar:hover {
            background: #2980b9;
            color: white;
            transform: translateY(-2px);
        }
        
        .btn-eliminar {
            background: #e74c3c;
            color: white;
            border: none;
        }
        
        .btn-eliminar:hover {
            background: #c0392b;
            color: white;
            transform: translateY(-2px);
        }
        
        .badge {
            padding: 6px 12px;
        }
    </style>
</head>
<body>
    <main>
        <div class="container-fluid px-4 py-5">
            
            <div class="header-section">
                <h1><i class="bi bi-bag-check"></i> Gestionar Productos</h1>
                <p class="mb-0">Administra todos los productos de tu tienda de forma eficiente</p>
            </div>

            <div class="table-container">
                <?php if (count($productos) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th width="25%"><i class="bi bi-box"></i> Producto</th>
                                    <th width="12%">Precio</th>
                                    <th width="10%">Descuento</th>
                                    <th width="10%">Stock</th>
                                    <th width="15%">Categoría</th>
                                    <th width="10%">Estado</th>
                                    <th width="15%">Fecha</th>
                                    <th colspan="2" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($productos as $p): ?>
                                    <tr <?php echo ($p['activo'] == 0) ? 'style="background-color: #fff5f5;"' : ''; ?>>
                                        <td>
                                            <strong><?= htmlspecialchars($p['titulo']) ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge" style="background: #667eea;">$<?= number_format($p['precio'], 2) ?></span>
                                        </td>
                                        <td>
                                            <?= $p['descuento'] > 0 ? '<span class="badge" style="background: #f39c12;">' . $p['descuento'] . '%</span>' : '-' ?>
                                        </td>
                                        <td>
                                            <span class="badge" style="background: <?= $p['stock'] > 0 ? '#27ae60' : '#e74c3c'; ?>;">
                                                <?= $p['stock'] ?> un.
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($p['categoria_nombre'] ?? 'Sin categoría') ?></td>
                                        <td>
                                            <?php if ($p['activo'] == 1): ?>
                                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> Activo</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?= date('d/m/Y', strtotime($p['fecha_publicacion'])) ?></small>
                                        </td>
                                        <td class="text-center">
                                            <a href="index.php?mod=GestionarProductos&id=<?= $p['id'] ?>" class="btn btn-accion btn-editar">
                                                <i class="bi bi-pencil-square"></i> Editar
                                            </a>
                                        </td>
                                        <td class="text-center">
                                             <button class="btn btn-accion btn-eliminar" data-bs-toggle="modal" data-bs-target="#eliminarModal<?= $p['id'] ?>">
                                                <i class="bi bi-trash"></i> Eliminar
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Modal Eliminar -->
                                    <div class="modal fade" id="eliminarModal<?= $p['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header border-danger">
                                                    <h5 class="modal-title text-danger"><i class="bi bi-exclamation-triangle"></i> Eliminar Producto</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <p class="mb-2">¿Está seguro de que desea eliminar este producto?</p>
                                                        <p class="fw-bold text-danger">
                                                            <i class="bi bi-exclamation-circle"></i>
                                                            <?= htmlspecialchars($p['titulo']) ?>
                                                        </p>
                                                        <p class="text-muted small">Esta acción eliminará todas las imágenes asociadas y no se puede deshacer.</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <input type="hidden" name="accion" value="eliminar">
                                                        <input type="hidden" name="id_eliminar" value="<?= $p['id'] ?>">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                        <button type="submit" class="btn btn-danger">
                                                            <i class="bi bi-trash"></i> Eliminar Definitivamente
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5" style="color: #7f8c8d;">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #bdc3c7;"></i>
                        <h5 class="mt-3">No hay productos registrados</h5>
                        <p class="text-muted">Comienza a agregar productos a tu tienda</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>