<?php
require_once 'db.php';
require_once 'configuraciones.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $datos = [];

    if ($action == 'agregar') {
        $cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 0;
        $subtotal = agregar($id, $cantidad);

        if ($subtotal > 0) {
            $datos['ok'] = true;
            $datos['sub'] = MONEDA . number_format($subtotal, 2);
            // número de productos distintos
            $datos['numero'] = count($_SESSION['carrito']['productos']);
        } else {
            $datos['ok'] = false;
        }
    } elseif ($action == 'eliminar') {
        $datos['ok'] = eliminar($id);
        $datos['numero'] = count($_SESSION['carrito']['productos']);
    } else {
        $datos['ok'] = false;
    }

    echo json_encode($datos);
}

function agregar($id, $cantidad) {
    $res = 0;
    if ($id > 0 && $cantidad > 0 && is_numeric($cantidad)) {
        if (isset($_SESSION['carrito']['productos'][$id])) {
            // Reemplazamos la cantidad
            $_SESSION['carrito']['productos'][$id] = $cantidad;

            $db = new Database();
            $conexion = $db->getConexion();

            $stmt = $conexion->prepare("SELECT precio, descuento FROM productos WHERE id = ? AND activo = 1 LIMIT 1");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $precio = $row['precio'];
                $descuento = $row['descuento'];
                $precio_desc = $precio - (($precio * $descuento) / 100);
                $res = $precio_desc * $cantidad;
            }
        }
    }
    return $res;
}

function eliminar($id) {
    if ($id > 0) {
        if (isset($_SESSION['carrito']['productos'][$id])) {
            unset($_SESSION['carrito']['productos'][$id]);
            return true;
        }
    }
    return false;
}
