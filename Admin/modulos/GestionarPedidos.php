<?php
require_once("include/db.php");
require_once("include/configuracionesAD.php");

$db = new Database();
$conexion = $db->getConexion();

// Procesar cambio de estado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion'])) {
    if ($_POST['accion'] == 'cambiar_estado') {
        $id_compra = intval($_POST['id_compra']);
        $nuevo_estado = trim($_POST['nuevo_estado']);
        $estados_validos = ['pendiente', 'procesando', 'enviado', 'entregado', 'cancelado', 'pagado'];
        
        if (in_array($nuevo_estado, $estados_validos)) {
            $sql = "UPDATE compra SET status = ? WHERE id = ?";
            $stmt = $conexion->prepare($sql);
            if ($stmt->execute([$nuevo_estado, $id_compra])) {
                echo "<script>alert('Estado actualizado correctamente'); window.location.href='index.php?mod=GestionarPedidos';</script>";
                exit;
            }
        }
    } elseif ($_POST['accion'] == 'eliminar') {
        $id_eliminar = intval($_POST['id_eliminar']);
        
        try {
            $conexion->beginTransaction();
            
            // Eliminar detalles de compra
            $conexion->prepare("DELETE FROM detalle_compra WHERE id_compra = ?")->execute([$id_eliminar]);
            
            // Eliminar compra
            $conexion->prepare("DELETE FROM compra WHERE id = ?")->execute([$id_eliminar]);
            
            $conexion->commit();
            echo "<script>alert('Pedido eliminado correctamente'); window.location.href='index.php?mod=GestionarPedidos';</script>";
            exit;
            
        } catch (Exception $e) {
            $conexion->rollBack();
            echo "<script>alert('Error al eliminar: " . addslashes($e->getMessage()) . "');</script>";
        }
    }
}

// Filtros
$filtro_estado = isset($_GET['estado']) ? trim($_GET['estado']) : 'todos';
$filtro_cliente = isset($_GET['cliente']) ? trim($_GET['cliente']) : '';
$filtro_fecha = isset($_GET['fecha']) ? trim($_GET['fecha']) : '';

// Construir consulta
$sql = "SELECT c.id, c.id_transaccion, c.fecha, c.status, c.email, c.id_cliente, 
                c.total, c.medio_pago, cl.nombre, cl.apellido 
        FROM compra c 
        LEFT JOIN clientes cl ON c.id_cliente = cl.id 
        WHERE 1=1 ";

$parametros = [];

if ($filtro_estado != 'todos' && !empty($filtro_estado)) {
    $sql .= "AND c.status = ? ";
    $parametros[] = $filtro_estado;
}

if (!empty($filtro_cliente)) {
    $sql .= "AND (cl.nombre LIKE ? OR cl.apellido LIKE ? OR c.email LIKE ? OR c.id_transaccion LIKE ?) ";
    $filtro_like = '%' . $filtro_cliente . '%';
    $parametros[] = $filtro_like;
    $parametros[] = $filtro_like;
    $parametros[] = $filtro_like;
    $parametros[] = $filtro_like;
}

if (!empty($filtro_fecha)) {
    $sql .= "AND DATE(c.fecha) = ? ";
    $parametros[] = $filtro_fecha;
}

$sql .= "ORDER BY c.fecha DESC";

$stmt = $conexion->prepare($sql);
$stmt->execute($parametros);
$compras = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ver detalles de un pedido si se solicita
$detalles_pedido = null;
$pedido_actual = null;
if (isset($_GET['id'])) {
    $id_pedido = intval($_GET['id']);
    $sql_pedido = "SELECT c.id, c.id_transaccion, c.fecha, c.status, c.email, c.id_cliente, 
                          c.total, c.medio_pago, cl.nombre, cl.apellido, cl.telefono, cl.documento
                   FROM compra c 
                   LEFT JOIN clientes cl ON c.id_cliente = cl.id 
                   WHERE c.id = ?";
    $stmt_pedido = $conexion->prepare($sql_pedido);
    $stmt_pedido->execute([$id_pedido]);
    $pedido_actual = $stmt_pedido->fetch(PDO::FETCH_ASSOC);
    
    if ($pedido_actual) {
        $sql_detalles = "SELECT * FROM detalle_compra WHERE id_compra = ?";
        $stmt_detalles = $conexion->prepare($sql_detalles);
        $stmt_detalles->execute([$id_pedido]);
        $detalles_pedido = $stmt_detalles->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<style>
    /* ===== ESTILOS GESTIONAR PEDIDOS - MISMO ESTILO QUE GESTIONAR USUARIOS ===== */
    
    .gestionar-pedidos-container {
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
    .gestionar-pedidos-container .page-header {
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

    .gestionar-pedidos-container .page-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary), var(--primary-dark), var(--primary));
    }

    .gestionar-pedidos-container .page-header h1 {
        margin: 0;
        font-size: 2.5rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 1rem;
        color: var(--primary);
    }

    .gestionar-pedidos-container .page-header h1 i {
        color: var(--primary);
        font-size: 2.5rem;
    }

    .gestionar-pedidos-container .page-header p {
        margin-top: 0.5rem;
        margin-bottom: 0;
        color: #e0e0e0;
    }

    /* Stats Cards */
    .gestionar-pedidos-container .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .gestionar-pedidos-container .stat-card {
        background: var(--light);
        padding: 1.5rem;
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--border);
        transition: all 0.3s ease;
    }

    .gestionar-pedidos-container .stat-card:hover {
        border-color: var(--primary);
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(255, 215, 0, 0.1);
    }

    .gestionar-pedidos-container .stat-card h6 {
        color: #666666;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        margin: 0 0 1rem 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .gestionar-pedidos-container .stat-card h6 i {
        color: var(--primary);
    }

    .gestionar-pedidos-container .stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--secondary);
    }

    /* Filter Section */
    .gestionar-pedidos-container .filter-section {
        padding: 1.5rem;
        background: var(--light);
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
        border: 1px solid var(--border);
        transition: all 0.3s ease;
    }

    .gestionar-pedidos-container .filter-section:hover {
        border-color: var(--primary);
    }

    .gestionar-pedidos-container .filter-section h5 {
        color: var(--secondary);
        font-weight: 600;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .gestionar-pedidos-container .filter-section h5 i {
        color: var(--primary);
    }

    .gestionar-pedidos-container .filter-section.report-section {
        background: linear-gradient(135deg, #fff9e6 0%, #ffffff 100%);
        border-left: 4px solid var(--primary);
    }

    .gestionar-pedidos-container .search-row {
        display: grid;
        grid-template-columns: 1fr auto auto;
        gap: 1rem;
        align-items: flex-end;
    }

    .gestionar-pedidos-container .search-input {
        position: relative;
    }

    .gestionar-pedidos-container .search-input input,
    .gestionar-pedidos-container .form-control,
    .gestionar-pedidos-container .form-select {
        border: 2px solid var(--border);
        border-radius: 12px;
        padding: 0.75rem 1rem;
        width: 100%;
        background: var(--light);
        font-weight: 500;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .gestionar-pedidos-container .search-input input::placeholder {
        color: #999999;
    }

    .gestionar-pedidos-container .search-input input:focus,
    .gestionar-pedidos-container .form-control:focus,
    .gestionar-pedidos-container .form-select:focus {
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.2) !important;
        outline: none;
    }

    .gestionar-pedidos-container .search-input input:hover,
    .gestionar-pedidos-container .form-control:hover,
    .gestionar-pedidos-container .form-select:hover {
        border-color: var(--primary);
    }

    .gestionar-pedidos-container .search-input i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--primary);
    }

    .gestionar-pedidos-container .btn-search,
    .gestionar-pedidos-container .btn-filter {
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

    .gestionar-pedidos-container .btn-search:hover,
    .gestionar-pedidos-container .btn-filter:hover {
        background: var(--primary);
        color: var(--secondary);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 215, 0, 0.3);
    }

    .gestionar-pedidos-container .btn-clear {
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

    .gestionar-pedidos-container .btn-clear:hover {
        border-color: var(--primary);
        color: var(--primary);
        transform: translateY(-2px);
        text-decoration: none;
    }

    /* Table Container */
    .gestionar-pedidos-container .table-container {
        overflow-x: auto;
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--border);
        background: var(--light);
    }

    .gestionar-pedidos-container .table {
        margin-bottom: 0;
        background: var(--light);
        width: 100%;
        border-collapse: collapse;
    }

    .gestionar-pedidos-container .table thead {
        background: #f8f9fa;
        border-bottom: 2px solid var(--primary);
    }

    .gestionar-pedidos-container .table thead th {
        color: var(--secondary);
        font-weight: 700;
        padding: 1.25rem;
        border: none;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        vertical-align: middle;
    }

    .gestionar-pedidos-container .table thead th i {
        color: var(--primary);
        margin-right: 5px;
    }

    .gestionar-pedidos-container .table tbody tr {
        border-bottom: 1px solid #f0f0f0;
        transition: all 0.3s ease;
    }

    .gestionar-pedidos-container .table tbody tr:hover {
        background: rgba(255, 215, 0, 0.05);
    }

    .gestionar-pedidos-container .table tbody td {
        padding: 1rem 1.25rem;
        vertical-align: middle;
        color: var(--dark);
    }

    .gestionar-pedidos-container .table strong {
        color: var(--secondary);
        font-weight: 700;
    }

    /* Action Buttons - Mejorados */
    .gestionar-pedidos-container .btn-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 10px;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        border: 1px solid;
    }

    .gestionar-pedidos-container .btn-action i {
        font-size: 1rem;
    }

    .gestionar-pedidos-container .btn-view {
        background: var(--secondary);
        color: var(--primary);
        border-color: var(--primary);
    }

    .gestionar-pedidos-container .btn-view:hover {
        background: var(--primary);
        color: var(--secondary);
        transform: translateY(-2px);
    }

    .gestionar-pedidos-container .btn-download {
        background: var(--light);
        color: var(--success);
        border-color: var(--success);
    }

    .gestionar-pedidos-container .btn-download:hover {
        background: var(--success);
        color: var(--light);
        transform: translateY(-2px);
    }

    .gestionar-pedidos-container .btn-delete {
        background: var(--light);
        color: var(--danger);
        border-color: var(--danger);
    }

    .gestionar-pedidos-container .btn-delete:hover {
        background: var(--danger);
        color: var(--light);
        transform: translateY(-2px);
    }

    /* Badges */
    .gestionar-pedidos-container .badge-estatus {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .gestionar-pedidos-container .badge-pendiente {
        background: rgba(245, 158, 11, 0.15);
        color: var(--warning);
        border: 1px solid rgba(245, 158, 11, 0.3);
    }

    .gestionar-pedidos-container .badge-procesando {
        background: rgba(59, 130, 246, 0.15);
        color: var(--info);
        border: 1px solid rgba(59, 130, 246, 0.3);
    }

    .gestionar-pedidos-container .badge-enviado {
        background: rgba(139, 92, 246, 0.15);
        color: #8b5cf6;
        border: 1px solid rgba(139, 92, 246, 0.3);
    }

    .gestionar-pedidos-container .badge-entregado {
        background: rgba(16, 185, 129, 0.15);
        color: var(--success);
        border: 1px solid rgba(16, 185, 129, 0.3);
    }

    .gestionar-pedidos-container .badge-cancelado {
        background: rgba(239, 68, 68, 0.15);
        color: var(--danger);
        border: 1px solid rgba(239, 68, 68, 0.3);
    }

    .gestionar-pedidos-container .badge-pagado {
        background: rgba(255, 215, 0, 0.15);
        color: var(--primary-dark);
        border: 1px solid rgba(255, 215, 0, 0.3);
    }

    .gestionar-pedidos-container .badge-pago {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        background: rgba(0, 0, 0, 0.05);
        color: var(--secondary);
        border: 1px solid var(--border);
    }

    /* Detail Section */
    .gestionar-pedidos-container .detail-section {
        background: var(--light);
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--border);
    }

    .gestionar-pedidos-container .detail-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 2px solid var(--primary);
    }

    .gestionar-pedidos-container .detail-header h3 {
        color: var(--secondary);
        font-weight: 700;
        margin: 0;
    }

    .gestionar-pedidos-container .detail-header h3 i {
        color: var(--primary);
        margin-right: 0.5rem;
    }

    .gestionar-pedidos-container .btn-back {
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

    .gestionar-pedidos-container .btn-back:hover {
        border-color: var(--primary);
        color: var(--primary);
        transform: translateX(-2px);
    }

    .gestionar-pedidos-container .detail-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        margin-bottom: 2rem;
    }

    .gestionar-pedidos-container .detail-card {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 16px;
        border-left: 4px solid var(--primary);
    }

    .gestionar-pedidos-container .detail-card h5 {
        color: var(--secondary);
        font-weight: 700;
        margin-bottom: 1rem;
        font-size: 1rem;
    }

    .gestionar-pedidos-container .detail-card h5 i {
        color: var(--primary);
        margin-right: 0.5rem;
    }

    .gestionar-pedidos-container .detail-line {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--border);
    }

    .gestionar-pedidos-container .detail-line:last-child {
        border-bottom: none;
    }

    .gestionar-pedidos-container .detail-label {
        font-weight: 600;
        color: #6b7280;
        font-size: 0.95rem;
    }

    .gestionar-pedidos-container .detail-value {
        color: var(--secondary);
        font-weight: 500;
    }

    .gestionar-pedidos-container .products-table {
        width: 100%;
        border-collapse: collapse;
        margin: 1.5rem 0;
    }

    .gestionar-pedidos-container .products-table thead tr {
        background: #f8f9fa;
        border-bottom: 2px solid var(--primary);
    }

    .gestionar-pedidos-container .products-table th {
        padding: 1rem;
        text-align: left;
        font-weight: 700;
        color: var(--secondary);
        text-transform: uppercase;
        font-size: 0.75rem;
    }

    .gestionar-pedidos-container .products-table tbody tr {
        border-bottom: 1px solid var(--border);
    }

    .gestionar-pedidos-container .products-table tbody tr:hover {
        background: rgba(255, 215, 0, 0.05);
    }

    .gestionar-pedidos-container .products-table td {
        padding: 1rem;
        color: var(--dark);
    }

    .gestionar-pedidos-container .form-section {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 16px;
        margin-top: 1.5rem;
    }

    .gestionar-pedidos-container .form-section h5 {
        color: var(--secondary);
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .gestionar-pedidos-container .form-section h5 i {
        color: var(--primary);
        margin-right: 0.5rem;
    }

    .gestionar-pedidos-container .btn-primary-custom {
        background: var(--secondary);
        color: var(--light);
        border: 2px solid var(--primary);
        border-radius: 12px;
        padding: 0.75rem 2rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
    }

    .gestionar-pedidos-container .btn-primary-custom:hover {
        background: var(--primary);
        color: var(--secondary);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 215, 0, 0.3);
    }

    /* Empty State */
    .gestionar-pedidos-container .empty-state {
        text-align: center;
        padding: 3rem 2rem;
        color: #999999;
    }

    .gestionar-pedidos-container .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        color: var(--primary);
        opacity: 0.5;
    }

    .gestionar-pedidos-container .empty-state p {
        font-size: 1rem;
        margin: 0;
    }

    /* Modal */
    .gestionar-pedidos-container .modal-custom {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }

    .gestionar-pedidos-container .modal-content-custom {
        background: var(--light);
        border-radius: 20px;
        max-width: 500px;
        width: 90%;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
        overflow: hidden;
    }

    .gestionar-pedidos-container .modal-header-custom {
        background: var(--secondary);
        color: var(--primary);
        padding: 1.25rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .gestionar-pedidos-container .modal-header-custom h4 {
        margin: 0;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .gestionar-pedidos-container .modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: var(--primary);
        padding: 0;
        line-height: 1;
    }

    .gestionar-pedidos-container .modal-close:hover {
        opacity: 0.8;
    }

    .gestionar-pedidos-container .modal-body-custom {
        padding: 1.5rem;
    }

    .gestionar-pedidos-container .modal-footer-custom {
        padding: 1rem 1.5rem;
        border-top: 1px solid var(--border);
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .gestionar-pedidos-container .page-header h1 {
            font-size: 1.75rem;
        }

        .gestionar-pedidos-container .page-header i {
            font-size: 1.75rem;
        }

        .gestionar-pedidos-container .search-row {
            grid-template-columns: 1fr;
        }

        .gestionar-pedidos-container .stats-row {
            grid-template-columns: 1fr;
        }

        .gestionar-pedidos-container .table {
            font-size: 0.85rem;
        }

        .gestionar-pedidos-container .table th, 
        .gestionar-pedidos-container .table td {
            padding: 0.75rem 0.5rem;
        }

        .gestionar-pedidos-container .btn-action {
            padding: 0.4rem 0.8rem;
            font-size: 0.75rem;
        }

        .gestionar-pedidos-container .btn-action i {
            font-size: 0.85rem;
        }

        .gestionar-pedidos-container .detail-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .gestionar-pedidos-container .detail-row {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
    }
</style>

<div class="gestionar-pedidos-container">
    <div class="container-fluid" style="max-width: 1400px; margin: 0 auto; padding: 0 1rem;">
        <div class="page-header">
            <h1>
                <i class="bi bi-bag-check"></i>
                Gestionar Pedidos
            </h1>
            <p>Visualiza, gestiona y controla todos los pedidos de tu tienda</p>
        </div>

        <!-- Estadísticas rápidas -->
        <div class="stats-row">
            <div class="stat-card">
                <h6><i class="bi bi-cart"></i> Total Pedidos</h6>
                <div class="stat-value"><?= count($compras) ?></div>
            </div>
            <div class="stat-card">
                <h6><i class="bi bi-clock-history"></i> Pendientes</h6>
                <div class="stat-value"><?= array_reduce($compras, fn($c, $r) => $c + ($r["status"] == "pendiente" ? 1 : 0), 0) ?></div>
            </div>
            <div class="stat-card">
                <h6><i class="bi bi-truck"></i> En Proceso</h6>
                <div class="stat-value"><?= array_reduce($compras, fn($c, $r) => $c + (in_array($r["status"], ["procesando", "enviado"]) ? 1 : 0), 0) ?></div>
            </div>
            <div class="stat-card">
                <h6><i class="bi bi-cash-stack"></i> Total Ventas</h6>
                <div class="stat-value">$<?= number_format(array_reduce($compras, fn($c, $r) => $c + $r["total"], 0), 2) ?></div>
            </div>
        </div>

        <?php if ($detalles_pedido && $pedido_actual): ?>
            <!-- Vista de detalles del pedido -->
            <div class="detail-section">
                <div class="detail-header">
                    <h3><i class="bi bi-box-seam"></i> Pedido #<?php echo $pedido_actual['id']; ?></h3>
                    <div style="display: flex; gap: 1rem;">
                        <a href="include/generar_factura.php?id=<?php echo $pedido_actual['id']; ?>" class="btn-back" style="background: var(--success); color: white; border-color: var(--success);">
                            <i class="bi bi-download"></i> Descargar Factura
                        </a>
                        <a href="index.php?mod=GestionarPedidos" class="btn-back">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>

                <div class="detail-row">
                    <div class="detail-card">
                        <h5><i class="bi bi-person"></i> Información del Cliente</h5>
                        <div class="detail-line">
                            <span class="detail-label">Nombre:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($pedido_actual['nombre'] . ' ' . $pedido_actual['apellido']); ?></span>
                        </div>
                        <div class="detail-line">
                            <span class="detail-label">Email:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($pedido_actual['email']); ?></span>
                        </div>
                        <div class="detail-line">
                            <span class="detail-label">Teléfono:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($pedido_actual['telefono'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="detail-line">
                            <span class="detail-label">Documento:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($pedido_actual['documento'] ?? 'N/A'); ?></span>
                        </div>
                    </div>

                    <div class="detail-card">
                        <h5><i class="bi bi-receipt"></i> Información del Pedido</h5>
                        <div class="detail-line">
                            <span class="detail-label">Transacción:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($pedido_actual['id_transaccion']); ?></span>
                        </div>
                        <div class="detail-line">
                            <span class="detail-label">Fecha:</span>
                            <span class="detail-value"><?php echo date('d/m/Y H:i', strtotime($pedido_actual['fecha'])); ?></span>
                        </div>
                        <div class="detail-line">
                            <span class="detail-label">Medio de Pago:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($pedido_actual['medio_pago']); ?></span>
                        </div>
                        <div class="detail-line">
                            <span class="detail-label">Total:</span>
                            <span class="detail-value" style="color: var(--success); font-weight: 700;">$<?php echo number_format($pedido_actual['total'], 2, ',', '.'); ?></span>
                        </div>
                    </div>
                </div>

                <hr style="border: none; border-top: 2px solid var(--border); margin: 2rem 0;">

                <h5 style="color: var(--secondary); font-weight: 700; margin-bottom: 1.5rem;">
                    <i class="bi bi-basket"></i> Productos del Pedido
                </h5>
                <table class="products-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Precio Unitario</th>
                            <th>Cantidad</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detalles_pedido as $detalle): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($detalle['titulo']); ?></td>
                                <td>$<?php echo number_format($detalle['precio'], 2, ',', '.'); ?></td>
                                <td style="text-align: center; font-weight: 600;"><?php echo $detalle['cantidad']; ?></td>
                                <td style="color: var(--success); font-weight: 600;">$<?php echo number_format($detalle['precio'] * $detalle['cantidad'], 2, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="form-section">
                    <h5><i class="bi bi-arrow-repeat"></i> Cambiar Estado del Pedido</h5>
                    <form method="POST" style="display: flex; gap: 1rem; align-items: flex-end;">
                        <input type="hidden" name="accion" value="cambiar_estado">
                        <input type="hidden" name="id_compra" value="<?php echo $pedido_actual['id']; ?>">
                        <div style="flex: 1;">
                            <label class="form-label" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--secondary);">Nuevo Estado</label>
                            <select name="nuevo_estado" class="form-select" required style="width: 100%;">
                                <option value="pagado" <?php if ($pedido_actual['status'] == 'pagado') echo 'selected'; ?>>Pagado</option>
                                <option value="pendiente" <?php if ($pedido_actual['status'] == 'pendiente') echo 'selected'; ?>>Pendiente</option>
                                <option value="procesando" <?php if ($pedido_actual['status'] == 'procesando') echo 'selected'; ?>>Procesando</option>
                                <option value="enviado" <?php if ($pedido_actual['status'] == 'enviado') echo 'selected'; ?>>Enviado</option>
                                <option value="entregado" <?php if ($pedido_actual['status'] == 'entregado') echo 'selected'; ?>>Entregado</option>
                                <option value="cancelado" <?php if ($pedido_actual['status'] == 'cancelado') echo 'selected'; ?>>Cancelado</option>
                            </select>
                        </div>
                        <button type="submit" class="btn-primary-custom">
                            <i class="bi bi-check-circle"></i> Actualizar
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <!-- Filtros -->
        <div class="filter-section">
            <h5><i class="bi bi-funnel"></i> Filtrar y Buscar</h5>
            <form method="GET" class="row g-3">
                <input type="hidden" name="mod" value="GestionarPedidos">
                <div class="col-lg-3 col-md-6">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="todos" <?php if ($filtro_estado == 'todos') echo 'selected'; ?>>Todos</option>
                        <option value="pagado" <?php if ($filtro_estado == 'pagado') echo 'selected'; ?>>Pagado</option>
                        <option value="pendiente" <?php if ($filtro_estado == 'pendiente') echo 'selected'; ?>>Pendiente</option>
                        <option value="procesando" <?php if ($filtro_estado == 'procesando') echo 'selected'; ?>>Procesando</option>
                        <option value="enviado" <?php if ($filtro_estado == 'enviado') echo 'selected'; ?>>Enviado</option>
                        <option value="entregado" <?php if ($filtro_estado == 'entregado') echo 'selected'; ?>>Entregado</option>
                        <option value="cancelado" <?php if ($filtro_estado == 'cancelado') echo 'selected'; ?>>Cancelado</option>
                    </select>
                </div>
                <div class="col-lg-4 col-md-6">
                    <label class="form-label">Buscar</label>
                    <input type="text" name="cliente" class="form-control" placeholder="Nombre, código transacción, email..." value="<?php echo htmlspecialchars($filtro_cliente); ?>">
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label">Fecha</label>
                    <input type="date" name="fecha" class="form-control" value="<?php echo $filtro_fecha; ?>">
                </div>
                <div class="col-lg-3 col-md-6" style="display: flex; flex-direction: column; justify-content: flex-end; gap: 0.5rem;">
                    <button type="submit" class="btn-filter" style="width: 100%;">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>

        <!-- Sección de Reportes -->
        <div class="filter-section report-section">
            <h5 style="color: var(--primary-dark);">
                <i class="bi bi-file-earmark-spreadsheet"></i> Generar Reportes
            </h5>
            <form method="GET" class="row g-3" id="formReportes">
                <input type="hidden" name="mod" value="GestionarPedidos">
                <input type="hidden" name="estado" value="<?php echo htmlspecialchars($filtro_estado != 'todos' ? $filtro_estado : ''); ?>">
                <input type="hidden" name="cliente" value="<?php echo htmlspecialchars($filtro_cliente); ?>">
                
                <div class="col-lg-5 col-md-6">
                    <div style="display: flex; gap: 0.5rem;">
                        <input type="date" name="fecha_inicio" class="form-control" placeholder="Desde..." style="flex: 1;">
                        <span style="padding: 0.75rem; color: #999;">a</span>
                        <input type="date" name="fecha_fin" class="form-control" placeholder="Hasta..." style="flex: 1;">
                    </div>
                </div>
                
                <div class="col-lg-7 col-md-6" style="display: flex; flex-direction: column; justify-content: flex-end; gap: 0.5rem;">
                    <div style="display: flex; gap: 0.5rem;">
                        <button type="submit" formaction="include/generar_reporte_consolidado.php" class="btn-filter" style="flex: 1; background: var(--secondary);">
                            <i class="bi bi-file-earmark-pdf"></i> Reporte Consolidado
                        </button>
                        <button type="button" class="btn-filter" style="flex: 1; background: var(--secondary);" onclick="abrirSelectorFechas()">
                            <i class="bi bi-calendar-event"></i> Por Calendario
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Modal Calendario -->
        <div id="modalCalendario" class="modal-custom">
            <div class="modal-content-custom">
                <div class="modal-header-custom">
                    <h4><i class="bi bi-calendar"></i> Seleccionar Fechas</h4>
                    <button onclick="cerrarSelectorFechas()" class="modal-close">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <form method="GET" action="include/generar_reporte_consolidado.php">
                    <div class="modal-body-custom">
                        <input type="hidden" name="mod" value="GestionarPedidos">
                        <input type="hidden" name="estado" value="<?php echo htmlspecialchars($filtro_estado != 'todos' ? $filtro_estado : ''); ?>">
                        <input type="hidden" name="cliente" value="<?php echo htmlspecialchars($filtro_cliente); ?>">
                        
                        <div style="margin-bottom: 1.5rem;">
                            <label class="form-label" style="display: block; margin-bottom: 0.75rem;">Fecha Inicio</label>
                            <input type="date" name="fecha_inicio" class="form-control" required>
                        </div>
                        <div style="margin-bottom: 1.5rem;">
                            <label class="form-label" style="display: block; margin-bottom: 0.75rem;">Fecha Fin</label>
                            <input type="date" name="fecha_fin" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer-custom">
                        <button type="button" onclick="cerrarSelectorFechas()" class="btn-clear">Cancelar</button>
                        <button type="submit" class="btn-filter">
                            <i class="bi bi-download"></i> Generar Reporte
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de pedidos -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th><i class="bi bi-hash"></i> #ID</th>
                        <th><i class="bi bi-upc-scan"></i> Transacción</th>
                        <th><i class="bi bi-person"></i> Cliente</th>
                        <th><i class="bi bi-envelope"></i> Email</th>
                        <th><i class="bi bi-calendar"></i> Fecha</th>
                        <th><i class="bi bi-cash-stack"></i> Total</th>
                        <th><i class="bi bi-credit-card"></i> Pago</th>
                        <th><i class="bi bi-info-circle"></i> Estado</th>
                        <th><i class="bi bi-eye"></i> Ver</th>
                        <th><i class="bi bi-receipt"></i> Factura</th>
                        <th><i class="bi bi-trash"></i> Eliminar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($compras) > 0): ?>
                        <?php foreach ($compras as $compra): ?>
                            <tr>
                                <td><strong><?php echo $compra['id']; ?></strong></td>
                                <td><?php echo htmlspecialchars($compra['id_transaccion']); ?></td>
                                <td><?php echo htmlspecialchars($compra['nombre'] . ' ' . $compra['apellido']); ?></td>
                                <td><?php echo htmlspecialchars($compra['email']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($compra['fecha'])); ?></td>
                                <td><strong style="color: var(--success);">$<?php echo number_format($compra['total'], 2, ',', '.'); ?></strong></td>
                                <td>
                                    <span class="badge-pago"><?php echo htmlspecialchars($compra['medio_pago']); ?></span>
                                </td>
                                <td>
                                    <span class="badge-estatus badge-<?php echo htmlspecialchars($compra['status']); ?>">
                                        <?php echo ucfirst($compra['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="index.php?mod=GestionarPedidos&id=<?php echo $compra['id']; ?>" class="btn-action btn-view" title="Ver detalles">
                                        <i class="bi bi-eye"></i> Ver
                                    </a>
                                </td>
                                <td>
                                    <a href="include/generar_factura.php?id=<?php echo $compra['id']; ?>" class="btn-action btn-download" title="Descargar factura">
                                        <i class="bi bi-receipt"></i> Factura
                                    </a>
                                </td>
                                <td>
                                    <button class="btn-action btn-delete" onclick="confirmarEliminar(<?php echo $compra['id']; ?>)" title="Eliminar">
                                        <i class="bi bi-trash"></i> Eliminar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11">
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <p>No se encontraron pedidos</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Formulario oculto para eliminar -->
<form id="formEliminar" method="POST" style="display: none;">
    <input type="hidden" name="accion" value="eliminar">
    <input type="hidden" name="id_eliminar" id="idEliminar">
</form>

<script>
    function confirmarEliminar(id) {
        if (confirm('¿Estás seguro de que deseas eliminar este pedido? Esta acción no se puede deshacer.')) {
            document.getElementById('idEliminar').value = id;
            document.getElementById('formEliminar').submit();
        }
    }

    function abrirSelectorFechas() {
        const modal = document.getElementById('modalCalendario');
        modal.style.display = 'flex';
    }

    function cerrarSelectorFechas() {
        const modal = document.getElementById('modalCalendario');
        modal.style.display = 'none';
    }

    // Cerrar modal cuando se hace clic fuera de él
    document.getElementById('modalCalendario').addEventListener('click', function(e) {
        if (e.target === this) {
            cerrarSelectorFechas();
        }
    });

    // Cerrar con tecla ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            cerrarSelectorFechas();
        }
    });
</script>