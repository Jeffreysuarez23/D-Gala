<?php
/**
 * CRUD de Pedidos/Compras
 * Funciones para operaciones en la tabla de compras
 */

require_once("../include/db.php");

$db = new Database();
$conexion = $db->getConexion();

class PedidosCRUD {
    private $conexion;
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }
    
    /**
     * Obtener todos los pedidos
     */
    public function obtenerTodos($filtros = []) {
        $sql = "SELECT c.id, c.id_transaccion, c.fecha, c.status, c.email, c.id_cliente, 
                        c.total, c.medio_pago, cl.nombre, cl.apellido 
                FROM compra c 
                LEFT JOIN clientes cl ON c.id_cliente = cl.id 
                WHERE 1=1 ";
        
        $parametros = [];
        
        if (isset($filtros['estado']) && !empty($filtros['estado'])) {
            $sql .= "AND c.status = ? ";
            $parametros[] = $filtros['estado'];
        }
        
        if (isset($filtros['cliente']) && !empty($filtros['cliente'])) {
            $sql .= "AND (cl.nombre LIKE ? OR cl.apellido LIKE ? OR c.email LIKE ?) ";
            $likes = '%' . $filtros['cliente'] . '%';
            $parametros[] = $likes;
            $parametros[] = $likes;
            $parametros[] = $likes;
        }
        
        if (isset($filtros['fecha']) && !empty($filtros['fecha'])) {
            $sql .= "AND DATE(c.fecha) = ? ";
            $parametros[] = $filtros['fecha'];
        }
        
        $sql .= "ORDER BY c.fecha DESC";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener un pedido por ID
     */
    public function obtenerPorId($id) {
        $sql = "SELECT c.id, c.id_transaccion, c.fecha, c.status, c.email, c.id_cliente, 
                        c.total, c.medio_pago, cl.nombre, cl.apellido, cl.telefono, cl.documento
                FROM compra c 
                LEFT JOIN clientes cl ON c.id_cliente = cl.id 
                WHERE c.id = ?";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener detalles de un pedido
     */
    public function obtenerDetalles($id_compra) {
        $sql = "SELECT * FROM detalle_compra WHERE id_compra = ? ORDER BY id DESC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$id_compra]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Cambiar estado del pedido
     */
    public function cambiarEstado($id, $nuevo_estado) {
        $estados_validos = ['pendiente', 'procesando', 'enviado', 'entregado', 'cancelado'];
        
        if (!in_array($nuevo_estado, $estados_validos)) {
            throw new Exception("Estado no válido");
        }
        
        $sql = "UPDATE compra SET status = ? WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        
        if ($stmt->execute([$nuevo_estado, $id])) {
            return true;
        }
        throw new Exception("Error al actualizar el estado");
    }
    
    /**
     * Crear un nuevo pedido
     */
    public function crear($datos) {
        // Validar datos requeridos
        $campos_requeridos = ['id_transaccion', 'email', 'id_cliente', 'total', 'medio_pago', 'status'];
        foreach ($campos_requeridos as $campo) {
            if (!isset($datos[$campo]) || empty($datos[$campo])) {
                throw new Exception("Campo requerido faltante: $campo");
            }
        }
        
        $sql = "INSERT INTO compra (id_transaccion, fecha, status, email, id_cliente, total, medio_pago) 
                VALUES (?, NOW(), ?, ?, ?, ?, ?)";
        
        $stmt = $this->conexion->prepare($sql);
        
        if ($stmt->execute([
            $datos['id_transaccion'],
            $datos['status'],
            $datos['email'],
            $datos['id_cliente'],
            $datos['total'],
            $datos['medio_pago']
        ])) {
            return $this->conexion->lastInsertId();
        }
        throw new Exception("Error al crear el pedido");
    }
    
    /**
     * Agregar detalle al pedido
     */
    public function agregarDetalle($id_compra, $datos) {
        $campos_requeridos = ['id_producto', 'titulo', 'precio', 'cantidad'];
        foreach ($campos_requeridos as $campo) {
            if (!isset($datos[$campo])) {
                throw new Exception("Campo requerido faltante: $campo");
            }
        }
        
        $sql = "INSERT INTO detalle_compra (id_compra, id_producto, titulo, precio, cantidad) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->conexion->prepare($sql);
        
        if ($stmt->execute([
            $id_compra,
            $datos['id_producto'],
            $datos['titulo'],
            $datos['precio'],
            $datos['cantidad']
        ])) {
            return true;
        }
        throw new Exception("Error al agregar detalle al pedido");
    }
    
    /**
     * Eliminar un pedido
     */
    public function eliminar($id) {
        try {
            $this->conexion->beginTransaction();
            
            // Eliminar detalles de compra
            $sql_detalles = "DELETE FROM detalle_compra WHERE id_compra = ?";
            $stmt_detalles = $this->conexion->prepare($sql_detalles);
            $stmt_detalles->execute([$id]);
            
            // Eliminar compra
            $sql_compra = "DELETE FROM compra WHERE id = ?";
            $stmt_compra = $this->conexion->prepare($sql_compra);
            $stmt_compra->execute([$id]);
            
            $this->conexion->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conexion->rollBack();
            throw new Exception("Error al eliminar el pedido: " . $e->getMessage());
        }
    }
    
    /**
     * Obtener estadísticas de pedidos
     */
    public function obtenerEstadisticas() {
        $stats = [];
        
        // Total de pedidos
        $sql = "SELECT COUNT(*) as total FROM compra";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Pedidos por estado
        $sql = "SELECT status, COUNT(*) as cantidad FROM compra GROUP BY status";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        $stats['por_estado'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Total de ventas
        $sql = "SELECT SUM(total) as total_ventas FROM compra WHERE status != 'cancelado'";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        $stats['total_ventas'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_ventas'];
        
        // Promedio de venta
        $sql = "SELECT AVG(total) as promedio FROM compra WHERE status != 'cancelado'";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        $stats['promedio_venta'] = $stmt->fetch(PDO::FETCH_ASSOC)['promedio_venta'];
        
        return $stats;
    }
    
    /**
     * Obtener pedidos por rango de fechas
     */
    public function obtenerPorFechas($fecha_inicio, $fecha_fin) {
        $sql = "SELECT c.id, c.id_transaccion, c.fecha, c.status, c.email, c.id_cliente, 
                        c.total, c.medio_pago, cl.nombre, cl.apellido 
                FROM compra c 
                LEFT JOIN clientes cl ON c.id_cliente = cl.id 
                WHERE c.fecha BETWEEN ? AND ? 
                ORDER BY c.fecha DESC";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$fecha_inicio, $fecha_fin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener pedidos de un cliente específico
     */
    public function obtenerPorCliente($id_cliente) {
        $sql = "SELECT c.id, c.id_transaccion, c.fecha, c.status, c.email, c.total, c.medio_pago 
                FROM compra c 
                WHERE c.id_cliente = ? 
                ORDER BY c.fecha DESC";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$id_cliente]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar pedidos por transacción
     */
    public function buscarPorTransaccion($id_transaccion) {
        $sql = "SELECT c.id, c.id_transaccion, c.fecha, c.status, c.email, c.id_cliente, 
                        c.total, c.medio_pago, cl.nombre, cl.apellido 
                FROM compra c 
                LEFT JOIN clientes cl ON c.id_cliente = cl.id 
                WHERE c.id_transaccion = ? 
                LIMIT 1";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$id_transaccion]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// Ejemplo de uso (comentado):
/*
try {
    $pedidos = new PedidosCRUD($conexion);
    
    // Obtener todos los pedidos
    $todos = $pedidos->obtenerTodos();
    
    // Obtener con filtros
    $filtrados = $pedidos->obtenerTodos(['estado' => 'pendiente']);
    
    // Obtener un pedido específico
    $pedido = $pedidos->obtenerPorId(1);
    
    // Cambiar estado
    $pedidos->cambiarEstado(1, 'enviado');
    
    // Obtener estadísticas
    $stats = $pedidos->obtenerEstadisticas();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
*/
?>
