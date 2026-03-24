<?php
require_once 'db.php';
require_once 'configuraciones.php';

header('Content-Type: application/json; charset=utf-8');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$db = new Database();
$pdo = $db->getConexion();

$action = isset($_POST['action']) ? $_POST['action'] : '';
$response = ['ok' => false];

try {
    // Crear carrito si no existe
    if (!isset($_SESSION['carrito'])) {
        $_SESSION['carrito'] = ['variantes' => [], 'productos' => []];
    }
    if (!isset($_SESSION['carrito']['variantes'])) {
        $_SESSION['carrito']['variantes'] = [];
    }

    // 1. Agregar variante al carrito
    if ($action === 'agregarVariante') {
        $variante_id = isset($_POST['variante_id']) ? (int)$_POST['variante_id'] : 0;
        $cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;
        $id_producto = isset($_POST['id_producto']) ? (int)$_POST['id_producto'] : 0;

        if ($variante_id <= 0 || $cantidad <= 0 || $id_producto <= 0) {
            throw new Exception('Datos inválidos');
        }

        // Validar que la variante existe y obtener datos
        $stmt = $pdo->prepare("
            SELECT pv.stock, pv.precio, t.nombre as talla, c.nombre as color
            FROM productos_variantes pv
            LEFT JOIN c_tallas t ON pv.id_talla = t.id
            LEFT JOIN c_colores c ON pv.id_color = c.id
            WHERE pv.id = ? AND pv.id_producto = ?
        ");
        $stmt->execute([$variante_id, $id_producto]);
        $variante = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$variante) {
            throw new Exception('Variante no encontrada');
        }

        if ($variante['stock'] < $cantidad) {
            throw new Exception('Stock insuficiente');
        }

        // Crear clave única para la variante
        $clave_variante = "variante_" . $variante_id;

        // Si la variante ya existe en el carrito, incrementar cantidad
        if (isset($_SESSION['carrito']['variantes'][$clave_variante])) {
            $_SESSION['carrito']['variantes'][$clave_variante]['cantidad'] += $cantidad;
        } else {
            $_SESSION['carrito']['variantes'][$clave_variante] = [
                'variante_id' => $variante_id,
                'id_producto' => $id_producto,
                'cantidad' => $cantidad,
                'precio' => $variante['precio'],
                'talla' => $variante['talla'],
                'color' => $variante['color']
            ];
        }

        $num_items = count($_SESSION['carrito']['variantes']) + count($_SESSION['carrito']['productos']);
        
        $response['ok'] = true;
        $response['numero'] = $num_items;
        $response['message'] = $cantidad . ' variante(s) agregada(s) al carrito';
    }

    // 2. Actualizar cantidad de variante
    else if ($action === 'actualizar' || $action === 'actualizarCantidad') {
        $variante_id = isset($_POST['variante_id']) ? (int)$_POST['variante_id'] : 0;
        $cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 0;

        if ($variante_id <= 0) {
            throw new Exception('ID de variante inválido');
        }

        $clave_variante = "variante_" . $variante_id;

        if (!isset($_SESSION['carrito']['variantes'][$clave_variante])) {
            throw new Exception('Variante no encontrada en carrito');
        }

        if ($cantidad <= 0) {
            unset($_SESSION['carrito']['variantes'][$clave_variante]);
        } else {
            $_SESSION['carrito']['variantes'][$clave_variante]['cantidad'] = $cantidad;
        }

        $num_items = count($_SESSION['carrito']['variantes']) + count($_SESSION['carrito']['productos']);

        $response['ok'] = true;
        $response['numero'] = $num_items;
        $response['message'] = 'Cantidad actualizada';
    }

    // 3. Eliminar variante del carrito
    else if ($action === 'eliminar' || $action === 'eliminarVariante') {
        $variante_id = isset($_POST['variante_id']) ? (int)$_POST['variante_id'] : 0;

        if ($variante_id <= 0) {
            throw new Exception('ID de variante inválido');
        }

        $clave_variante = "variante_" . $variante_id;

        if (isset($_SESSION['carrito']['variantes'][$clave_variante])) {
            unset($_SESSION['carrito']['variantes'][$clave_variante]);
        }

        $num_items = count($_SESSION['carrito']['variantes']) + count($_SESSION['carrito']['productos']);

        $response['ok'] = true;
        $response['numero'] = $num_items;
        $response['message'] = 'Producto eliminado del carrito';
    }

    // 4. Obtener carrito
    else if ($action === 'obtenerCarrito') {
        $carrito = $_SESSION['carrito']['variantes'];
        $total_items = array_sum(array_column($carrito, 'cantidad'));
        $total_precio = 0;

        foreach ($carrito as $item) {
            $total_precio += $item['precio'] * $item['cantidad'];
        }

        $response['ok'] = true;
        $response['carrito'] = $carrito;
        $response['numero'] = count($carrito);
        $response['total_items'] = $total_items;
        $response['total_precio'] = $total_precio;
    }

    // 5. Vaciar carrito
    else if ($action === 'vaciarCarrito') {
        $_SESSION['carrito']['variantes'] = [];
        $response['ok'] = true;
        $response['numero'] = count($_SESSION['carrito']['productos']);
        $response['message'] = 'Carrito vaciado';
    }

    else {
        throw new Exception('Acción no válida: ' . $action);
    }

} catch (Exception $e) {
    $response['ok'] = false;
    $response['error'] = $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
