<?php
require_once '../include/db.php';
require_once '../include/configuraciones.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$db = new Database();
$conexion = $db->getConexion();
$lista_carrito = array();

// 1. Agregar productos
$productos = isset($_SESSION['carrito']['productos']) ? $_SESSION['carrito']['productos'] : array();
if (!empty($productos)) {
    foreach ($productos as $id_prod => $cantidad) {
        $sql = $conexion->prepare("SELECT id, titulo, precio, imagen, descuento FROM productos WHERE id = ? AND activo = 1");
        $sql->execute([$id_prod]);
        $producto = $sql->fetch(PDO::FETCH_ASSOC);
        if ($producto) {
            $producto['cantidad'] = $cantidad;
            $producto['tipo'] = 'producto';
            $lista_carrito[] = $producto;
        }
    }
}

// 2. Agregar variantes
$variantes = isset($_SESSION['carrito']['variantes']) ? $_SESSION['carrito']['variantes'] : array();
if (!empty($variantes)) {
    foreach ($variantes as $key => $variante) {
        $variante['tipo'] = 'variante';
        
        // Obtener imagen y datos del producto original
        if (isset($variante['id_producto'])) {
            $sql_prod = $conexion->prepare("SELECT titulo, imagen FROM productos WHERE id = ?");
            $sql_prod->execute([$variante['id_producto']]);
            $prod_original = $sql_prod->fetch(PDO::FETCH_ASSOC);
            
            if ($prod_original) {
                $variante['imagen'] = $prod_original['imagen'];
                $variante['titulo_producto'] = $prod_original['titulo'];
            }
        }
        
        $lista_carrito[] = $variante;
    }
}

if (empty($lista_carrito)) {
    echo '<script>alert("Carrito vacío, no se puede proceder al pago"); window.location.href = "index.php";</script>';
    exit;
}

$total = 0;
?>

<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Resumen de Pago</title>
    <link rel="stylesheet" href="tt.css">
    <?php include '../include/links.php'; ?>
    <meta name="theme-color" content="#ffffff">
    
    <style>
        /* Estilos modernos y minimalistas - SOLO VISUAL, SIN AFECTAR BACKEND */
        .payment-wrapper {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 30px;
            align-items: start;
            max-width: 1200px;
            margin: 0 auto;
        }

        @media (max-width: 992px) {
            .payment-wrapper {
                grid-template-columns: 1fr;
                gap: 24px;
                justify-items: center; /* Centra los items cuando están en columna */
            }
            
            .payment-summary,
            .payment-methods {
                width: 100%;
                max-width: 600px; /* Ancho máximo para que no se estiren demasiado */
                margin: 0 auto; /* Centrado horizontal */
            }
        }

        /* Columna izquierda - Productos - Diseño limpio */
        .payment-summary {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.1);
            border: 1px solid #f0f0f0;
            width: 100%;
        }

        .summary-header {
            padding: 20px 24px;
            border-bottom: 1px solid #f0f0f0;
            background: var(--primary-black);
        }

        .summary-header h3 {
            font-size: 1.2rem;
            font-weight: 600;
            color: #f0f0f0;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .summary-header h3 i {
            color: var(--primary-gold);
            font-size: 1.3rem;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-table tbody td {
            padding: 20px 24px;
            border-bottom: 1px solid #f5f5f5;
            vertical-align: middle;
        }

        .product-item {
            display: flex;
            gap: 16px;
            align-items: center;
        }

        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid #eaeaea;
            background: #f9f9f9;
        }

        .product-details {
            flex: 1;
        }

        .product-name {
            font-weight: 600;
            font-size: 1rem;
            color: var(--primary-black);
            margin-bottom: 4px;
        }

        .product-badge {
            background: #f5f5f5;
            color: #666;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 500;
            display: inline-block;
        }

        .product-variant {
            font-size: 0.8rem;
            color: #888;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #fafafa;
            padding: 2px 8px;
            border-radius: 20px;
            margin-left: 4px;
        }

        .price-section {
            text-align: right;
        }

        .price-current {
            font-weight: 600;
            color: var(--primary-black);
            font-size: 1rem;
        }

        .price-original {
            color: #aaa;
            font-size: 0.8rem;
            text-decoration: line-through;
            display: block;
            margin-bottom: 2px;
        }

        .qty-badge {
            background: #f5f5f5;
            color: var(--primary-black);
            padding: 4px 10px;
            border-radius: 30px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .qty-badge i {
            font-size: 0.7rem;
            color: var(--primary-gold);
        }

        .summary-footer {
            background: #fafafa;
            padding: 20px 24px;
            border-top: 1px solid #f0f0f0;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .total-label {
            font-size: 1rem;
            font-weight: 500;
            color: #666;
        }

        .total-amount {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--primary-black);
        }

        .total-amount span {
            font-size: 1rem;
            font-weight: 500;
            margin-right: 4px;
            color: #888;
        }

        /* Columna derecha - Métodos de pago - SIN SCROLL */
        .payment-methods {
            background: white;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.1);
            border: 1px solid #f0f0f0;
            position: sticky;
            top: 100px;
            width: 100%;
        }

        @media (max-width: 992px) {
            .payment-methods {
                position: static;
            }
        }

        .methods-header {
            margin-bottom: 24px;
            text-align: left;
        }

        @media (max-width: 992px) {
            .methods-header {
                text-align: center; /* Centrado en móvil */
            }
        }

        .methods-header h4 {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-black);
            margin-bottom: 12px;
        }

        .secure-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f0f9f0;
            color: #2e7d32;
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .secure-badge i {
            font-size: 0.9rem;
        }

        /* Opción PayPal */
        .payment-option {
            margin-bottom: 20px;
            padding: 16px;
            border: 1px solid #eaeaea;
            border-radius: 16px;
            transition: all 0.2s ease;
            background: white;
        }

        @media (max-width: 992px) {
            .payment-option {
                max-width: 500px;
                margin-left: auto;
                margin-right: auto;
            }
        }

        .payment-option:hover {
            border-color: var(--primary-gold);
        }

        .payment-option.active {
            border-color: var(--primary-gold);
            background: #fffdf5;
        }

        .payment-option-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        @media (max-width: 480px) {
            .payment-option-header {
                flex-wrap: wrap;
            }
        }

        .payment-icon {
            width: 40px;
            height: 40px;
            background: #f5f5f5;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-black);
            font-size: 1.3rem;
        }

        .payment-icon.paypal {
            background: #003087;
            color: white;
        }

        .payment-info {
            flex: 1;
        }

        .payment-info h5 {
            font-weight: 600;
            margin: 0 0 2px 0;
            color: var(--primary-black);
            font-size: 1rem;
        }

        .payment-info p {
            color: #888;
            font-size: 0.75rem;
            margin: 0;
        }

        .payment-badge {
            background: #f0f0f0;
            color: #666;
            padding: 4px 10px;
            border-radius: 30px;
            font-size: 0.65rem;
            font-weight: 600;
        }

        /* PayPal container */
        .paypal-container {
            margin-top: 12px;
            padding: 12px;
            background: #fafafa;
            border-radius: 12px;
        }

        /* Resumen de compra - Minimalista */
        .order-summary {
            background: #fafafa;
            border-radius: 16px;
            padding: 16px;
            margin: 16px 0;
        }

        @media (max-width: 992px) {
            .order-summary {
                max-width: 500px;
                margin-left: auto;
                margin-right: auto;
            }
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            color: #666;
            font-size: 0.9rem;
        }

        .summary-item.total {
            margin-top: 10px;
            padding-top: 12px;
            border-top: 1px dashed #ddd;
            font-weight: 600;
            color: var(--primary-black);
            font-size: 1.1rem;
        }

        .summary-item.total span:last-child {
            color: var(--primary-black);
            font-size: 1.2rem;
        }

        /* Botones - Manteniendo funcionalidad pero mejorando diseño */
        .payment-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            tranform: translateY(0);
             transition: all 0.2s ease;
        }
        .payment-actions:hover {
            transform: translateY(-3px);
        }

        @media (max-width: 992px) {
            .payment-actions {
                align-items: center;
            }
            
            .btn-back {
                width: 100%;
                max-width: 500px;
            }
        }

        .btn-back {
            background: white;
            color: var(--primary-black);
            border: 1px solid #ddd;
            padding: 12px;
            border-radius: 50px;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-back:hover {
            background: #f5f5f5;
            border-color: #ccc;
            transform: translateY(-1px);
        }

        /* Iconos de métodos de pago */
        .payment-icons-minimal {
            display: flex;
            justify-content: center;
            gap: 16px;
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid #f0f0f0;
        }

        .payment-icons-minimal i {
            font-size: 1.8rem;
            color: #aaa;
            transition: color 0.2s ease;
        }

        .payment-icons-minimal i:hover {
            color: var(--primary-gold);
        }

        /* Ajustes adicionales para centrado en móvil */
        @media (max-width: 992px) {
            .payment-methods {
                text-align: center;
            }
            
            .payment-option-header {
                justify-content: center;
                text-align: left;
            }
            
            .payment-info {
                text-align: left;
            }
            
            .secure-badge {
                margin: 0 auto;
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .product-item {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .product-image {
                width: 90px;
                height: 90px;
            }
            
            .summary-table tbody td {
                padding: 16px;
            }
            
            .total-amount {
                font-size: 1.4rem;
            }
            
            .payment-methods {
                padding: 20px;
            }
        }

        @media (max-width: 576px) {
            .product-image {
                width: 70px;
                height: 70px;
            }
            
            .payment-icons-minimal i {
                font-size: 1.5rem;
            }
            
            .payment-summary,
            .payment-methods {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include '../include/header.php'; ?>
    
    <section class="about-hero">
        <div class="container text-center">
            <h1 class="fadeInUp">Resumen <span>de Pago</span></h1>
            <div class="gold-line fadeInUp" style="animation-delay: 0.2s;"></div>
            <p class="fadeInUp" style="animation-delay: 0.4s;">
                Revisa tus productos y elige tu método de pago para completar tu compra de forma segura y rápida.
            </p>
        </div>
    </section>
    
    <main class="container section-padding">
        <div class="payment-wrapper">
            <!-- Columna izquierda: Productos -->
            <div class="payment-summary fadeInUp">
                <div class="summary-header">
                    <h3>
                        <i class="fas fa-box"></i>
                        Productos
                    </h3>
                </div>
                
                <div class="table-responsive">
                    <table class="summary-table">
                        <tbody>
                            <?php 
                            foreach ($lista_carrito as $item):
                                if ($item['tipo'] === 'variante'):
                                    $titulo = $item['titulo_producto'] ?? 'Producto';
                                    $precio = $item['precio'] ?? 0;
                                    $cantidad = $item['cantidad'] ?? 1;
                                    $imagen = $item['imagen'] ?? 'noadd.jpg';
                                    $subtotal = $precio * $cantidad;
                                    $total += $subtotal;
                                    $variante_info = ($item['talla'] ?? '') . ' - ' . ($item['color'] ?? '');
                            ?>
                                    <tr>
                                        <td>
                                            <div class="product-item">
                                                <img src="<?php echo '../assets/img/' . htmlspecialchars($imagen); ?>" 
                                                     alt="<?php echo htmlspecialchars($titulo); ?>" 
                                                     class="product-image"
                                                     onerror="this.src='../assets/img/noadd.jpg'">
                                                <div class="product-details">
                                                    <div class="product-name"><?php echo htmlspecialchars($titulo); ?></div>
                                                    <div>
                                                        <span class="product-badge">Variante</span>
                                                        <span class="product-variant">
                                                            <i class="fas fa-tag"></i>
                                                            <?php echo htmlspecialchars($variante_info); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="price-section">
                                                <span class="price-current"><?php echo MONEDA . number_format($precio, 2, '.', ','); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="qty-badge">
                                                <i class="fas fa-times"></i>
                                                <?php echo $cantidad; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="price-current"><?php echo MONEDA . number_format($subtotal, 2, '.', ','); ?></span>
                                        </td>
                                    </tr>
                            <?php 
                                else:
                                    $id = $item['id'] ?? 0;
                                    $titulo = $item['titulo'] ?? 'Producto';
                                    $precio = $item['precio'] ?? 0;
                                    $descuento = $item['descuento'] ?? 0;
                                    $cantidad = $item['cantidad'] ?? 1;
                                    $imagen = $item['imagen'] ?? 'noadd.jpg';
                                    $precio_desc = $precio - (($precio * $descuento) / 100);
                                    $subtotal = $cantidad * $precio_desc;
                                    $total += $subtotal;
                            ?>
                                    <tr>
                                        <td>
                                            <div class="product-item">
                                                <img src="<?php echo '../assets/img/' . htmlspecialchars($imagen); ?>" 
                                                     alt="<?php echo htmlspecialchars($titulo); ?>" 
                                                     class="product-image"
                                                     onerror="this.src='../assets/img/noadd.jpg'">
                                                <div class="product-details">
                                                    <div class="product-name"><?php echo htmlspecialchars($titulo); ?></div>
                                                    <?php if ($descuento > 0): ?>
                                                        <span class="product-badge">-<?php echo $descuento; ?>%</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="price-section">
                                                <?php if ($descuento > 0): ?>
                                                    <span class="price-original"><?php echo MONEDA . number_format($precio, 2, '.', ','); ?></span>
                                                <?php endif; ?>
                                                <span class="price-current"><?php echo MONEDA . number_format($precio_desc, 2, '.', ','); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="qty-badge">
                                                <i class="fas fa-times"></i>
                                                <?php echo $cantidad; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="price-current"><?php echo MONEDA . number_format($subtotal, 2, '.', ','); ?></span>
                                        </td>
                                    </tr>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="summary-footer">
                    <div class="total-row">
                        <span class="total-label">Total</span>
                        <span class="total-amount">
                            <span><?php echo MONEDA; ?></span>
                            <?php echo number_format($total, 2, '.', ','); ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Columna derecha: Métodos de pago - SIN SCROLL -->
            <div class="payment-methods fadeInUp" style="animation-delay: 0.1s">
                <div class="methods-header">
                    <h4>Método de pago</h4>
                    <div class="secure-badge">
                        <i class="fas fa-shield-alt"></i>
                        Pago 100% seguro
                    </div>
                </div>
                
                <!-- Opción PayPal (funcional) -->
                <div class="payment-option active">
                    <div class="payment-option-header">
                        <div class="payment-icon paypal">
                            <i class="fab fa-paypal"></i>
                        </div>
                        <div class="payment-info">
                            <h5>PayPal</h5>
                            <p>Paga con tu cuenta de PayPal</p>
                        </div>
                        <span class="payment-badge">Recomendado</span>
                    </div>
                    
                    <!-- PayPal Container -->
                    <div class="paypal-container">
                        <div id="paypal-button-container"></div>
                    </div>
                </div>
                
                <!-- Resumen de compra -->
                <div class="order-summary">
                    <div class="summary-item">
                        <span>Subtotal</span>
                        <span><?php echo MONEDA . number_format($total, 2, '.', ','); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Envío</span>
                        <span style="color: #2e7d32;">Gratis</span>
                    </div>
                    <div class="summary-item total">
                        <span>Total</span>
                        <span><?php echo MONEDA . number_format($total, 2, '.', ','); ?></span>
                    </div>
                </div>
                
                <!-- Botones de acción -->
                <div class="payment-actions">
                    <a style="text-decoration: none;" href="checkout.php" class="btn-back">
                        <i class="fas fa-arrow-left"></i>
                        Volver al carrito
                    </a>
                </div>
                
            </div>
        </div>
    </main>

    <?php include '../include/footer.php'; ?>

    <script src="https://www.paypal.com/sdk/js?client-id=<?php echo CLIENT_ID; ?>&components=buttons"></script>
    <script>
        paypal.Buttons({
            style: {
                color: 'gold',
                shape: 'pill',
                label: 'pay',
                height: 45
            },
            createOrder: function (data, actions) {
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: '<?php echo number_format($total, 2, '.', ''); ?>'
                        }
                    }]
                });
            },
            onCancel: function (data) {
                alert('Transacción fue cancelada');
                console.log(data);
            },
            onApprove: function (data, actions) {
                let url = '../include/captura.php'
                actions.order.capture().then(function (details) {
                    console.log(details);
                    
                    return fetch(url, {
                        method: 'post',
                        credentials: 'same-origin',
                        headers: {
                            'content-type': 'application/json'
                        },
                        body: JSON.stringify({
                            details: details
                        })
                    }).then(function(response){
                        if (!response.ok) {
                            console.error('Error en fetch captura:', response.statusText);
                            alert('Ocurrió un problema al procesar la compra. Intente nuevamente.');
                        }
                        window.location.href = '../include/confirmacion.php?key=' + details['id'];
                    }).catch(function(err){
                        console.error('Error en fetch captura (excepción):', err);
                        alert('Ocurrió un error en la conexión. Intente nuevamente.');
                    })
                });
            }
        }).render('#paypal-button-container');
    </script>

    <!-- Bootstrap JS (necesario para dropdowns y otros componentes de Bootstrap) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>