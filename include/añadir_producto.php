<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Datos del formulario
    $titulo       = $_POST['titulo'];
    $descripcion  = $_POST['descripcion'];
    $precio       = floatval($_POST['precio']);
    $descuento    = isset($_POST['descuento']) ? intval($_POST['descuento']) : 0;
    $id_categoria = intval($_POST['id_categoria']);
    $activo       = $_POST['activo'];

    $db = new Database();
    $pdo = $db->getConexion();

    try {
        $pdo->beginTransaction();

        // Procesar imagen principal
        $imagenPrincipalNombre = 'noadd.jpg'; // Valor por defecto
        $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $tamañoMaximo = 2 * 1024 * 1024; // 2MB
        $rutaBase = "../assets/img/";

        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
            $tipo = $_FILES['imagen']['type'];
            $tamano = $_FILES['imagen']['size'];

            if (in_array($tipo, $tiposPermitidos) && $tamano <= $tamañoMaximo) {
                $nombreOriginal = $_FILES['imagen']['name'];
                $nombreUnico = uniqid('main_') . '-' . basename($nombreOriginal);
                $rutaFinal = $rutaBase . $nombreUnico;

                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaFinal)) {
                    $imagenPrincipalNombre = $nombreUnico;
                }
            }
        }

        // Insertar producto con imagen principal y descuento
        $sql = "INSERT INTO productos (titulo, descripcion, precio, descuento, id_categoria, activo, imagen) 
                VALUES (:titulo, :descripcion, :precio, :descuento, :id_categoria, :activo, :imagen)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':titulo'       => $titulo,
            ':descripcion'  => $descripcion,
            ':precio'       => $precio,
            ':descuento'    => $descuento,
            ':id_categoria' => $id_categoria,
            ':activo'       => $activo,
            ':imagen'       => $imagenPrincipalNombre
        ]);

        $producto_id = $pdo->lastInsertId(); // ID del nuevo producto

        // Insertar imágenes adicionales
        if (!empty($_FILES['imagenes']['name'][0])) {
            foreach ($_FILES['imagenes']['tmp_name'] as $key => $tmpName) {
                $nombreOriginal = $_FILES['imagenes']['name'][$key];
                $tipo           = $_FILES['imagenes']['type'][$key];
                $tamano         = $_FILES['imagenes']['size'][$key];

                if (!in_array($tipo, $tiposPermitidos)) continue;
                if ($tamano > $tamañoMaximo) continue;

                $nombreUnico = uniqid('ropa_') . '-' . basename($nombreOriginal);
                $rutaFinal   = $rutaBase . $nombreUnico;

                if (move_uploaded_file($tmpName, $rutaFinal)) {
                    $sqlImg = "INSERT INTO producto_imagenes (producto_id, imagen) VALUES (:producto_id, :imagen)";
                    $stmtImg = $pdo->prepare($sqlImg);
                    $stmtImg->execute([
                        ':producto_id' => $producto_id,
                        ':imagen'      => $nombreUnico
                    ]);
                }
            }
        }

        $pdo->commit();
        header("Location: ../public/index.php");
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        die("❌ Error al guardar el producto: " . $e->getMessage());
    }
}
?>
