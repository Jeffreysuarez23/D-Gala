<?php
require_once '../include/db.php';
require_once '../include/configuraciones.php';

?>

<!DOCTYPE html>
<html lang="es" data-bs-theme="auto">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compra Completada - Tienda Online</title>
    <link rel="stylesheet" href="../public/tt.css">
    <?php include '../include/links.php'; ?>
    <meta name="theme-color" content="#000000">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: linear-gradient(135deg, var(--secondary-light) 0%, var(--primary-light) 100%);
        }

        .success-container {
            text-align: center;
            max-width: 1850px;
            width: 100%;
            padding: 4rem 3rem;
            background-color: var(--primary-light);
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            border: 1px solid var(--border-light);
            animation: slideIn 0.6s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success-icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 2.5rem;
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            color: #22c55e;
            border: 4px solid #86efac;
            box-shadow: 0 10px 30px rgba(34, 197, 94, 0.2);
            animation: scaleIn 0.7s ease-out;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0.8);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .success-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 1rem;
            letter-spacing: 0.5px;
        }

        .success-message {
            color: var(--accent-gray);
            font-size: 1.15rem;
            margin-bottom: 2rem;
            line-height: 1.8;
        }

        .order-info {
            background-color: var(--secondary-light);
            padding: 2.5rem;
            border-radius: 8px;
            margin: 2.5rem 0;
            border-left: 5px solid #28a745;
            text-align: left;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.2rem 0;
            border-bottom: 1px solid var(--border-light);
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 700;
            color: var(--primary-dark);
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 1px;
        }

        .info-value {
            color: var(--accent-gray);
            font-size: 1.1rem;
            text-align: right;
            font-weight: 600;
        }

        .next-steps {
            background: linear-gradient(135deg, #f0fdf4 0%, #e8fce7 100%);
            border-left: 5px solid #28a745;
            padding: 2.5rem;
            border-radius: 8px;
            margin: 2.5rem 0;
            text-align: left;
        }

        .next-steps h3 {
            color: var(--primary-dark);
            font-weight: 700;
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .next-steps ol {
            color: var(--accent-gray);
            padding-left: 2rem;
        }

        .next-steps li {
            margin-bottom: 1rem;
            line-height: 1.7;
            font-size: 1rem;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 3rem;
        }

        .btn-action {
            padding: 1rem 2rem;
            font-weight: 700;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.95rem;
            border: 2px solid;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
        }

        .btn-action:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-medium);
        }

        .btn-primary-action {
            background-color: var(--primary-dark);
            color: var(--primary-light);
            border:none;
            border-radius: 10px;
            color: var(--primary-gold);
        }

        .btn-primary-action:hover {
            background-color: var(--primary-gold);
            transform: translateY(-2px);
            box-shadow: var(--shadow-dark);
            border:none;
            color: black;
            text-decoration: none;
        }

        .btn-secondary-action {
            background: var(--gray-light);
            color: var(--text-dark);
            border-color: #dee2e6;
        }

        .btn-secondary-action:hover {
            background: #e9ecef;
            border-color: #adb5bd;
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
            color: var(--text-dark);
            text-decoration: none;
        }

        .email-notice {
            background-color: #f0f9ff;
            border-left: 4px solid #0ea5e9;
            padding: 1.5rem;
            border-radius: 6px;
            margin-top: 2rem;
            text-align: left;
        }

        .email-notice h4 {
            color: #0c4a6e;
            font-weight: 700;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .email-notice p {
            color: #0c4a6e;
            margin: 0;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <?php include '../include/header.php'; ?>

    <section class="about-hero">
        <div class="container text-center">
            <h1 class="fadeInUp">Tu co<span>mpra</span></h1>
            <div class="gold-line fadeInUp" style="animation-delay: 0.2s;"></div>
            <p class="fadeInUp" style="animation-delay: 0.4s;">
                Gracias por tu compra. Estamos procesando tu pedido y te enviaremos una confirmación por correo electrónico con los detalles de tu compra.
            </p>
        </div>
    </section>

    <main>
        <div class="container">
            <div class="success-container">
                <!-- Success Icon -->
                <div class="success-icon">
                    ✓
                </div>

                <!-- Title -->
                <h1 class="success-title">¡Compra Completada!</h1>

                <!-- Message -->
                <p class="success-message">
                    Tu pago ha sido procesado correctamente y tu pedido ha sido registrado en nuestro sistema.
                </p>

                <!-- Order Info -->
                <div class="order-info">
                    <div class="info-row">
                        <span class="info-label">Estado</span>
                        <span class="info-value" style="color: #22c55e; font-weight: 600;">✓ Aprobado</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Método de Pago</span>
                        <span class="info-value">PayPal</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Fecha</span>
                        <span class="info-value"><?php echo date('d/m/Y H:i'); ?></span>
                    </div>
                </div>

                <!-- Next Steps -->
                <div class="next-steps">
                    <h3><i class="bi bi-info-circle"></i> Próximos pasos:</h3>
                    <ol>
                        <li>Recibirás un correo de confirmación con los detalles de tu compra</li>
                        <li>Podrás descargar tu comprobante en PDF desde tu cuenta</li>
                        <li>Consulta la sección "Mis Compras" para ver el estado de tu pedido</li>
                    </ol>
                </div>

                <!-- Email Notice -->
                <div class="email-notice">
                    <h4><i class="bi bi-envelope-check"></i> Revisa tu correo</h4>
                    <p>Se ha enviado un comprobante detallado a la dirección de correo registrada en tu cuenta con todos los detalles de tu compra.</p>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <a href="../public/compras.php" class="btn-action btn-primary-action">
                        <i class="bi bi-bag-check"></i> Ver Mis Compras
                    </a>
                    <a href="../public/index.php" class="btn-action btn-secondary-action">
                        <i class="bi bi-shop"></i> Continuar Comprando
                    </a>
                </div>
            </div>
        </div>
    </main>

    <?php include '../include/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Limpiar el badge del carrito cuando se carga esta página
        document.addEventListener('DOMContentLoaded', function() {
            // Verificar si viene de una compra completada
            if (sessionStorage.getItem('carrito_limpiado') === 'true') {
                // Actualizar el badge del carrito a 0
                const cartBadge = document.getElementById('num_cart');
                if (cartBadge) {
                    cartBadge.textContent = '0';
                }
                // Limpiar la bandera
                sessionStorage.removeItem('carrito_limpiado');
            }
        });
    </script>
