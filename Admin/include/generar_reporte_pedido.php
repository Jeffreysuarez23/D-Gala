<?php
/**
 * Generador de Reportes Profesionales de Pedidos/Compras en PDF
 */

require_once("../include/db.php");
require_once("../include/configuracionesAD.php");

// Validar que se reciba un ID de pedido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: ID de pedido no especificado");
}

$id_compra = intval($_GET['id']);
$db = new Database();
$conexion = $db->getConexion();

// Obtener datos del pedido
$sql_pedido = "SELECT c.id, c.id_transaccion, c.fecha, c.status, c.email, c.id_cliente, 
                       c.total, c.medio_pago, cl.nombre, cl.apellido, cl.telefono, cl.documento,
                       cl.direccion, cl.ciudad, cl.estado, cl.codigo_postal, cl.pais
                FROM compra c 
                LEFT JOIN clientes cl ON c.id_cliente = cl.id 
                WHERE c.id = ?";

$stmt_pedido = $conexion->prepare($sql_pedido);
$stmt_pedido->execute([$id_compra]);
$pedido = $stmt_pedido->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    die("Error: Pedido no encontrado");
}

// Obtener detalles del pedido
$sql_detalles = "SELECT * FROM detalle_compra WHERE id_compra = ? ORDER BY id ASC";
$stmt_detalles = $conexion->prepare($sql_detalles);
$stmt_detalles->execute([$id_compra]);
$detalles = $stmt_detalles->fetchAll(PDO::FETCH_ASSOC);

// Usar HTML a PDF mediante iText o generación HTML simple
// Vamos a generar un HTML profesional que puede ser impreso como PDF

header('Content-Type: text/html; charset=utf-8');
header('Content-Disposition: attachment; filename="reporte_' . $pedido['id_transaccion'] . '.html"');

// O mejor aún, generar PDF directamente si está disponible TCPDF
// Si no existe TCPDF, usaremos la opción de HTML printable

$html = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Compra #' . htmlspecialchars($pedido['id_transaccion']) . '</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
            line-height: 1.6;
            color: #333;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            background-color: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            border-bottom: 3px solid #6366f1;
            padding-bottom: 20px;
        }
        
        .company-info h1 {
            color: #6366f1;
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .company-info p {
            color: #666;
            font-size: 13px;
            margin: 3px 0;
        }
        
        .invoice-info {
            text-align: right;
        }
        
        .invoice-info h2 {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .invoice-info p {
            color: #666;
            font-size: 13px;
            margin: 3px 0;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 12px;
            margin-top: 10px;
        }
        
        .status-pendiente { background-color: #fef3c7; color: #92400e; }
        .status-procesando { background-color: #dbeafe; color: #0c4a6e; }
        .status-enviado { background-color: #d1fae5; color: #065f46; }
        .status-entregado { background-color: #d1e7ee; color: #0369a1; }
        .status-cancelado { background-color: #fee2e2; color: #7f1d1d; }
        
        .content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }
        
        .section-title {
            color: #6366f1;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 15px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 8px;
        }
        
        .client-info, .order-info {
            font-size: 13px;
        }
        
        .client-info p, .order-info p {
            margin: 6px 0;
            line-height: 1.8;
        }
        
        .label {
            color: #666;
            font-weight: 500;
        }
        
        .value {
            color: #333;
        }
        
        .table-section {
            margin-bottom: 40px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        thead {
            background-color: #6366f1;
            color: white;
        }
        
        th {
            padding: 12px;
            text-align: left;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 13px;
        }
        
        tbody tr:hover {
            background-color: #f9fafb;
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .totals {
            display: flex;
            justify-content: flex-end;
            margin-top: 30px;
            margin-bottom: 30px;
        }
        
        .totals-table {
            width: 350px;
        }
        
        .totals-table tr {
            border-bottom: 1px solid #e5e7eb;
        }
        
        .totals-table tr:last-child {
            border-bottom: 2px solid #6366f1;
            font-weight: bold;
        }
        
        .totals-table td {
            padding: 10px;
            border: none;
        }
        
        .totals-table .label {
            text-align: left;
        }
        
        .totals-table .amount {
            text-align: right;
            color: #333;
        }
        
        .total-row {
            background-color: #f9fafb;
            font-size: 16px;
            color: #6366f1;
            font-weight: bold;
        }
        
        .footer {
            border-top: 2px solid #e5e7eb;
            padding-top: 20px;
            text-align: center;
            color: #666;
            font-size: 12px;
            margin-top: 40px;
        }
        
        .footer p {
            margin: 5px 0;
        }
        
        .footer-divider {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
            text-align: center;
        }
        
        .footer-item {
            flex: 1;
        }
        
        .payment-info {
            background-color: #f9fafb;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .payment-info p {
            font-size: 13px;
            margin: 5px 0;
        }
        
        @media print {
            body {
                background-color: white;
                padding: 0;
            }
            .container {
                box-shadow: none;
                padding: 20px;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                <h1>🛒 TIENDA ONLINE</h1>
                <p><strong>Email:</strong> ' . htmlspecialchars(Config::CONTACT_EMAIL ?? 'contacto@tienda.com') . '</p>
                <p><strong>Teléfono:</strong> ' . htmlspecialchars(Config::CONTACT_PHONE ?? '+1-800-0000') . '</p>
            </div>
            <div class="invoice-info">
                <h2>Comprobante de Compra</h2>
                <p><span class="label">ID de Transacción:</span> <span class="value">' . htmlspecialchars($pedido['id_transaccion']) . '</span></p>
                <p><span class="label">Orden #:</span> <span class="value">' . str_pad($pedido['id'], 6, '0', STR_PAD_LEFT) . '</span></p>
                <p><span class="label">Fecha:</span> <span class="value">' . date('d/m/Y H:i', strtotime($pedido['fecha'])) . '</span></p>
                <span class="status-badge status-' . strtolower($pedido['status']) . '">' . strtoupper($pedido['status']) . '</span>
            </div>
        </div>
        
        <!-- Content -->
        <div class="content">
            <!-- Cliente -->
            <div>
                <div class="section-title">📋 Datos del Cliente</div>
                <div class="client-info">
                    <p><span class="label">Nombre:</span> <span class="value">' . htmlspecialchars($pedido['nombre'] . ' ' . $pedido['apellido']) . '</span></p>
                    <p><span class="label">Email:</span> <span class="value">' . htmlspecialchars($pedido['email']) . '</span></p>
                    <p><span class="label">Teléfono:</span> <span class="value">' . htmlspecialchars($pedido['telefono'] ?? 'No especificado') . '</span></p>
                    <p><span class="label">Documento:</span> <span class="value">' . htmlspecialchars($pedido['documento'] ?? 'No especificado') . '</span></p>
                    <p><span class="label">Dirección:</span> <span class="value">' . htmlspecialchars($pedido['direccion'] ?? 'No especificada') . '</span></p>
                    <p><span class="label">Ciudad:</span> <span class="value">' . htmlspecialchars($pedido['ciudad'] ?? 'No especificada') . '</span></p>
                    <p><span class="label">Código Postal:</span> <span class="value">' . htmlspecialchars($pedido['codigo_postal'] ?? 'No especificado') . '</span></p>
                </div>
            </div>
            
            <!-- Detalles del Pedido -->
            <div>
                <div class="section-title">📦 Detalles del Pedido</div>
                <div class="order-info">
                    <p><span class="label">Método de Pago:</span> <span class="value">' . ucfirst(htmlspecialchars($pedido['medio_pago'])) . '</span></p>
                    <p><span class="label">Estado del Pedido:</span> <span class="value">' . ucfirst(htmlspecialchars($pedido['status'])) . '</span></p>
                    <p><span class="label">Cantidad de Artículos:</span> <span class="value">' . count($detalles) . '</span></p>
                </div>
                
                <div class="payment-info">
                    <strong style="color: #6366f1;">Información de Pago</strong>
                    <p>Medio de Pago: ' . ucfirst(htmlspecialchars($pedido['medio_pago'])) . '</p>
                    <p>Total a Pagar: $' . number_format($pedido['total'], 2, '.', ',') . '</p>
                </div>
            </div>
        </div>
        
        <!-- Items Table -->
        <div class="table-section">
            <div class="section-title">📝 Artículos Comprados</div>
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th class="text-center">Cantidad</th>
                        <th class="text-right">Precio Unitario</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>';

$subtotal = 0;
foreach ($detalles as $detalle) {
    $subtotal_item = $detalle['precio'] * $detalle['cantidad'];
    $subtotal += $subtotal_item;
    
    $html .= '<tr>
                        <td>' . htmlspecialchars($detalle['titulo']) . '</td>
                        <td class="text-center">' . $detalle['cantidad'] . '</td>
                        <td class="text-right">$' . number_format($detalle['precio'], 2, '.', ',') . '</td>
                        <td class="text-right">$' . number_format($subtotal_item, 2, '.', ',') . '</td>
                    </tr>';
}

$html .= '</tbody>
            </table>
        </div>
        
        <!-- Totals -->
        <div class="totals">
            <table class="totals-table">
                <tr>
                    <td class="label">Subtotal:</td>
                    <td class="amount">$' . number_format($subtotal, 2, '.', ',') . '</td>
                </tr>
                <tr>
                    <td class="label">Impuestos:</td>
                    <td class="amount">$0.00</td>
                </tr>
                <tr class="total-row">
                    <td class="label">TOTAL A PAGAR:</td>
                    <td class="amount">$' . number_format($pedido['total'], 2, '.', ',') . '</td>
                </tr>
            </table>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p><strong>¡Gracias por su compra!</strong></p>
            <p>Este es un comprobante de compra oficial. Guárdelo para su registro.</p>
            
            <div class="footer-divider">
                <div class="footer-item">
                    <p><strong>Soporte</strong></p>
                    <p>' . htmlspecialchars(Config::CONTACT_EMAIL ?? 'contacto@tienda.com') . '</p>
                </div>
                <div class="footer-item">
                    <p><strong>Horario</strong></p>
                    <p>Lunes - Viernes<br>9:00 AM - 6:00 PM</p>
                </div>
                <div class="footer-item">
                    <p><strong>Teléfono</strong></p>
                    <p>' . htmlspecialchars(Config::CONTACT_PHONE ?? '+1-800-0000') . '</p>
                </div>
            </div>
            
            <p style="margin-top: 30px; color: #999; font-size: 11px;">
                Generado el ' . date('d/m/Y H:i:s') . ' | Sistema de Gestión de Compras
            </p>
        </div>
    </div>
    
    <script>
        // Permitir impresión y descarga como PDF
        window.addEventListener("load", function() {
            // Detectar si es una descarga o impresión
            var url = new URL(window.location);
            var print = url.searchParams.get("print");
            
            if (print === "true") {
                window.print();
            }
        });
    </script>
</body>
</html>';

// Validar si se solicita descargar como PDF o solo visualizar
$download = isset($_GET['download']) ? filter_var($_GET['download'], FILTER_VALIDATE_BOOLEAN) : false;

if ($download) {
    // Intenta usar TCPDF si está disponible
    $tcpdf_path = '../include/../../../vendor/autoload.php';
    
    if (file_exists($tcpdf_path) || class_exists('TCPDF')) {
        // Si TCPDF está disponible, usarlo
        require_once($tcpdf_path);
        
        // Crear PDF
        $pdf = new \TCPDF();
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->AddPage();
        
        // Limpiar HTML para TCPDF
        $html_clean = strip_tags($html, '<br><strong><em><u><table><tbody><thead><tr><td><th><ul><li>');
        $pdf->writeHTML($html_clean, true, false, true, false, '');
        
        // Descargar
        $filename = 'reporte_' . str_replace(['/', ' ', ':'], '_', $pedido['id_transaccion']) . '.pdf';
        $pdf->Output($filename, 'D');
        exit;
    } else {
        // Si no está TCPDF, usar mPDF o generar HTML para impresión
        // Por ahora, redirigir a impresión
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: inline; filename="reporte_' . str_replace(['/', ' ', ':'], '_', $pedido['id_transaccion']) . '.html"');
        
        // Agregar script para auto-imprimir
        echo str_replace(
            'if (print === "true") {',
            'var shouldPrint = true; if (true) {',
            $html
        );
        exit;
    }
} else {
    // Solo visualizar
    header('Content-Type: text/html; charset=utf-8');
    echo $html;
    exit;
}
?>
