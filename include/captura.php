<?php
require_once 'db.php';
require_once 'configuraciones.php';

// Crear la conexión PDO
$db = new Database();
$conexion = $db->getConexion();  

// Obtener los datos de la solicitud
$json = file_get_contents('php://input');
$datos = json_decode($json, true);


if (is_array($datos)) {
    // Obtener el ID del cliente de la sesión
    $idCliente = $_SESSION['user_cliente'];

    // Consultar el correo del cliente
    try {
        $sql_prod = "SELECT email FROM clientes WHERE id = :id AND estatus = 1";
        $stmt_prod = $conexion->prepare($sql_prod);
        $stmt_prod->bindParam(':id', $idCliente);
        $stmt_prod->execute();
        $row_cliente = $stmt_prod->fetch(PDO::FETCH_ASSOC);

        if (!$row_cliente) {
            // Si no se encuentra el cliente, detener la ejecución
            echo "<script>alert('Cliente no encontrado o no activo.');</script>";
            exit();
        }

    } catch (PDOException $e) {
        echo "<script>alert('Error al obtener datos del cliente: " . $e->getMessage() . "');</script>";
        exit();
    }

    // Obtener los datos de la transacción
    $id_transaccion = $datos['details']['id'];
    $monto = $datos['details']['purchase_units'][0]['amount']['value'];
    $status = $datos['details']['status'];
    $fecha = $datos['details']['update_time'];
    $fecha_nueva = date('Y-m-d H:i:s', strtotime($fecha));
    $email = $row_cliente['email'];
    
    // Insertar la compra en la base de datos
    try {
        $sql = "INSERT INTO compra (id_transaccion, fecha, status, email, id_cliente, total, medio_pago) 
                VALUES (:id_transaccion, :fecha, :status, :email, :id_cliente, :total, 'PayPal')";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':id_transaccion', $id_transaccion);
        $stmt->bindParam(':fecha', $fecha_nueva);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':id_cliente', $idCliente);
        $stmt->bindParam(':total', $monto);
        $stmt->execute();
        
        // Obtener el ID de la última compra insertada
        $id = $conexion->lastInsertId();

        if ($id > 0) {
            // Procesar productos del carrito
            $productos = isset($_SESSION['carrito']['productos']) ? $_SESSION['carrito']['productos'] : null;

            if ($productos != null) {
                foreach ($productos as $clave => $cantidad) {
                    try {
                        // Obtener los detalles del producto
                        $sql_prod = "SELECT id, titulo, precio, imagen, descuento FROM productos WHERE id = :id AND activo = 1";
                        $stmt_prod = $conexion->prepare($sql_prod);
                        $stmt_prod->bindParam(':id', $clave);
                        $stmt_prod->execute();
                        $row_prod = $stmt_prod->fetch(PDO::FETCH_ASSOC);

                        if ($row_prod) {
                            // Calcular el precio con descuento
                            $precio = $row_prod['precio'];
                            $descuento = $row_prod['descuento'];
                            $precio_desc = $precio - (($precio * $descuento) / 100);

                            // Insertar en detalle_compra
                            $sql_detalle = "INSERT INTO detalle_compra (id_compra, id_producto, titulo, precio, cantidad) 
                                            VALUES (:id_compra, :id_producto, :titulo, :precio, :cantidad)";
                            
                            $stmt_detalle = $conexion->prepare($sql_detalle);
                            $stmt_detalle->bindParam(':id_compra', $id);
                            $stmt_detalle->bindParam(':id_producto', $clave);
                            $stmt_detalle->bindParam(':titulo', $row_prod['titulo']);
                            $stmt_detalle->bindParam(':precio', $precio_desc);
                            $stmt_detalle->bindParam(':cantidad', $cantidad);
                            $stmt_detalle->execute();
                        }

                    } catch (PDOException $e) {
                        // Error al procesar el producto
                        echo "<script>alert('Error al procesar el producto: " . $e->getMessage() . "');</script>";
                        exit();
                    }
                }
            }

            // Procesar variantes del carrito
            $variantes = isset($_SESSION['carrito']['variantes']) ? $_SESSION['carrito']['variantes'] : [];
            if (!empty($variantes)) {
                foreach ($variantes as $clave_variante => $variante_data) {
                    try {
                        $variante_id = $variante_data['variante_id'];
                        $id_producto = $variante_data['id_producto'];
                        $cantidad_var = $variante_data['cantidad'];
                        $precio_var = $variante_data['precio'];
                        $talla = isset($variante_data['talla']) ? $variante_data['talla'] : '';
                        $color = isset($variante_data['color']) ? $variante_data['color'] : '';

                        // Obtener los detalles del producto
                        $sql_prod = "SELECT id, titulo, descuento FROM productos WHERE id = :id AND activo = 1";
                        $stmt_prod = $conexion->prepare($sql_prod);
                        $stmt_prod->bindParam(':id', $id_producto);
                        $stmt_prod->execute();
                        $row_prod = $stmt_prod->fetch(PDO::FETCH_ASSOC);

                        if ($row_prod) {
                            // Construir el título con variantes
                            $titulo_variante = $row_prod['titulo'];
                            if (!empty($talla) || !empty($color)) {
                                $variantes_info = [];
                                if (!empty($talla)) $variantes_info[] = "Talla: " . $talla;
                                if (!empty($color)) $variantes_info[] = "Color: " . $color;
                                $titulo_variante .= " (" . implode(", ", $variantes_info) . ")";
                            }

                            // Insertar en detalle_compra
                            $sql_detalle = "INSERT INTO detalle_compra (id_compra, id_producto, titulo, precio, cantidad) 
                                            VALUES (:id_compra, :id_producto, :titulo, :precio, :cantidad)";
                            
                            $stmt_detalle = $conexion->prepare($sql_detalle);
                            $stmt_detalle->bindParam(':id_compra', $id);
                            $stmt_detalle->bindParam(':id_producto', $id_producto);
                            $stmt_detalle->bindParam(':titulo', $titulo_variante);
                            $stmt_detalle->bindParam(':precio', $precio_var);
                            $stmt_detalle->bindParam(':cantidad', $cantidad_var);
                            $stmt_detalle->execute();
                        }
                    } catch (PDOException $e) {
                        error_log('Error al procesar variante: ' . $e->getMessage());
                    }
                }
            }

            // Enviar correo de confirmación (SE ENVÍA SIEMPRE, NO SOLO CON VARIANTES)
            require_once 'mailer.php';
            $mailer = new Mailer();
            $asunto = "Comprobante de tu Compra - Tienda Online";
            $cuerpo = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Compra</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Plus Jakarta Sans", Arial, sans-serif;
            background: linear-gradient(135deg, #f5f5f5 0%, #ffffff 100%);
            color: #5d657b;
        }

        .container {
            max-width: 700px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #000000 0%, #1a1a1a 100%);
            color: #ffffff;
            padding: 40px 20px;
            text-align: center;
            border-bottom: 4px solid #ffffff;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .header p {
            font-size: 14px;
            opacity: 0.9;
            margin: 0;
        }

        .content {
            padding: 30px;
        }

        .greeting {
            margin-bottom: 20px;
        }

        .greeting h2 {
            color: #000000;
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .greeting p {
            color: #5d657b;
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 25px 0;
            padding: 20px;
            background-color: #f5f5f5;
            border-radius: 8px;
        }

        .info-block h3 {
            color: #000000;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            border-bottom: 2px solid #000000;
            padding-bottom: 8px;
        }

        .info-block p {
            color: #5d657b;
            font-size: 13px;
            margin: 5px 0;
            line-height: 1.6;
        }

        .transaction-id {
            background-color: #000000;
            color: #ffffff;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
            margin: 20px 0;
        }

        .transaction-id-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.8;
        }

        .transaction-id-value {
            font-size: 18px;
            font-weight: 700;
            margin-top: 5px;
            font-family: monospace;
            word-break: break-all;
        }

        .products-section {
            margin: 30px 0;
        }

        .products-section h3 {
            color: #000000;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
            border-bottom: 3px solid #000000;
            padding-bottom: 10px;
        }

        .product-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f5f5f5;
            font-size: 13px;
        }

        .product-row:last-child {
            border-bottom: none;
        }

        .product-details {
            flex: 1;
        }

        .product-name {
            color: #000000;
            font-weight: 600;
            margin-bottom: 3px;
        }

        .product-quantity {
            color: #5d657b;
            font-size: 12px;
        }

        .product-price {
            text-align: right;
            min-width: 120px;
        }

        .product-unit {
            color: #5d657b;
            font-size: 12px;
        }

        .product-amount {
            color: #000000;
            font-weight: 600;
            font-size: 14px;
        }

        .summary-section {
            background-color: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 13px;
        }

        .summary-label {
            color: #5d657b;
        }

        .summary-value {
            color: #000000;
            font-weight: 600;
        }

        .summary-row.total {
            border-top: 2px solid #000000;
            padding-top: 10px;
            margin-top: 10px;
            font-weight: 700;
            font-size: 16px;
            color: #000000;
        }

        .cta-button {
            display: inline-block;
            background-color: #000000;
            color: #ffffff;
            padding: 12px 30px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 12px;
            margin-top: 20px;
            text-align: center;
        }

        .security-notice {
            background-color: #f0fdf4;
            border-left: 4px solid #22c55e;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
            font-size: 12px;
        }

        .security-notice i {
            color: #22c55e;
            font-weight: 700;
        }

        .security-text {
            color: #166534;
            margin: 0;
        }

        .footer-section {
            background-color: #1a1a1a;
            color: #ffffff;
            padding: 25px;
            text-align: center;
            font-size: 12px;
        }

        .footer-text {
            margin: 5px 0;
            color: rgba(255, 255, 255, 0.8);
        }

        .footer-note {
            color: rgba(255, 255, 255, 0.6);
            margin-top: 15px;
            font-size: 11px;
            padding-top: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }

        @media (max-width: 600px) {
            .info-grid {
                grid-template-columns: 1fr;
            }

            .header h1 {
                font-size: 24px;
            }

            .content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>✓ Compra Confirmada</h1>
            <p>Tu pedido ha sido procesado exitosamente</p>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Greeting -->
            <div class="greeting">
                <h2>¡Hola! Gracias por tu compra</h2>
                <p>Te adjuntamos los detalles de tu pedido. Si tienes cualquier pregunta, no dudes en contactarnos.</p>
            </div>

            <!-- Transaction ID -->
            <div class="transaction-id">
                <div class="transaction-id-label">ID de Transacción</div>
                <div class="transaction-id-value">' . htmlspecialchars($id_transaccion) . '</div>
            </div>

            <!-- Info Grid -->
            <div class="info-grid">
                <div class="info-block">
                    <h3>Información del Pedido</h3>
                    <p><strong>Fecha:</strong> ' . date('d/m/Y H:i', strtotime($fecha_nueva)) . '</p>
                    <p><strong>Estado:</strong> ' . strtoupper($status) . '</p>
                    <p><strong>Método:</strong> PayPal</p>
                </div>
                <div class="info-block">
                    <h3>Datos de Contacto</h3>
                    <p><strong>' . htmlspecialchars($email) . '</strong></p>
                </div>
            </div>

            <!-- Products Section -->
            <div class="products-section">
                <h3>Productos en tu Pedido</h3>';

                // Procesar productos del carrito
                $productos = isset($_SESSION['carrito']['productos']) ? $_SESSION['carrito']['productos'] : [];
                $total = 0;

                foreach ($productos as $clave => $cantidad) {
                    try {
                        $sql_prod = "SELECT id, titulo, precio, descuento, imagen FROM productos WHERE id = :id AND activo = 1";
                        $stmt_prod = $conexion->prepare($sql_prod);
                        $stmt_prod->bindParam(':id', $clave);
                        $stmt_prod->execute();
                        $row_prod = $stmt_prod->fetch(PDO::FETCH_ASSOC);

                        if ($row_prod) {
                            // Calcular el precio con descuento
                            $precio = $row_prod['precio'];
                            $descuento = $row_prod['descuento'];
                            $precio_desc = $precio - (($precio * $descuento) / 100);
                            $total_producto = $precio_desc * $cantidad;
                            $total += $total_producto;

                            // Agregar producto a la tabla
                            $cuerpo .= '
                <div class="product-row">
                    <div class="product-details">
                        <div class="product-name">' . htmlspecialchars($row_prod['titulo']) . '</div>
                        <div class="product-quantity">Cantidad: ' . $cantidad . '</div>
                    </div>
                    <div class="product-price">
                        <div class="product-unit">' . MONEDA . number_format($precio_desc, 2) . ' c/u</div>
                        <div class="product-amount">' . MONEDA . number_format($total_producto, 2) . '</div>
                    </div>
                </div>';
                        }
                    } catch (PDOException $e) {
                        error_log('Error al procesar producto en email: ' . $e->getMessage());
                    }
                }

                // Procesar variantes en el email
                $variantes = isset($_SESSION['carrito']['variantes']) ? $_SESSION['carrito']['variantes'] : [];
                if (!empty($variantes)) {
                    foreach ($variantes as $clave_variante => $variante_data) {
                        try {
                            $variante_id = $variante_data['variante_id'];
                            $id_producto = $variante_data['id_producto'];
                            $cantidad_var = $variante_data['cantidad'];
                            $precio_var = $variante_data['precio'];
                            $talla = isset($variante_data['talla']) ? $variante_data['talla'] : '';
                            $color = isset($variante_data['color']) ? $variante_data['color'] : '';

                            // Obtener el título del producto
                            $sql_prod = "SELECT id, titulo FROM productos WHERE id = :id AND activo = 1";
                            $stmt_prod = $conexion->prepare($sql_prod);
                            $stmt_prod->bindParam(':id', $id_producto);
                            $stmt_prod->execute();
                            $row_prod = $stmt_prod->fetch(PDO::FETCH_ASSOC);

                            if ($row_prod) {
                                $total_producto = $precio_var * $cantidad_var;
                                $total += $total_producto;

                                // Construir información de variantes
                                $variantes_info = '';
                                if (!empty($talla) || !empty($color)) {
                                    $variantes_info = '<div style="color: #7c879a; font-size: 11px; margin-top: 2px;">';
                                    if (!empty($talla)) $variantes_info .= '📏 Talla: <strong>' . htmlspecialchars($talla) . '</strong>';
                                    if (!empty($color)) $variantes_info .= ' | 🎨 Color: <strong>' . htmlspecialchars($color) . '</strong>';
                                    $variantes_info .= '</div>';
                                }

                                $cuerpo .= '
                <div class="product-row">
                    <div class="product-details">
                        <div class="product-name">' . htmlspecialchars($row_prod['titulo']) . '</div>
                        ' . $variantes_info . '
                        <div class="product-quantity">Cantidad: ' . $cantidad_var . '</div>
                    </div>
                    <div class="product-price">
                        <div class="product-unit">' . MONEDA . number_format($precio_var, 2) . ' c/u</div>
                        <div class="product-amount">' . MONEDA . number_format($total_producto, 2) . '</div>
                    </div>
                </div>';
                            }
                        } catch (PDOException $e) {
                            error_log('Error al procesar variante en email: ' . $e->getMessage());
                        }
                    }
                }

                $cuerpo .= '
            </div>

            <!-- Summary Section -->
            <div class="summary-section">
                <div class="summary-row">
                    <span class="summary-label">Subtotal:</span>
                    <span class="summary-value">' . MONEDA . number_format($total, 2) . '</span>
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
                    <span>' . MONEDA . number_format($monto, 2) . '</span>
                </div>
            </div>

            <!-- Security Notice -->
            <div class="security-notice">
                <p class="security-text">🔒 <strong>Tu compra está protegida</strong> con encriptación SSL de 256 bits y procesamiento seguro de PayPal.</p>
            </div>

            <!-- CTA -->
            <p style="margin: 20px 0; text-align: center;">
                <a href="' . SITE_URL . '/public/compras.php" class="cta-button">Ver mis compras</a>
            </p>

            <p style="color: #5d657b; font-size: 13px; text-align: center; margin-top: 20px;">
                Puedes descargar tu comprobante en formato PDF desde tu cuenta.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer-section">
            <p class="footer-text"><strong>Tienda Online</strong></p>
            <p class="footer-text">Gracias por tu confianza. Esperamos verte de nuevo pronto.</p>
            <div class="footer-note">
                <p style="margin: 0;">Si tienes preguntas sobre tu compra, no dudes en contactarnos.</p>
                <p style="margin: 5px 0 0 0;">&copy; ' . date("Y") . '. Todos los derechos reservados.</p>
            </div>
        </div>
    </div>
</body>
</html>';

                
                $mailer->EnviarEmail($email, $asunto, $cuerpo);

            // Limpiar el carrito de compras
            unset($_SESSION['carrito']);
            
            // Mostrar mensaje de éxito y redirigir a la confirmación
            echo "<script>
                alert('Compra realizada con éxito , Se le envio a su correo La informacin de la compra..');
                sessionStorage.setItem('carrito_limpiado', 'true');
                window.location.href='confirmacion.php';
            </script>";
            exit();
        }

    } catch (PDOException $e) {
        // Error al insertar la compra
        echo "<script>alert('Error al procesar la compra: " . $e->getMessage() . "');</script>";
        exit();
    }
}
?>
