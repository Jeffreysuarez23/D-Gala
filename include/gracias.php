<?php
require_once 'db.php';
require_once 'configuraciones.php';

$db = new Database();
$conexion = $db->getConexion();
$id_transaccion = isset($_GET['key']) ? $_GET['key'] : '0';

$error = '';
$fecha = '';
$email = '';
$total = 0;
$sqlDet = null;

if ($id_transaccion == '0') {
    $error = 'Error al procesar la compra, intente nuevamente.';
} else {
    $stmt = $conexion->prepare("SELECT COUNT(id) FROM compra WHERE id_transaccion = ? AND status = ?");
    $stmt->execute([$id_transaccion, 'COMPLETED']);
    if ($stmt->fetchColumn() > 0) {
        $stmt = $conexion->prepare("SELECT id, fecha, email, total FROM compra WHERE id_transaccion = ? AND status = ? LIMIT 1");
        $stmt->execute([$id_transaccion, 'COMPLETED']);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $idCompra = $row['id'];
            $fecha = $row['fecha'];
            $email = $row['email'];
            $total = $row['total'];

            $sqlDet = $conexion->prepare("SELECT titulo, precio, cantidad FROM detalle_compra WHERE id_compra = ?");
            $sqlDet->execute([$idCompra]);
        } else {
            $error = 'No se encontraron los datos de la compra.';
        }
    } else {
        $error = 'Error al procesar la compra, intente nuevamente.';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gracias por tu compra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .thank-you-container {
            padding-top: 60px;
            padding-bottom: 60px;
        }
    </style>
</head>
<body>
    <?php include '../include/header.php'; ?>

    <div class="container thank-you-container">
        <?php if (strlen($error) > 0) { ?>
            <div class="alert alert-danger text-center">
                <h4 class="mb-3"><?php echo $error; ?></h4>
                <a href="../public/index.php" class="btn btn-outline-primary">Volver al inicio</a>
            </div>
        <?php } else { ?>
            <div class="text-center mb-5">
                <h1 class="display-5 text-success">¡Gracias por tu compra!</h1>
                <p class="lead">Tu pago ha sido procesado correctamente.</p>
            </div>

            <!-- Tarjeta con datos de la compra -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Resumen de la Compra</h5>
                </div>
                <div class="card-body">
                    <p><strong>Folio de la compra:</strong> <?php echo $id_transaccion; ?></p>
                    <p><strong>Fecha de compra:</strong> <?php echo $fecha; ?></p>
                    <p><strong>Email:</strong> <?php echo $email; ?></p>
                    <p><strong>Total:</strong> <?php echo MONEDA . number_format($total, 2); ?></p>
                </div>
            </div>

            <!-- Tabla de productos -->
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Detalles de Productos</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Cantidad</th>
                                <th scope="col">Producto</th>
                                <th scope="col">Importe</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row_det = $sqlDet->fetch(PDO::FETCH_ASSOC)) {
                                $importe = $row_det['precio'] * $row_det['cantidad'];
                            ?>
                                <tr>
                                    <td><?php echo $row_det['cantidad']; ?></td>
                                    <td><?php echo $row_det['titulo'];?></td>
                                    <td><?php echo MONEDA . number_format($importe, 2); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="text-center mt-5">
                <a href="../public/index.php" class="btn btn-success btn-lg">Volver al inicio</a>
            </div>
        <?php } ?>
    </div>

    <?php include '../include/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
