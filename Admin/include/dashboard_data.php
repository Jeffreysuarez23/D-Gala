<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once dirname(__FILE__) . '/db.php';
require_once dirname(__FILE__) . '/config.php';

try {
    $db = new Database();
    $conexion = $db->getConexion();
    
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    
    // Obtener estadísticas generales
    if($action === 'stats') {
        $stats = [
            'total_orders' => 0,
            'total_revenue' => 0,
            'total_users' => 0,
            'total_products' => 0,
            'orders_trend' => 0,
            'revenue_trend' => 0
        ];
        
        // Total de compras este mes
        $query = $conexion->query("SELECT COUNT(*) as count FROM compra WHERE DATE(fecha) >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $result = $query->fetch(PDO::FETCH_ASSOC);
        $stats['total_orders'] = intval($result['count'] ?? 0);
        
        // Total de ingresos este mes
        $query = $conexion->query("SELECT COALESCE(SUM(total), 0) as total FROM compra WHERE DATE(fecha) >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $result = $query->fetch(PDO::FETCH_ASSOC);
        $stats['total_revenue'] = floatval($result['total'] ?? 0);
        
        // Total de clientes/usuarios
        $query = $conexion->query("SELECT COUNT(*) as count FROM clientes");
        $result = $query->fetch(PDO::FETCH_ASSOC);
        $stats['total_users'] = intval($result['count'] ?? 0);
        
        // Total de productos activos
        $query = $conexion->query("SELECT COUNT(*) as count FROM productos WHERE activo = 1");
        $result = $query->fetch(PDO::FETCH_ASSOC);
        $stats['total_products'] = intval($result['count'] ?? 0);
        
        // Calcular tendencias
        $query = $conexion->query("SELECT COUNT(*) as count FROM compra WHERE DATE(fecha) BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $result = $query->fetch(PDO::FETCH_ASSOC);
        $prev_orders = intval($result['count'] ?? 0);
        $stats['orders_trend'] = $prev_orders > 0 ? round((($stats['total_orders'] - $prev_orders) / $prev_orders) * 100, 2) : 0;
        
        $query = $conexion->query("SELECT COALESCE(SUM(total), 0) as total FROM compra WHERE DATE(fecha) BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $result = $query->fetch(PDO::FETCH_ASSOC);
        $prev_revenue = floatval($result['total'] ?? 0);
        $stats['revenue_trend'] = $prev_revenue > 0 ? round((($stats['total_revenue'] - $prev_revenue) / $prev_revenue) * 100, 2) : 0;
        
        echo json_encode($stats);
    }
    
    // Obtener ventas mensuales
    else if($action === 'sales') {
        $data = [];
        $query = $conexion->query("
            SELECT DATE_FORMAT(fecha, '%Y-%m') as mes, COUNT(*) as ordenes, COALESCE(SUM(total), 0) as total
            FROM compra
            WHERE fecha >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(fecha, '%Y-%m')
            ORDER BY mes ASC
        ");
        
        while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $data[] = [
                'mes' => $row['mes'],
                'ordenes' => intval($row['ordenes']),
                'total' => floatval($row['total'])
            ];
        }
        
        echo json_encode($data);
    }
    
    // Obtener productos más vendidos
    else if($action === 'products') {
        $data = [];
        $query = $conexion->query("
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
        
        while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $data[] = [
                'id' => intval($row['id']),
                'nombre' => $row['titulo'],
                'precio' => floatval($row['precio']),
                'vendidos' => intval($row['vendidos']),
                'ingresos' => floatval($row['ingresos'])
            ];
        }
        
        echo json_encode($data);
    }
    
    // Obtener distribución de categorías
    else if($action === 'categories') {
        $data = [];
        $query = $conexion->query("
            SELECT 
                id_categoria as categoria,
                COUNT(*) as cantidad
            FROM productos
            WHERE activo = 1
            GROUP BY id_categoria
            HAVING cantidad > 0
            ORDER BY cantidad DESC
        ");
        
        while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $categoria = intval($row['categoria']) > 0 ? 'Categoría ' . $row['categoria'] : 'Sin categoría';
            $data[] = [
                'label' => $categoria,
                'value' => intval($row['cantidad'])
            ];
        }
        
        echo json_encode($data);
    }
    
    // Obtener crecimiento de usuarios
    else if($action === 'users') {
        $data = [];
        $query = $conexion->query("
            SELECT DATE_FORMAT(fecha_alta, '%Y-%m') as mes, COUNT(*) as usuarios
            FROM clientes
            WHERE fecha_alta >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(fecha_alta, '%Y-%m')
            ORDER BY mes ASC
        ");
        
        while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $data[] = [
                'mes' => $row['mes'],
                'usuarios' => intval($row['usuarios'])
            ];
        }
        
        echo json_encode($data);
    }
    
    // Obtener órdenes recientes
    else if($action === 'orders') {
        $data = [];
        $query = $conexion->query("
            SELECT 
                c.id, c.id_transaccion, c.total, c.fecha, c.status,
                cl.nombre, cl.apellido, cl.email
            FROM compra c
            JOIN clientes cl ON c.id_cliente = cl.id
            ORDER BY c.fecha DESC
            LIMIT 8
        ");
        
        while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $estado_label = '';
            $estado_class = '';
            
            switch($row['status']) {
                case 'COMPLETED':
                    $estado_label = 'COMPLETADO';
                    $estado_class = 'success-btn';
                    break;
                case 'PENDING':
                    $estado_label = 'PENDIENTE';
                    $estado_class = 'close-btn';
                    break;
                case 'PROCESSING':
                    $estado_label = 'PROCESANDO';
                    $estado_class = 'warning-btn';
                    break;
                case 'CANCELLED':
                    $estado_label = 'CANCELADO';
                    $estado_class = 'danger-btn';
                    break;
                default:
                    $estado_label = $row['status'];
                    $estado_class = 'secondary-btn';
            }
            
            $data[] = [
                'id' => intval($row['id']),
                'numero' => $row['id_transaccion'],
                'cliente' => $row['nombre'] . ' ' . $row['apellido'],
                'total' => floatval($row['total']),
                'fecha' => $row['fecha'],
                'estado' => $row['status'],
                'estado_label' => $estado_label,
                'estado_class' => $estado_class
            ];
        }
        
        echo json_encode($data);
    }
    
    else {
        echo json_encode(['error' => 'Acción no válida']);
    }
    
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
