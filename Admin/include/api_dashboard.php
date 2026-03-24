<?php
// Configurar headers para CORS y JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejo de preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Incluir configuración
require_once dirname(__FILE__) . '/config.php';

try {
    // Crear conexión directa con mysqli
    $conexion = new mysqli(
        Config::DB_HOST,
        Config::DB_USER,
        Config::DB_PASS,
        Config::DB_NAME
    );
    
    // Configurar charset
    $conexion->set_charset("utf8mb4");
    
    // Verificar conexión
    if ($conexion->connect_error) {
        throw new Exception("Error de conexión: " . $conexion->connect_error);
    }
    
    $action = isset($_GET['action']) ? trim($_GET['action']) : '';
    
    // Acción: Estadísticas
    if($action === 'stats') {
        $stats = [];
        
        // Total de compras este mes
        $result = $conexion->query("SELECT COUNT(*) as count FROM compra WHERE DATE(fecha) >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $row = $result->fetch_assoc();
        $stats['total_orders'] = intval($row['count']);
        
        // Total de ingresos este mes
        $result = $conexion->query("SELECT COALESCE(SUM(total), 0) as total FROM compra WHERE DATE(fecha) >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $row = $result->fetch_assoc();
        $stats['total_revenue'] = floatval($row['total']);
        
        // Total de clientes
        $result = $conexion->query("SELECT COUNT(*) as count FROM clientes");
        $row = $result->fetch_assoc();
        $stats['total_users'] = intval($row['count']);
        
        // Total de productos activos
        $result = $conexion->query("SELECT COUNT(*) as count FROM productos WHERE activo = 1");
        $row = $result->fetch_assoc();
        $stats['total_products'] = intval($row['count']);
        
        // Calcular tendencia de órdenes
        $result = $conexion->query("SELECT COUNT(*) as count FROM compra WHERE DATE(fecha) BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $row = $result->fetch_assoc();
        $prev_orders = intval($row['count']);
        $stats['orders_trend'] = $prev_orders > 0 ? round((($stats['total_orders'] - $prev_orders) / $prev_orders) * 100, 2) : 0;
        
        // Calcular tendencia de ingresos
        $result = $conexion->query("SELECT COALESCE(SUM(total), 0) as total FROM compra WHERE DATE(fecha) BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $row = $result->fetch_assoc();
        $prev_revenue = floatval($row['total']);
        $stats['revenue_trend'] = $prev_revenue > 0 ? round((($stats['total_revenue'] - $prev_revenue) / $prev_revenue) * 100, 2) : 0;
        
        echo json_encode($stats);
    }
    
    // Acción: Ventas mensuales
    else if($action === 'sales') {
        $data = [];
        $result = $conexion->query("
            SELECT DATE_FORMAT(fecha, '%Y-%m') as mes, COUNT(*) as ordenes, COALESCE(SUM(total), 0) as total
            FROM compra
            WHERE fecha >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(fecha, '%Y-%m')
            ORDER BY mes ASC
        ");
        
        if($result) {
            while($row = $result->fetch_assoc()) {
                $data[] = [
                    'mes' => $row['mes'],
                    'ordenes' => intval($row['ordenes']),
                    'total' => floatval($row['total'])
                ];
            }
        }
        
        echo json_encode($data);
    }
    
    // Acción: Productos más vendidos
    else if($action === 'products') {
        $data = [];
        $result = $conexion->query("
            SELECT 
                p.id, p.titulo, p.precio,
                COUNT(dc.id) as vendidos,
                COALESCE(SUM(dc.cantidad * dc.precio), 0) as ingresos
            FROM productos p
            LEFT JOIN detalle_compra dc ON p.id = dc.id_producto
            WHERE p.activo = 1
            GROUP BY p.id, p.titulo, p.precio
            ORDER BY vendidos DESC
            LIMIT 5
        ");
        
        if($result) {
            while($row = $result->fetch_assoc()) {
                $data[] = [
                    'id' => intval($row['id']),
                    'nombre' => $row['titulo'],
                    'precio' => floatval($row['precio']),
                    'vendidos' => intval($row['vendidos']),
                    'ingresos' => floatval($row['ingresos'])
                ];
            }
        }
        
        echo json_encode($data);
    }
    
    // Acción: Categorías
    else if($action === 'categories') {
        $data = [];
        $result = $conexion->query("
            SELECT 
                id_categoria as categoria,
                COUNT(*) as cantidad
            FROM productos
            WHERE activo = 1
            GROUP BY id_categoria
            HAVING cantidad > 0
            ORDER BY cantidad DESC
        ");
        
        if($result) {
            while($row = $result->fetch_assoc()) {
                $categoria = intval($row['categoria']) > 0 ? 'Categoría ' . $row['categoria'] : 'Sin categoría';
                $data[] = [
                    'label' => $categoria,
                    'value' => intval($row['cantidad'])
                ];
            }
        }
        
        echo json_encode($data);
    }
    
    // Acción: Crecimiento de usuarios
    else if($action === 'users') {
        $data = [];
        $result = $conexion->query("
            SELECT DATE_FORMAT(fecha_alta, '%Y-%m') as mes, COUNT(*) as usuarios
            FROM clientes
            WHERE fecha_alta >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(fecha_alta, '%Y-%m')
            ORDER BY mes ASC
        ");
        
        if($result) {
            while($row = $result->fetch_assoc()) {
                $data[] = [
                    'mes' => $row['mes'],
                    'usuarios' => intval($row['usuarios'])
                ];
            }
        }
        
        echo json_encode($data);
    }
    
    // Acción: Órdenes recientes
    else if($action === 'orders') {
        $data = [];
        $result = $conexion->query("
            SELECT 
                c.id, c.id_transaccion, c.total, c.fecha, c.status,
                cl.nombre, cl.apellido
            FROM compra c
            JOIN clientes cl ON c.id_cliente = cl.id
            ORDER BY c.fecha DESC
            LIMIT 8
        ");
        
        if($result) {
            while($row = $result->fetch_assoc()) {
                $estado_label = '';
                $estado_class = '';
                
                switch(strtoupper($row['status'])) {
                    case 'COMPLETED':
                        $estado_label = 'Completado';
                        $estado_class = 'success-btn';
                        break;
                    case 'PENDING':
                        $estado_label = 'Pendiente';
                        $estado_class = 'close-btn';
                        break;
                    case 'PROCESSING':
                        $estado_label = 'Procesando';
                        $estado_class = 'warning-btn';
                        break;
                    case 'CANCELLED':
                        $estado_label = 'Cancelado';
                        $estado_class = 'danger-btn';
                        break;
                    default:
                        $estado_label = $row['status'];
                        $estado_class = '';
                }
                
                $data[] = [
                    'id' => intval($row['id']),
                    'numero' => $row['id_transaccion'],
                    'cliente' => trim($row['nombre'] . ' ' . $row['apellido']),
                    'total' => floatval($row['total']),
                    'fecha' => $row['fecha'],
                    'estado' => $row['status'],
                    'estado_label' => $estado_label,
                    'estado_class' => $estado_class
                ];
            }
        }
        
        echo json_encode($data);
    }
    
    else {
        http_response_code(400);
        echo json_encode(['error' => 'Acción no válida: ' . $action]);
    }
    
    $conexion->close();
    
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
?>
