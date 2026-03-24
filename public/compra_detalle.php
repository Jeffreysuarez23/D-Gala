<?php
require_once '../include/db.php';
require_once '../include/configuraciones.php';
require_once '../include/cliente.php';

$db = new Database();
$conexion = $db->getConexion();

$token = $_SESSION['token'];
$orden = $_GET['orden'] ?? null;
$token = $_GET['token'] ?? null;

if ($orden == null || $token == null || $token != $_SESSION['token']) {
    echo '<script>alert("Solicitud Cancelada"); window.location.href = "compras.php";</script>';
    exit;
}
$sqlCompra = $conexion->prepare("SELECT id, id_transaccion, fecha, total FROM compra WHERE id_transaccion = ? LIMIT 1");
$sqlCompra->execute([$orden]);
$rowCompra = $sqlCompra->fetch(PDO::FETCH_ASSOC);
$idCompra = $rowCompra['id'];

$fecha = new DateTime($rowCompra['fecha']);
$fecha = $fecha->format('d/m/Y H:i');

$sqlDetalle = $conexion->prepare("SELECT id ,titulo,precio,cantidad FROM detalle_compra WHERE id_compra = ?");
$sqlDetalle->execute([$idCompra]);


?>

<!DOCTYPE html>
<html lang="es" data-bs-theme="auto">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Productos</title>
    <?php include '../include/links.php'; ?>

    <meta name="theme-color" content="#60606dff">
</head>

<!DOCTYPE html>
<html lang="es" data-bs-theme="auto">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detalle de Compra - Tienda Online</title>
    <?php include '../include/links.php'; ?>
    <meta name="theme-color" content="#000000">
    <style>
        main { min-height: calc(100vh - 300px); }

        .breadcrumb-nav {
            margin-bottom: 2rem;
        }

        .breadcrumb-nav a {
            color: var(--primary-dark);
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .breadcrumb-nav a:hover {
            text-decoration: underline;
        }

        .header-section {
            margin-bottom: 2rem;
        }

        .header-section h1 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-dark);
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .detail-card {
            background-color: var(--secondary-light);
            padding: 2rem;
            border-radius: 8px;
            border: 1px solid var(--border-light);
        }

        .detail-card h3 {
            color: var(--primary-dark);
            font-weight: 700;
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid var(--primary-dark);
            padding-bottom: 0.75rem;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid var(--border-light);
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: var(--primary-dark);
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .detail-value {
            color: var(--accent-gray);
            font-size: 1rem;
            text-align: right;
            font-family: monospace;
        }

        .detail-value.amount {
            font-weight: 600;
            color: var(--primary-dark);
            font-family: inherit;
        }

        .products-section {
            margin-bottom: 2rem;
        }

        .products-section h3 {
            color: var(--primary-dark);
            font-weight: 700;
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid var(--primary-dark);
            padding-bottom: 0.75rem;
        }

        .product-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            background-color: var(--primary-light);
            border: 1px solid var(--border-light);
            border-radius: 8px;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .product-item:hover {
            box-shadow: var(--shadow-soft);
            transform: translateY(-2px);
        }

        .product-info {
            flex: 1;
        }

        .product-name {
            font-weight: 600;
            color: var(--primary-dark);
            margin-bottom: 0.5rem;
        }

        .product-specs {
            color: var(--accent-gray);
            font-size: 0.9rem;
            display: flex;
            gap: 1.5rem;
        }

        .product-pricing {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.5rem;
            min-width: 150px;
        }

        .product-unit-price {
            color: var(--accent-gray);
            font-size: 0.85rem;
        }

        .product-subtotal {
            font-weight: 700;
            color: var(--primary-dark);
            font-size: 1.1rem;
        }

        .summary-card {
            background-color: var(--secondary-light);
            padding: 2rem;
            border-radius: 8px;
            border-left: 4px solid var(--primary-dark);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid var(--border-light);
        }

        .summary-row:last-child {
            border-bottom: none;
        }

        .summary-row.total {
            border-top: 2px solid var(--primary-dark);
            padding-top: 1rem;
            font-weight: 700;
            font-size: 1.3rem;
        }

        .summary-label {
            color: var(--accent-gray);
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .summary-value {
            color: var(--primary-dark);
            font-weight: 600;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 2rem;
        }

        .btn-action {
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.9rem;
            border: 2px solid;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-back {
            background-color: transparent;
            color: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .btn-back:hover {
            background-color: var(--secondary-light);
            transform: translateY(-2px);
        }

        .btn-pdf {
            background-color: var(--primary-dark);
            color: var(--primary-light);
            border-color: var(--primary-dark);
        }

        .btn-pdf:hover {
            background-color: var(--secondary-dark);
            border-color: var(--secondary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        @media (max-width: 768px) {
            .detail-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php include '../include/header.php'; ?>

    <main class="section-padding">
        <div class="container">
            <!-- Breadcrumb -->
            <div class="breadcrumb-nav">
                <a href="index.php"><i class="bi bi-house"></i> Inicio</a>
                <span style="color: var(--accent-gray); margin: 0 0.5rem;">/</span>
                <a href="compras.php"><i class="bi bi-bag-check"></i> Mis Compras</a>
                <span style="color: var(--accent-gray); margin: 0 0.5rem;">/</span>
                <span style="color: var(--primary-dark); font-weight: 700;">Detalle</span>
            </div>

            <!-- Header -->
            <div class="header-section">
                <h1><i class="bi bi-receipt"></i> Detalle de la Compra</h1>
                <p style="color: var(--accent-gray); margin-top: 0.5rem;">Información completa de tu pedido</p>
            </div>

            <!-- Detail Grid -->
            <div class="detail-grid">
                <!-- Purchase Info -->
                <div class="detail-card">
                    <h3><i class="bi bi-info-circle"></i> Información del Pedido</h3>
                    <div class="detail-row">
                        <span class="detail-label">Orden #</span>
                        <span class="detail-value"><?php echo str_pad($rowCompra['id'], 5, '0', STR_PAD_LEFT); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Transacción</span>
                        <span class="detail-value"><?php echo htmlspecialchars($rowCompra['id_transaccion']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Fecha</span>
                        <span class="detail-value"><?php echo $fecha; ?></span>
                    </div>
                </div>

                <!-- Total Summary -->
                <div class="detail-card">
                    <h3><i class="bi bi-currency-dollar"></i> Resumen</h3>
                    <div class="detail-row">
                        <span class="detail-label">Subtotal</span>
                        <span class="detail-value amount"><?php echo MONEDA . number_format($rowCompra['total'], 2); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Envío</span>
                        <span class="detail-value amount">Gratis</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Impuestos</span>
                        <span class="detail-value amount"><?php echo MONEDA . '0.00'; ?></span>
                    </div>
                    <div class="detail-row" style="border-bottom: none; padding-top: 1rem; border-top: 2px solid var(--border-light); margin-top: 1rem;">
                        <span class="detail-label" style="font-size: 1rem;">Total</span>
                        <span class="detail-value amount" style="font-size: 1.3rem;"><?php echo MONEDA . number_format($rowCompra['total'], 2); ?></span>
                    </div>
                </div>
            </div>

            <!-- Products Section -->
            <div class="products-section">
                <h3><i class="bi bi-box"></i> Productos en tu Pedido</h3>
                
                <?php 
                    $total_items = 0;
                    $sqlDetalle->execute([$idCompra]);
                    while ($row = $sqlDetalle->fetch(PDO::FETCH_ASSOC)) { 
                        $subtotal = $row['precio'] * $row['cantidad'];
                        $total_items += $row['cantidad'];

                        // Extraer información de variantes del título si existe
                        $titulo = $row['titulo'];
                        $variantes_info = '';
                        
                        // Buscar información entre paréntesis (formato: "Nombre (Talla: X, Color: Y)")
                        if (preg_match('/\((.*?)\)/', $titulo, $matches)) {
                            $variantes_str = $matches[1];
                            $titulo = trim(str_replace('(' . $matches[1] . ')', '', $titulo));
                            $variantes_info = '<div style="color: var(--accent-gray); font-size: 0.85rem; margin-top: 0.5rem;">' . htmlspecialchars($variantes_str) . '</div>';
                        }
                ?>
                    <div class="product-item">
                        <div class="product-info">
                            <div class="product-name"><?php echo htmlspecialchars($titulo); ?></div>
                            <?php echo $variantes_info; ?>
                            <div class="product-specs">
                                <span>Cantidad: <strong><?php echo $row['cantidad']; ?></strong></span>
                                <span>Precio: <strong><?php echo MONEDA . number_format($row['precio'], 2); ?></strong> c/u</span>
                            </div>
                        </div>
                        <div class="product-pricing">
                            <div class="product-subtotal"><?php echo MONEDA . number_format($subtotal, 2); ?></div>
                        </div>
                    </div>
                <?php } ?>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="compras.php" class="btn-action btn-back">
                    <i class="bi bi-arrow-left"></i> Volver a Compras
                </a>
                <a href="../include/generar_pdf.php?id_compra=<?php echo $rowCompra['id']; ?>" class="btn-action btn-pdf" target="_blank">
                    <i class="bi bi-file-pdf"></i> Descargar PDF
                </a>
                <a href="index.php" class="btn-action btn-back">
                    <i class="bi bi-shop"></i> Seguir Comprando
                </a>
            </div>
        </div>
    </main>

    <?php include '../include/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>