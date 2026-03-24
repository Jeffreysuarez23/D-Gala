<?php
require_once 'db.php';
require_once 'configuraciones.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$datos = ['ok' => false];

if (isset($_POST['id'], $_POST['token'])) {
    $id = $_POST['id'];
    $cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;
    $token = $_POST['token'];

    // Validar token
    $token_tmp = hash_hmac('sha1', $id, KEY_TOKEN);

    if ($token === $token_tmp && $cantidad > 0 && is_numeric($cantidad)) {
        // Crear carrito si no existe (compatibilidad con sistema anterior y variantes)
        if (!isset($_SESSION['carrito'])) {
            $_SESSION['carrito'] = ['productos' => [], 'variantes' => []];
        } else {
            // Asegurar que las claves existen (compatibilidad)
            if (!isset($_SESSION['carrito']['variantes'])) {
                $_SESSION['carrito']['variantes'] = [];
            }
        }

        // Si el producto ya existe, sumamos la cantidad
        if (isset($_SESSION['carrito']['productos'][$id])) {
            $_SESSION['carrito']['productos'][$id] += $cantidad;
        } else {
            $_SESSION['carrito']['productos'][$id] = $cantidad;
        }

        // Número de elementos (productos + variantes)
        $productos_distintos = count($_SESSION['carrito']['productos']) + count($_SESSION['carrito']['variantes']);

        // Número total de unidades
        $unidades_totales = array_sum($_SESSION['carrito']['productos']) + 
                           array_sum(array_column($_SESSION['carrito']['variantes'] ?? [], 'cantidad'));

        $datos['ok'] = true;
        $datos['numero'] = $productos_distintos;
        $datos['unidades'] = $unidades_totales;
    }
}

// Limpiar cualquier salida previa
ob_clean();
echo json_encode($datos);
exit;

