<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_producto = intval($_POST['id_producto']);
    $caracteristicas = $_POST['caracteristicas']; // Array de características

    // Conexión a la base de datos
    $db = new Database();
    $pdo = $db->getConexion();

    try {
        $pdo->beginTransaction();

        // Insertar las características y sus valores
        foreach ($caracteristicas as $caracteristica) {
            $id_caracteristica = intval($caracteristica['id_caracteristica']);
            $valores = $caracteristica['valores']; // Valores y stock de la característica

            foreach ($valores as $valorStock) {
                $valor = $valorStock['valor'];
                $stock = intval($valorStock['stock']);

                // Insertar en la tabla `caracter_producto`
                $sql = "INSERT INTO caracter_producto (id_producto, id_caracteristica, valor, stock) 
                        VALUES (:id_producto, :id_caracteristica, :valor, :stock)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':id_producto' => $id_producto,
                    ':id_caracteristica' => $id_caracteristica,
                    ':valor' => $valor,
                    ':stock' => $stock
                ]);
            }
        }

        $pdo->commit();

        // Mensaje de éxito y redirección
        echo "<script>
                alert('✔️ Características agregadas correctamente.');
                window.location.href = '../public/crud_caracteristicas_p.php'; // Redirigir a la página deseada
              </script>";
    } catch (PDOException $e) {
        $pdo->rollBack();

        // Mensaje de error
        echo "<script>
                alert('❌ Error al agregar las características: " . addslashes($e->getMessage()) . "');
                window.location.href = '../public/crud_caracteristicas_p.php'; // Redirigir en caso de error
              </script>";
    }
}
?>
