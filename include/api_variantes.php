<?php
require_once 'db.php';
require_once 'configuraciones.php';

header('Content-Type: application/json; charset=utf-8');

$db = new Database();
$pdo = $db->getConexion();

$action = isset($_GET['action']) ? $_GET['action'] : '';
$response = ['success' => false];

try {
    // 1. Obtener todas las variantes de un producto
    if ($action === 'getVariantes') {
        $id_producto = isset($_GET['id_producto']) ? (int)$_GET['id_producto'] : 0;
        
        if ($id_producto <= 0) {
            throw new Exception('ID de producto inválido');
        }

        // Obtener variantes con información de talla y color
        $stmt = $pdo->prepare("
            SELECT 
                pv.id as variante_id,
                pv.id_talla,
                pv.id_color,
                pv.precio,
                pv.stock,
                t.nombre as talla_nombre,
                c.nombre as color_nombre,
                c.codigo_hex
            FROM productos_variantes pv
            LEFT JOIN c_tallas t ON pv.id_talla = t.id
            LEFT JOIN c_colores c ON pv.id_color = c.id
            WHERE pv.id_producto = ?
            ORDER BY t.nombre ASC, c.nombre ASC
        ");
        $stmt->execute([$id_producto]);
        $variantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener tallas únicas disponibles
        $stmt = $pdo->prepare("
            SELECT DISTINCT t.id, t.nombre
            FROM productos_variantes pv
            LEFT JOIN c_tallas t ON pv.id_talla = t.id
            WHERE pv.id_producto = ? AND t.id IS NOT NULL
            ORDER BY t.nombre ASC
        ");
        $stmt->execute([$id_producto]);
        $tallas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener colores únicos disponibles
        $stmt = $pdo->prepare("
            SELECT DISTINCT c.id, c.nombre, c.codigo_hex
            FROM productos_variantes pv
            LEFT JOIN c_colores c ON pv.id_color = c.id
            WHERE pv.id_producto = ? AND c.id IS NOT NULL
            ORDER BY c.nombre ASC
        ");
        $stmt->execute([$id_producto]);
        $colores = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response['success'] = true;
        $response['variantes'] = $variantes;
        $response['tallas'] = $tallas;
        $response['colores'] = $colores;
    }

    // 2. Obtener variantes filtradas por talla y/o color
    else if ($action === 'filtrarVariantes') {
        $id_producto = isset($_GET['id_producto']) ? (int)$_GET['id_producto'] : 0;
        $id_talla = isset($_GET['id_talla']) ? (int)$_GET['id_talla'] : null;
        $id_color = isset($_GET['id_color']) ? (int)$_GET['id_color'] : null;

        if ($id_producto <= 0) {
            throw new Exception('ID de producto inválido');
        }

        $sql = "
            SELECT 
                pv.id as variante_id,
                pv.id_talla,
                pv.id_color,
                pv.precio,
                pv.stock,
                t.nombre as talla_nombre,
                c.nombre as color_nombre,
                c.codigo_hex
            FROM productos_variantes pv
            LEFT JOIN c_tallas t ON pv.id_talla = t.id
            LEFT JOIN c_colores c ON pv.id_color = c.id
            WHERE pv.id_producto = ?
        ";

        $params = [$id_producto];

        if ($id_talla !== null) {
            $sql .= " AND (pv.id_talla = ? OR pv.id_talla IS NULL)";
            $params[] = $id_talla;
        }

        if ($id_color !== null) {
            $sql .= " AND (pv.id_color = ? OR pv.id_color IS NULL)";
            $params[] = $id_color;
        }

        $sql .= " ORDER BY pv.stock DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $variantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener colores disponibles para la talla seleccionada
        if ($id_talla !== null) {
            $stmt = $pdo->prepare("
                SELECT DISTINCT c.id, c.nombre, c.codigo_hex
                FROM productos_variantes pv
                LEFT JOIN c_colores c ON pv.id_color = c.id
                WHERE pv.id_producto = ? AND pv.id_talla = ? AND c.id IS NOT NULL
                ORDER BY c.nombre ASC
            ");
            $stmt->execute([$id_producto, $id_talla]);
            $colores_disponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response['colores_disponibles'] = $colores_disponibles;
        }

        // Obtener tallas disponibles para el color seleccionado
        if ($id_color !== null) {
            $stmt = $pdo->prepare("
                SELECT DISTINCT t.id, t.nombre
                FROM productos_variantes pv
                LEFT JOIN c_tallas t ON pv.id_talla = t.id
                WHERE pv.id_producto = ? AND pv.id_color = ? AND t.id IS NOT NULL
                ORDER BY t.nombre ASC
            ");
            $stmt->execute([$id_producto, $id_color]);
            $tallas_disponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response['tallas_disponibles'] = $tallas_disponibles;
        }

        $response['success'] = true;
        $response['variantes'] = $variantes;
    }

    // 3. Obtener una variante específica
    else if ($action === 'getVariante') {
        $variante_id = isset($_GET['variante_id']) ? (int)$_GET['variante_id'] : 0;

        if ($variante_id <= 0) {
            throw new Exception('ID de variante inválido');
        }

        $stmt = $pdo->prepare("
            SELECT 
                pv.id as variante_id,
                pv.id_producto,
                pv.id_talla,
                pv.id_color,
                pv.precio,
                pv.stock,
                t.nombre as talla_nombre,
                c.nombre as color_nombre,
                c.codigo_hex
            FROM productos_variantes pv
            LEFT JOIN c_tallas t ON pv.id_talla = t.id
            LEFT JOIN c_colores c ON pv.id_color = c.id
            WHERE pv.id = ?
        ");
        $stmt->execute([$variante_id]);
        $variante = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$variante) {
            throw new Exception('Variante no encontrada');
        }

        $response['success'] = true;
        $response['variante'] = $variante;
    }

} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
