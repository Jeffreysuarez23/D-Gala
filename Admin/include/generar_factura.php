<?php
/**
 * Genera factura tipo ticket para impresora térmica (80mm)
 * Optimizado para navegador web - convertible a PDF
 */

require_once("db.php");

if (!isset($_GET['id'])) {
    die('ID de pedido no especificado');
}

$id_pedido = intval($_GET['id']);
$db = new Database();
$conexion = $db->getConexion();

// Obtener información del pedido
$sql_pedido = "SELECT c.id, c.id_transaccion, c.fecha, c.status, c.email, c.id_cliente, 
                      c.total, c.medio_pago, cl.nombre, cl.apellido, cl.telefono, cl.documento
               FROM compra c 
               LEFT JOIN clientes cl ON c.id_cliente = cl.id 
               WHERE c.id = ?";
$stmt_pedido = $conexion->prepare($sql_pedido);
$stmt_pedido->execute([$id_pedido]);
$pedido = $stmt_pedido->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    die('Pedido no encontrado');
}

// Obtener detalles del pedido
$sql_detalles = "SELECT * FROM detalle_compra WHERE id_compra = ?";
$stmt_detalles = $conexion->prepare($sql_detalles);
$stmt_detalles->execute([$id_pedido]);
$detalles = $stmt_detalles->fetchAll(PDO::FETCH_ASSOC);

// Calcular subtotal
$subtotal = 0;
foreach ($detalles as $detalle) {
    $subtotal += $detalle['precio'] * $detalle['cantidad'];
}

// Calcular descuento
$descuento = $subtotal - $pedido['total'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura <?php echo $pedido['id_transaccion']; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            background: #f5f5f5;
            padding: 20px;
        }

        /* Estilos para pantalla */
        .ticket-container {
            width: 320px;
            background: white;
            margin: 0 auto;
            padding: 15px;
            border: 1px solid #ddd;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        /* Estilos para impresión térmica (80mm) */
        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }

            .ticket-container {
                width: 80mm;
                margin: 0;
                padding: 5mm;
                box-shadow: none;
                border: none;
                page-break-after: always;
            }

            @page {
                size: 80mm auto;
                margin: 0;
                padding: 0;
            }

            .no-print {
                display: none;
            }
        }

        .header {
            text-align: center;
            border-bottom: 2px dashed #333;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .header h1 {
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 2px;
        }

        .header p {
            font-size: 10px;
            color: #666;
            margin-top: 3px;
        }

        .section {
            margin-bottom: 10px;
            border-bottom: 1px dashed #999;
            padding-bottom: 8px;
        }

        .section:last-of-type {
            border-bottom: 2px dashed #333;
        }

        .section-title {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .row {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            line-height: 1.4;
            margin-bottom: 3px;
        }

        .label {
            font-weight: bold;
            min-width: 70px;
        }

        .value {
            text-align: right;
            flex: 1;
        }

        .product-header {
            display: grid;
            grid-template-columns: 2fr 0.8fr 1fr 1fr;
            gap: 5px;
            font-weight: bold;
            font-size: 9px;
            line-height: 1.2;
            margin-bottom: 5px;
            text-align: right;
        }

        .product-header > :first-child {
            text-align: left;
        }

        .product-row {
            display: grid;
            grid-template-columns: 2fr 0.8fr 1fr 1fr;
            gap: 5px;
            font-size: 9px;
            line-height: 1.2;
            margin-bottom: 2px;
            text-align: right;
        }

        .product-row > :first-child {
            text-align: left;
        }

        .products-section {
            margin-bottom: 10px;
        }

        .totals-section {
            background: #f9f9f9;
            padding: 8px;
            border-radius: 3px;
            margin-bottom: 10px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            margin-bottom: 3px;
        }

        .total-row.final {
            border-top: 2px solid #333;
            padding-top: 5px;
            font-weight: bold;
            font-size: 12px;
        }

        .footer {
            text-align: center;
            font-size: 9px;
            color: #555;
            margin-top: 10px;
            line-height: 1.4;
        }

        .footer-line {
            margin-bottom: 3px;
        }

        .print-button {
            text-align: center;
            margin-top: 20px;
        }

        .print-button button {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin: 5px;
        }

        .print-button button:hover {
            background: #45a049;
        }

        .download-button {
            text-align: center;
            margin-top: 0;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="ticket-container">
        <!-- Encabezado -->
        <div class="header">
            <h1>FACTURA</h1>
            <p>TICKET DE COMPRA</p>
        </div>

        <!-- Información del Pedido -->
        <div class="section">
            <div class="row">
                <span class="label">Pedido:</span>
                <span class="value">#<?php echo htmlspecialchars($pedido['id_transaccion']); ?></span>
            </div>
            <div class="row">
                <span class="label">Fecha:</span>
                <span class="value"><?php echo date('d/m/Y H:i', strtotime($pedido['fecha'])); ?></span>
            </div>
            <div class="row">
                <span class="label">Estado:</span>
                <span class="value"><?php echo ucfirst(htmlspecialchars($pedido['status'])); ?></span>
            </div>
        </div>

        <!-- Información del Cliente -->
        <div class="section">
            <div class="section-title">📋 Cliente</div>
            <div class="row">
                <span class="label">Nombre:</span>
                <span class="value"><?php echo htmlspecialchars($pedido['nombre'] . ' ' . $pedido['apellido']); ?></span>
            </div>
            <?php if (!empty($pedido['documento'])): ?>
                <div class="row">
                    <span class="label">Doc:</span>
                    <span class="value"><?php echo htmlspecialchars($pedido['documento']); ?></span>
                </div>
            <?php endif; ?>
            <?php if (!empty($pedido['telefono'])): ?>
                <div class="row">
                    <span class="label">Teléfono:</span>
                    <span class="value"><?php echo htmlspecialchars($pedido['telefono']); ?></span>
                </div>
            <?php endif; ?>
            <?php if (!empty($pedido['email'])): ?>
                <div class="row">
                    <span class="label">Email:</span>
                    <span class="value" style="font-size: 9px;"><?php echo htmlspecialchars($pedido['email']); ?></span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Productos -->
        <div class="products-section">
            <div class="section-title">🛒 Productos</div>
            <div class="product-header">
                <div>Descripción</div>
                <div>Cant.</div>
                <div>Precio</div>
                <div>Total</div>
            </div>
            <?php foreach ($detalles as $detalle): ?>
                <div class="product-row">
                    <div><?php echo htmlspecialchars(substr($detalle['titulo'], 0, 25)); ?></div>
                    <div><?php echo htmlspecialchars($detalle['cantidad']); ?></div>
                    <div>$<?php echo number_format($detalle['precio'], 2, ',', '.'); ?></div>
                    <div>$<?php echo number_format($detalle['precio'] * $detalle['cantidad'], 2, ',', '.'); ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Totales -->
        <div class="totals-section">
            <div class="total-row">
                <span>SUBTOTAL:</span>
                <span>$<?php echo number_format($subtotal, 2, ',', '.'); ?></span>
            </div>
            <?php if ($descuento > 0): ?>
                <div class="total-row">
                    <span>DESCUENTO:</span>
                    <span>-$<?php echo number_format($descuento, 2, ',', '.'); ?></span>
                </div>
            <?php endif; ?>
            <div class="total-row final">
                <span>TOTAL:</span>
                <span>$<?php echo number_format($pedido['total'], 2, ',', '.'); ?></span>
            </div>
        </div>

        <!-- Medio de Pago -->
        <div class="section">
            <div class="row">
                <span class="label">Pago:</span>
                <span class="value"><?php echo ucfirst(htmlspecialchars($pedido['medio_pago'])); ?></span>
            </div>
        </div>

        <!-- Pie de Página -->
        <div class="footer">
            <div class="footer-line">¡Gracias por su compra!</div>
            <div class="footer-line">━━━━━━━━━━━━━━━━━</div>
            <div class="footer-line"><?php echo date('d/m/Y H:i:s', strtotime($pedido['fecha'])); ?></div>
        </div>
    </div>

    <!-- Botones de control -->
    <div class="no-print print-button">
        <button onclick="window.print()">🖨️ Imprimir / Descargar PDF</button>
        <button onclick="window.history.back()">Volver</button>
    </div>
    <div class="no-print download-button">
        <p>💡 Usa tu navegador para descargar como PDF (Ctrl+P o ⌘+P)</p>
    </div>

    <script>
        // Auto-imprimir si viene de un link con ?print=1
        if (new URLSearchParams(window.location.search).get('print') === '1') {
            window.onload = function() {
                window.print();
            };
        }
    </script>
</body>
</html>
