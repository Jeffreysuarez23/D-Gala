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
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Pedidos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            --primary: #6366f1;
            --secondary: #8b5cf6;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            --dark: #1f2937;
            --light: #f9fafb;
        }

        * {
            transition: all 0.3s ease;
        }

        body {
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
            min-height: 100vh;
        }

        main {
            padding: 2rem 0;
        }

        .page-header {
            background: var(--primary-gradient);
            color: white;
            padding: 2.5rem 2rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            box-shadow: 0 20px 50px rgba(99, 102, 241, 0.2);
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            position: relative;
            z-index: 1;
        }

        .page-header p {
            font-size: 1rem;
            opacity: 0.95;
            position: relative;
            z-index: 1;
        }

        .page-header i {
            font-size: 2.5rem;
        }

        .card-custom {
            background: white;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .card-custom:hover {
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
        }

        .filter-section {
            padding: 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        .filter-section h5 {
            color: var(--dark);
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-control, .form-select {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            background: #f9fafb;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .form-label {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .btn-filter {
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }

        .btn-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);
        }

        .table-container {
            overflow-x: auto;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .table {
            margin-bottom: 0;
            background: white;
        }

        .table thead {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            border-bottom: 2px solid #e5e7eb;
        }

        .table thead th {
            color: var(--dark);
            font-weight: 700;
            padding: 1.25rem;
            border: none;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        .table tbody tr {
            border-bottom: 1px solid #f3f4f6;
        }

        .table tbody tr:hover {
            background: linear-gradient(90deg, #f9fafb 0%, #f3f4f6 100%);
        }

        .table tbody td {
            padding: 1rem 1.25rem;
            vertical-align: middle;
            color: var(--dark);
        }

        .table strong {
            color: var(--primary);
            font-weight: 700;
        }

        .badge-custom {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-pendiente {
            background: #fef3c7;
            color: #b45309;
        }

        .badge-procesando {
            background: #bfdbfe;
            color: #1e3a8a;
        }

        .badge-enviado {
            background: #c7d2fe;
            color: #3730a3;
        }

        .badge-entregado {
            background: #bbf7d0;
            color: #065f46;
        }

        .badge-cancelado {
            background: #ffafa9;
            color: #e00b0b;
        }

        .badge-pagado {
            background: #d1fae5;
            color: #047857;
        }

        .btn-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .btn-view {
            background: #dbeafe;
            color: var(--info);
            margin-right: 0.5rem;
        }

        .btn-view:hover {
            background: var(--info);
            color: white;
            transform: translateY(-2px);
        }

        .btn-download {
            background: #d1fae5;
            color: var(--success);
            margin-right: 0.5rem;
        }

        .btn-download:hover {
            background: var(--success);
            color: white;
            transform: translateY(-2px);
        }

        .btn-delete {
            background: #fee2e2;
            color: var(--danger);
        }

        .btn-delete:hover {
            background: var(--danger);
            color: white;
            transform: translateY(-2px);
        }

        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: #9ca3af;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-state p {
            font-size: 1.1rem;
        }

        .detail-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .detail-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid #f3f4f6;
        }

        .detail-header h3 {
            color: var(--dark);
            font-weight: 700;
            margin: 0;
        }

        .btn-back {
            background: #f3f4f6;
            color: var(--dark);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-back:hover {
            background: var(--dark);
            color: white;
            transform: translateX(-2px);
        }

        .detail-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .detail-card {
            background: #f9fafb;
            padding: 1.5rem;
            border-radius: 8px;
            border-left: 4px solid var(--primary);
        }

        .detail-card h5 {
            color: var(--dark);
            font-weight: 700;
            margin-bottom: 1rem;
            font-size: 1rem;
        }

        .detail-line {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .detail-line:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: #6b7280;
            font-size: 0.95rem;
        }

        .detail-value {
            color: var(--dark);
            font-weight: 500;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin: 1.5rem 0;
        }

        .products-table thead tr {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            border-bottom: 2px solid #e5e7eb;
        }

        .products-table th {
            padding: 1rem;
            text-align: left;
            font-weight: 700;
            color: var(--dark);
            text-transform: uppercase;
            font-size: 0.8rem;
        }

        .products-table tbody tr {
            border-bottom: 1px solid #f3f4f6;
        }

        .products-table tbody tr:hover {
            background: #fafafa;
        }

        .products-table td {
            padding: 1rem;
            color: var(--dark);
        }

        .form-section {
            background: #f9fafb;
            padding: 1.5rem;
            border-radius: 8px;
            margin-top: 1.5rem;
        }

        .form-section h5 {
            color: var(--dark);
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .btn-primary-custom {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);
        }

        .btn-secondary-custom {
            background: #e5e7eb;
            color: var(--dark);
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-secondary-custom:hover {
            background: var(--dark);
            color: white;
        }

        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 1.75rem;
            }

            .page-header i {
                font-size: 1.75rem;
            }

            .table {
                font-size: 0.9rem;
            }

            .btn-icon {
                width: 32px;
                height: 32px;
                font-size: 0.9rem;
            }

            .detail-row {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <main>
        <div class="container-fluid" style="max-width: 1400px; margin: 0 auto;">
            
            <div class="page-header">
                <h1>
                    <i class="bi bi-bag-check"></i>
                    Gestionar Pedidos
                </h1>
                <p>Visualiza, gestiona y controla todos los pedidos de tu tienda</p>
            </div>

            <?php if ($detalles_pedido && $pedido_actual): ?>
                <!-- Vista de detalles del pedido -->
                <div class="detail-section">
                    <div class="detail-header">
                        <h3><i class="bi bi-box-seam"></i> Pedido #<?php echo $pedido_actual['id']; ?></h3>
                        <div style="display: flex; gap: 1rem;">
                            <a href="include/generar_factura.php?id=<?php echo $pedido_actual['id']; ?>" class="btn-back" style="background: var(--success); color: white;">
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

                    <hr style="border: none; border-top: 2px solid #f3f4f6; margin: 2rem 0;">

                    <h5 style="color: var(--dark); font-weight: 700; margin-bottom: 1.5rem;">
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
                                <label class="form-label">Nuevo Estado</label>
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

                <hr style="border: none; border-top: 2px solid #f3f4f6; margin: 3rem 0;">

            <?php endif; ?>

            <!-- Filtros -->
            <div class="filter-section">
                
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

            <!-- Sección de Reportes Mejorada -->
            <div class="filter-section" style="background: linear-gradient(135deg, #f0f4ff 0%, #f8f5ff 100%); border-left: 4px solid #6366f1;">
                <h5 style="color: #6366f1;">
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
                            <button type="submit" formaction="include/generar_reporte_consolidado.php" class="btn-filter" style="flex: 1; background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                <i class="bi bi-file-earmark-pdf"></i> Reporte Consolidado
                            </button>
                            <button type="button" class="btn-filter" style="flex: 1; background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); border: none;" onclick="abrirSelectorFechas()">
                                <i class="bi bi-calendar-event"></i> Por Calendario
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Modal Calendario -->
            <div id="modalCalendario" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
                <div style="background: white; padding: 2rem; border-radius: 12px; max-width: 600px; width: 90%; box-shadow: 0 20px 50px rgba(0,0,0,0.3);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                        <h4 style="color: var(--dark); font-weight: 700; margin: 0;">
                            <i class="bi bi-calendar"></i> Seleccionar Fechas para Reporte
                        </h4>
                        <button onclick="cerrarSelectorFechas()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #999;">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    <form method="GET" action="include/generar_reporte_consolidado.php">
                        <input type="hidden" name="mod" value="GestionarPedidos">
                        <input type="hidden" name="estado" value="<?php echo htmlspecialchars($filtro_estado != 'todos' ? $filtro_estado : ''); ?>">
                        <input type="hidden" name="cliente" value="<?php echo htmlspecialchars($filtro_cliente); ?>">
                        
                        <div style="margin-bottom: 1.5rem;">
                            <label class="form-label" style="display: block; margin-bottom: 0.75rem;">Fecha Inicio</label>
                            <input type="date" name="fecha_inicio" class="form-control" required>
                        </div>
                        <div style="margin-bottom: 2rem;">
                            <label class="form-label" style="display: block; margin-bottom: 0.75rem;">Fecha Fin</label>
                            <input type="date" name="fecha_fin" class="form-control" required>
                        </div>
                        <div style="display: flex; gap: 1rem;">
                            <button type="submit" class="btn-filter" style="flex: 1; background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
                                <i class="bi bi-download"></i> Generar Reporte
                            </button>
                            <button type="button" onclick="cerrarSelectorFechas()" style="flex: 1; background: #e5e7eb; color: var(--dark); border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; cursor: pointer;">
                                Cancelar
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
                                <th>#ID</th>
                                <th>Transacción</th>
                                <th>Cliente</th>
                                <th>Email</th>
                                <th>Fecha</th>
                                <th>Total</th>
                                <th>Pago</th>
                                <th>Estado</th>
                                <th>Ver</th>
                                <th>Factura</th>
                                <th>Eliminar</th>
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
                                            <span class="badge-custom badge-<?php echo htmlspecialchars($compra['medio_pago']); ?>">
                                                <?php echo htmlspecialchars($compra['medio_pago']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge-custom badge-<?php echo htmlspecialchars($compra['status']); ?>">
                                                <?php echo ucfirst($compra['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="index.php?mod=GestionarPedidos&id=<?php echo $compra['id']; ?>" class="btn-icon btn-view" title="Ver detalles">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                        <td>
                                            <a href="include/generar_factura.php?id=<?php echo $compra['id']; ?>" class="btn-icon btn-download" title="Descargar factura" style="background: #d1fae5; color: var(--success); margin-right: 0.5rem;">
                                                <i class="bi bi-receipt"></i>
                                            </a>
                                        </td>
                                        <td>
                                            <button class="btn-icon btn-delete" onclick="confirmarEliminar(<?php echo $compra['id']; ?>)" title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9">
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
    </main>

    <!-- Modal de confirmación para eliminar -->
    <form id="formEliminar" method="POST" style="display: none;">
        <input type="hidden" name="accion" value="eliminar">
        <input type="hidden" name="id_eliminar" id="idEliminar">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
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
</body>
</html>
