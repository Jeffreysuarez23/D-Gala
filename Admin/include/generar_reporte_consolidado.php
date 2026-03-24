<?php
/**
 * Generador de Reportes Consolidados de Compras en PDF/HTML
 * Permite descargar reportes de múltiples pedidos o un rango de fechas
 */

require_once("../include/db.php");
require_once("../include/configuracionesAD.php");

$db = new Database();
$conexion = $db->getConexion();

// Obtener filtros
$filtro_estado = isset($_GET['estado']) ? trim($_GET['estado']) : '';
$filtro_fecha_inicio = isset($_GET['fecha_inicio']) ? trim($_GET['fecha_inicio']) : '';
$filtro_fecha_fin = isset($_GET['fecha_fin']) ? trim($_GET['fecha_fin']) : '';
$filtro_cliente = isset($_GET['cliente']) ? trim($_GET['cliente']) : '';

// Construir consulta
$sql = "SELECT c.id, c.id_transaccion, c.fecha, c.status, c.email, c.id_cliente, 
                c.total, c.medio_pago, cl.nombre, cl.apellido 
        FROM compra c 
        LEFT JOIN clientes cl ON c.id_cliente = cl.id 
        WHERE 1=1 ";

$parametros = [];

if (!empty($filtro_estado)) {
    $sql .= "AND c.status = ? ";
    $parametros[] = $filtro_estado;
}

if (!empty($filtro_cliente)) {
    $sql .= "AND (cl.nombre LIKE ? OR cl.apellido LIKE ? OR c.email LIKE ? OR c.id_transaccion LIKE ?) ";
    $filtro_like = '%' . $filtro_cliente . '%';
    $parametros[] = $filtro_like;
    $parametros[] = $filtro_like;
    $parametros[] = $filtro_like;
    $parametros[] = $filtro_like;
}

if (!empty($filtro_fecha_inicio)) {
    $sql .= "AND DATE(c.fecha) >= ? ";
    $parametros[] = $filtro_fecha_inicio;
}

if (!empty($filtro_fecha_fin)) {
    $sql .= "AND DATE(c.fecha) <= ? ";
    $parametros[] = $filtro_fecha_fin;
}

$sql .= "ORDER BY c.fecha DESC";

$stmt = $conexion->prepare($sql);
$stmt->execute($parametros);
$compras = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular totales
$total_ventas = 0;
$total_items = 0;

foreach ($compras as $compra) {
    $total_ventas += $compra['total'];
}

// Generar HTML
$titulo_reporte = "Reporte Consolidado de Compras";
if (!empty($filtro_fecha_inicio) && !empty($filtro_fecha_fin)) {
    $titulo_reporte .= " (" . date('d/m/Y', strtotime($filtro_fecha_inicio)) . " - " . date('d/m/Y', strtotime($filtro_fecha_fin)) . ")";
} elseif (!empty($filtro_fecha_inicio)) {
    $titulo_reporte .= " (desde " . date('d/m/Y', strtotime($filtro_fecha_inicio)) . ")";
} elseif (!empty($filtro_fecha_fin)) {
    $titulo_reporte .= " (hasta " . date('d/m/Y', strtotime($filtro_fecha_fin)) . ")";
}

$html = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($titulo_reporte) . '</title>
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
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .header {
            border-bottom: 3px solid #000000;
            padding-bottom: 20px;
            margin-bottom: 40px;
        }
        
        .header h1 {
            color: #000000;
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #666;
            font-size: 13px;
        }
        
        .summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .summary-card {
            background: linear-gradient(135deg, #3b3c41 0%, #000000 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        
        .summary-card h3 {
            font-size: 14px;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 1px;
            opacity: 0.9;
            margin-bottom: 10px;
        }
        
        .summary-card .value {
            font-size: 28px;
            font-weight: bold;
        }
        
        .section-title {
            color: #000000;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 20px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        thead {
            background-color: #f3f4f6;
            border-bottom: 2px solid #e5e7eb;
        }
        
        th {
            padding: 12px;
            text-align: left;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #333;
        }
        
        td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 13px;
        }
        
        tbody tr:hover {
            background-color: #f9fafb;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            text-transform: capitalize;
        }
        
        .badge-pendiente { background-color: #fef3c7; color: #92400e; }
        .badge-procesando { background-color: #dbeafe; color: #0c4a6e; }
        .badge-enviado { background-color: #d1fae5; color: #065f46; }
        .badge-entregado { background-color: #d1e7ee; color: #0369a1; }
        .badge-cancelado { background-color: #fee2e2; color: #7f1d1d; }
        
        .badge-credit_card { background-color: #ddd6fe; color: #5b21b6; }
        .badge-transferencia { background-color: #alloc; color: #0c4a6e; }
        .badge-paypal { background-color: #fcd34d; color: #78350f; }
        
        .footer {
            border-top: 2px solid #e5e7eb;
            padding-top: 20px;
            text-align: center;
            color: #666;
            font-size: 12px;
            margin-top: 40px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
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
            <h1>' . htmlspecialchars($titulo_reporte) . '</h1>
            <p><strong>Generado el</strong> ' . date('d/m/Y H:i:s') . '</p>';

$filtros_aplicados = [];
if (!empty($filtro_estado)) {
    $filtros_aplicados[] = 'Estado: ' . htmlspecialchars(ucfirst($filtro_estado));
}
if (!empty($filtro_cliente)) {
    $filtros_aplicados[] = 'Búsqueda: ' . htmlspecialchars($filtro_cliente);
}
if (!empty($filtro_fecha_inicio) || !empty($filtro_fecha_fin)) {
    if (!empty($filtro_fecha_inicio) && !empty($filtro_fecha_fin)) {
        $filtros_aplicados[] = 'Período: ' . date('d/m/Y', strtotime($filtro_fecha_inicio)) . ' - ' . date('d/m/Y', strtotime($filtro_fecha_fin));
    } elseif (!empty($filtro_fecha_inicio)) {
        $filtros_aplicados[] = 'Desde: ' . date('d/m/Y', strtotime($filtro_fecha_inicio));
    } else {
        $filtros_aplicados[] = 'Hasta: ' . date('d/m/Y', strtotime($filtro_fecha_fin));
    }
}

if (count($filtros_aplicados) > 0) {
    $html .= '<p style="color: #666; margin-top: 10px;"><strong>Filtros:</strong> ' . implode(' | ', $filtros_aplicados) . '</p>';
}

$html .= '</div>
        
        <!-- Summary -->
        <div class="summary">
            <div class="summary-card">
                <h3>Total de Pedidos</h3>
                <div class="value">' . count($compras) . '</div>
            </div>
            <div class="summary-card">
                <h3>Ventas Totales</h3>
                <div class="value">$' . number_format($total_ventas, 2, '.', ',') . '</div>
            </div>';

// Contar por estado
$estados_count = [];
foreach ($compras as $compra) {
    $estado = $compra['status'];
    $estados_count[$estado] = ($estados_count[$estado] ?? 0) + 1;
}

if (count($estados_count) > 0) {
    $html .= '<div class="summary-card">
                <h3>Promedio por Pedido</h3>
                <div class="value">$' . number_format(count($compras) > 0 ? $total_ventas / count($compras) : 0, 2, '.', ',') . '</div>
            </div>';
}

$html .= '</div>
        
        <!-- Orders Table -->
        <div>
            <div class="section-title"> Detalle de Pedidos</div>';

if (count($compras) > 0) {
    $html .= '<table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Transacción</th>
                        <th>Cliente</th>
                        <th>Email</th>
                        <th>Fecha</th>
                        <th>Método de Pago</th>
                        <th>Estado</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>';
    
    foreach ($compras as $compra) {
        $html .= '<tr>
                        <td>' . str_pad($compra['id'], 6, '0', STR_PAD_LEFT) . '</td>
                        <td>' . htmlspecialchars($compra['id_transaccion']) . '</td>
                        <td>' . htmlspecialchars($compra['nombre'] . ' ' . $compra['apellido']) . '</td>
                        <td>' . htmlspecialchars($compra['email']) . '</td>
                        <td>' . date('d/m/Y H:i', strtotime($compra['fecha'])) . '</td>
                        <td><span class="badge badge-' . str_replace('_', '', htmlspecialchars($compra['medio_pago'])) . '">' . htmlspecialchars(ucfirst(str_replace('_', ' ', $compra['medio_pago']))) . '</span></td>
                        <td><span class="badge badge-' . htmlspecialchars($compra['status']) . '">' . htmlspecialchars(ucfirst($compra['status'])) . '</span></td>
                        <td class="text-right">$' . number_format($compra['total'], 2, '.', ',') . '</td>
                    </tr>';
    }
    
    $html .= '</tbody>
            </table>';
} else {
    $html .= '<div class="empty-state">
                <p>No se encontraron pedidos con los filtros especificados</p>
            </div>';
}

$html .= '</div>
        
        <!-- Resumen por Estado -->
        <div>
            <div class="section-title"> Resumen por Estado</div>
            <table>
                <thead>
                    <tr>
                        <th>Estado</th>
                        <th class="text-center">Cantidad</th>
                        <th class="text-right">Monto Total</th>
                        <th class="text-right">Porcentaje</th>
                    </tr>
                </thead>
                <tbody>';

$total_por_estado = [];
foreach ($compras as $compra) {
    $estado = $compra['status'];
    if (!isset($total_por_estado[$estado])) {
        $total_por_estado[$estado] = ['cantidad' => 0, 'total' => 0];
    }
    $total_por_estado[$estado]['cantidad']++;
    $total_por_estado[$estado]['total'] += $compra['total'];
}

foreach ($total_por_estado as $estado => $datos) {
    $porcentaje = ($total_ventas > 0) ? ($datos['total'] / $total_ventas * 100) : 0;
    $html .= '<tr>
                    <td><span class="badge badge-' . htmlspecialchars($estado) . '">' . htmlspecialchars(ucfirst($estado)) . '</span></td>
                    <td class="text-center">' . $datos['cantidad'] . '</td>
                    <td class="text-right">$' . number_format($datos['total'], 2, '.', ',') . '</td>
                    <td class="text-right">' . number_format($porcentaje, 1) . '%</td>
                </tr>';
}

$html .= '</tbody>
            </table>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p><strong>Reporte Profesional de Compras</strong></p>
            <p>Este documento contiene información confidencial de la tienda.</p>
            <p style="margin-top: 20px; color: #999;">Generado por Sistema de Gestión de Compras | ' . date('d/m/Y H:i:s') . '</p>
        </div>
    </div>
    
    <script>
        // Permitir impresión automática si se solicita
        window.addEventListener("load", function() {
            var url = new URL(window.location);
            var print = url.searchParams.get("print");
            if (print === "true") {
                window.print();
            }
        });
    </script>
</body>
</html>';

header('Content-Type: text/html; charset=utf-8');
header('Content-Disposition: inline; filename="reporte_consolidado_' . date('Y-m-d') . '.html"');

echo $html;
?>
