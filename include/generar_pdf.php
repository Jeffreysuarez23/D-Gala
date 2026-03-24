<?php
/**
 * Generador de PDF para Compras
 * Requiere TCPDF
 */

require_once 'db.php';
require_once 'configuraciones.php';

if (!isset($_GET['id_compra']) || !isset($_SESSION['user_cliente'])) {
    die('Acceso no permitido');
}

$id_compra = $_GET['id_compra'];
$id_cliente = $_SESSION['user_cliente'];

// Verificar que la compra pertenece al cliente
$db = new Database();
$conexion = $db->getConexion();

try {
    // Obtener datos de la compra
    $sql = "SELECT c.*, cli.nombre, cli.apellido, cli.email, cli.telefono, cli.documento 
            FROM compra c 
            INNER JOIN clientes cli ON c.id_cliente = cli.id 
            WHERE c.id = :id AND c.id_cliente = :id_cliente";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':id', $id_compra);
    $stmt->bindParam(':id_cliente', $id_cliente);
    $stmt->execute();
    
    $compra = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$compra) {
        die('Compra no encontrada');
    }

    // Obtener detalles de la compra
    $sql_detalles = "SELECT dc.*, p.imagen FROM detalle_compra dc 
                     LEFT JOIN productos p ON dc.id_producto = p.id 
                     WHERE dc.id_compra = :id_compra";
    
    $stmt_detalles = $conexion->prepare($sql_detalles);
    $stmt_detalles->bindParam(':id_compra', $id_compra);
    $stmt_detalles->execute();
    
    $detalles = $stmt_detalles->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die('Error: ' . $e->getMessage());
}

// Incluir TCPDF (usando autoload si está disponible)
// Si no tienes TCPDF instalado, usaremos un método alternativo con HTML a PDF

// Para este caso, usaremos un HTML que el usuario pueda guardar como PDF
// usando "Guardar como PDF" en el navegador (Print to PDF)

$html = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Compra #' . $compra['id'] . '</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: "Plus Jakarta Sans", Arial, sans-serif;
            background-color: #ffffff;
            color: #5d657b;
            line-height: 1.6;
        }

        @media print {
            body {
                background-color: white;
            }
            .no-print {
                display: none;
            }
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .header {
            border-bottom: 3px solid #000000;
            padding-bottom: 30px;
            margin-bottom: 30px;
            text-align: center;
        }

        .header h1 {
            color: #000000;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
            letter-spacing: 2px;
        }

        .header p {
            color: #5d657b;
            font-size: 14px;
        }

        .receipt-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            gap: 20px;
        }

        .receipt-section {
            flex: 1;
        }

        .receipt-section h3 {
            color: #000000;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 10px;
            letter-spacing: 1px;
            border-bottom: 2px solid #f5f5f5;
            padding-bottom: 8px;
        }

        .receipt-section p {
            color: #5d657b;
            font-size: 13px;
            margin-bottom: 5px;
        }

        .receipt-section strong {
            color: #000000;
            display: block;
            margin-top: 5px;
        }

        .products-section {
            margin-bottom: 30px;
        }

        .products-section h3 {
            color: #000000;
            font-weight: 700;
            font-size: 14px;
            text-transform: uppercase;
            margin-bottom: 15px;
            letter-spacing: 1px;
            border-bottom: 2px solid #000000;
            padding-bottom: 10px;
        }

        .product-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f5f5f5;
        }

        .product-info {
            flex: 1;
        }

        .product-name {
            color: #000000;
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 3px;
        }

        .product-details {
            color: #5d657b;
            font-size: 12px;
        }

        .product-price {
            text-align: right;
            min-width: 100px;
        }

        .product-price-unit {
            color: #5d657b;
            font-size: 12px;
        }

        .product-price-amount {
            color: #000000;
            font-weight: 600;
            font-size: 13px;
        }

        .summary {
            background-color: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 13px;
        }

        .summary-row.total {
            border-top: 2px solid #000000;
            padding-top: 10px;
            margin-top: 10px;
            font-weight: 700;
            font-size: 16px;
            color: #000000;
        }

        .summary-label {
            color: #5d657b;
        }

        .summary-value {
            color: #000000;
            font-weight: 600;
        }

        .footer {
            background-color: #000000;
            color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            font-size: 12px;
            margin-top: 30px;
        }

        .footer p {
            color: #ffffff;
            margin-bottom: 5px;
        }

        .actions {
            text-align: center;
            margin-top: 30px;
            gap: 10px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 5px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 2px solid;
            cursor: pointer;
        }

        .btn-print {
            background-color: #000000;
            color: #ffffff;
            border-color: #000000;
        }

        .btn-print:hover {
            background-color: #1a1a1a;
        }

        .btn-back {
            background-color: #ffffff;
            color: #000000;
            border-color: #000000;
        }

        .btn-back:hover {
            background-color: #f5f5f5;
        }

        .transaction-id {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }

        .transaction-id-label {
            color: #5d657b;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .transaction-id-value {
            color: #000000;
            font-weight: 700;
            font-size: 16px;
            margin-top: 5px;
            font-family: monospace;
            word-break: break-all;
        }

        .date-time {
            color: #5d657b;
            font-size: 12px;
            margin-top: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>COMPROBANTE DE COMPRA</h1>
            <p>Gracias por tu confianza</p>
        </div>

        <!-- Transaction ID -->
        <div class="transaction-id">
            <div class="transaction-id-label">ID de Transacción</div>
            <div class="transaction-id-value">' . htmlspecialchars($compra['id_transaccion']) . '</div>
        </div>

        <!-- Receipt Info -->
        <div class="receipt-header">
            <div class="receipt-section">
                <h3>Datos del Cliente</h3>
                <p><strong>' . htmlspecialchars($compra['nombre'] . ' ' . $compra['apellido']) . '</strong></p>
                <p>' . htmlspecialchars($compra['documento']) . '</p>
                <p>' . htmlspecialchars($compra['email']) . '</p>
                <p>' . htmlspecialchars($compra['telefono']) . '</p>
            </div>
            <div class="receipt-section">
                <h3>Información de la Compra</h3>
                <p><strong>Pedido #' . str_pad($compra['id'], 5, '0', STR_PAD_LEFT) . '</strong></p>
                <p>Fecha: ' . date('d/m/Y H:i', strtotime($compra['fecha'])) . '</p>
                <p>Estado: <strong>' . strtoupper($compra['status']) . '</strong></p>
                <p>Método: ' . htmlspecialchars($compra['medio_pago']) . '</p>
            </div>
        </div>

        <!-- Products -->
        <div class="products-section">
            <h3>Productos Comprados</h3>';

            $total_items = 0;
            foreach ($detalles as $item) {
                $subtotal = $item['precio'] * $item['cantidad'];
                $total_items += $item['cantidad'];
                
                $html .= '
            <div class="product-item">
                <div class="product-info">
                    <div class="product-name">' . htmlspecialchars($item['titulo']) . '</div>
                    <div class="product-details">
                        Cantidad: ' . $item['cantidad'] . ' × ' . MONEDA . number_format($item['precio'], 2) . '
                    </div>
                </div>
                <div class="product-price">
                    <div class="product-price-amount">' . MONEDA . number_format($subtotal, 2) . '</div>
                </div>
            </div>';
            }

        $html .= '
        </div>

        <!-- Summary -->
        <div class="summary">
            <div class="summary-row">
                <span class="summary-label">Subtotal:</span>
                <span class="summary-value">' . MONEDA . number_format($compra['total'], 2) . '</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Envío:</span>
                <span class="summary-value">Gratis</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Impuestos:</span>
                <span class="summary-value">' . MONEDA . '0.00</span>
            </div>
            <div class="summary-row total">
                <span>Total a Pagar:</span>
                <span>' . MONEDA . number_format($compra['total'], 2) . '</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Tienda Online</strong></p>
            <p>Gracias por tu compra. Si tienes alguna pregunta, contáctanos.</p>
            <p>&copy; ' . date('Y') . ' Todos los derechos reservados.</p>
        </div>

        <!-- Date -->
        <div class="date-time">
            <p>Documento generado: ' . date('d/m/Y H:i:s') . '</p>
        </div>

        <!-- Actions -->
        <div class="actions no-print">
            <button class="btn btn-print" onclick="window.print()">
                <i class="bi bi-printer"></i> Descargar como PDF
            </button>
            <a href="' . SITE_URL . '/public/compras.php" class="btn btn-back">
                ← Volver a Mis Compras
            </a>
        </div>
    </div>

    <script>
        // Auto print para facilitar descarga como PDF
        // Comentar o descommentar según necesites
        // window.print();
    </script>
</body>
</html>';

// Configurar headers para mostrar como documento
header('Content-Type: text/html; charset=utf-8');
echo $html;
?>
