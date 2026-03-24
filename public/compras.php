<?php
require_once '../include/db.php';
require_once '../include/configuraciones.php';
require_once '../include/cliente.php';

$db = new Database();
$conexion = $db->getConexion();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_cliente'])) {
    header("Location: " . SITE_URL . "/public/login.php");
    exit();
}

$token = generarToken();
$_SESSION['token'] = $token;
$idCliente = $_SESSION['user_cliente'];

// Obtener información básica del cliente para el hero
$sqlCliente = $conexion->prepare("SELECT nombre, apellido FROM clientes WHERE id = ?");
$sqlCliente->execute([$idCliente]);
$clienteInfo = $sqlCliente->fetch(PDO::FETCH_ASSOC);

// Obtener compras del cliente
$sql = $conexion->prepare("SELECT id, id_transaccion, fecha, status, total, medio_pago FROM compra WHERE id_cliente = ? ORDER BY fecha DESC");
$sql->execute([$idCliente]);
$compras = $sql->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es" data-bs-theme="auto">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="tt.css">
    <title>Mis Compras - Tienda Exclusiva</title>
    <?php include '../include/links.php'; ?>
    <style>
        /* ======================================================================
           DISEÑO BLACK & GOLD PREMIUM - MIS COMPRAS
           ====================================================================== */
        
        :root {
            --primary-black: #000000;
            --primary-gold: #ffd700;
            --gold-light: #fff3b0;
            --gold-dark: #e6b800;
            --text-light: #ffffff;
            --text-dark: #333333;
            --gray-light: #fafafa;
            --gray-medium: #f0f0f0;
            --gray-border: #e0e0e0;
            --shadow-sm: 0 8px 16px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 12px 24px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 20px 40px rgba(0, 0, 0, 0.12);
            --shadow-gold: 0 10px 30px rgba(255, 215, 0, 0.15);
            --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            --border-radius-card: 24px;
            --border-radius-badge: 40px;
            --border-radius-button: 40px;
        }

        /* ----------------------------------------------------------------------
           HERO SECTION (idéntico a mi_cuenta.php)
           ---------------------------------------------------------------------- */
        
        .purchases-hero {
            background: var(--primary-black);
            padding: 2.5rem 0;
            margin-bottom: 2rem;
            position: relative;
            border-bottom: 3px solid var(--primary-gold);
        }
        
        .purchases-hero .container {
            position: relative;
            z-index: 2;
        }
        
        .purchases-hero h1 {
            color: var(--text-light);
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 0.3rem;
            letter-spacing: -0.02em;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }
        
        .purchases-hero h1 i {
            color: var(--primary-gold);
            font-size: 2rem;
        }
        
        .purchases-hero h1 span {
            color: var(--primary-gold);
            font-weight: 800;
            background: rgba(255, 215, 0, 0.1);
            padding: 0.1rem 0.6rem;
            border-radius: 40px;
        }
        
        .purchases-hero .breadcrumb {
            background: transparent;
            padding: 0;
            margin-bottom: 0.5rem;
        }
        
        .purchases-hero .breadcrumb-item {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
        }
        
        .purchases-hero .breadcrumb-item a {
            color: var(--primary-gold);
            text-decoration: none;
            transition: var(--transition);
            font-weight: 500;
        }
        
        .purchases-hero .breadcrumb-item a:hover {
            color: var(--text-light);
            text-decoration: underline;
            text-underline-offset: 4px;
        }
        
        .purchases-hero .breadcrumb-item.active {
            color: var(--text-light);
        }
        
        .purchases-hero .breadcrumb-item + .breadcrumb-item::before {
            color: var(--primary-gold);
            content: "›";
            font-size: 1.2rem;
            line-height: 1;
        }

        /* ----------------------------------------------------------------------
           BOTÓN VOLVER (elegante)
           ---------------------------------------------------------------------- */
        
        .btn-back {
            background: white;
            color: var(--primary-black);
            border: 1px solid var(--gray-border);
            padding: 0.7rem 1.8rem;
            border-radius: var(--border-radius-button);
            font-weight: 600;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            margin-bottom: 1.8rem;
            text-decoration: none;
            box-shadow: var(--shadow-sm);
            font-size: 0.95rem;
            backdrop-filter: blur(4px);
        }
        
        .btn-back:hover {
            background: var(--primary-black);
            color: var(--primary-gold);
            border-color: var(--primary-black);
            transform: translateX(-6px);
            box-shadow: var(--shadow-md);
        }

        .btn-back i {
            transition: transform 0.2s;
        }

        .btn-back:hover i {
            transform: translateX(-3px);
        }

        /* ----------------------------------------------------------------------
           MENÚ DE NAVEGACIÓN (idéntico a mi_cuenta.php)
           ---------------------------------------------------------------------- */

        .menu-links {
            display: flex;
            gap: 1rem;
            margin-bottom: 2.5rem;
            flex-wrap: wrap;
        }

        .menu-link {
            padding: 0.9rem 2.2rem;
            border: 2px solid var(--gray-border);
            border-radius: var(--border-radius-button);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            box-shadow: var(--shadow-sm);
            font-size: 0.95rem;
            letter-spacing: 0.3px;
            transition: all 0.3s ease;

            background: var(--gray-light);
            color: var(--text-dark);
            border-color: #dee2e6;
        }

        .menu-link i {
            color: var(--primary-gold);
            transition: var(--transition);
            font-size: 1.1rem;
        }

        .menu-link:hover {
            background: #e9ecef;
            border-color: #adb5bd;
            color: var(--primary-black);
            transform: translateY(-2px);
            text-decoration: none;
        }

        .menu-link:hover i {
            color: var(--primary-black);
        }

        .menu-link.active {
            background: var(--primary-black);
            color: var(--primary-gold);
            box-shadow: var(--shadow-gold);
        }

        .menu-link.active:hover{
            background: var(--primary-gold);
            color: var(--primary-black);
            border-color: var(--primary-gold);
            transform: translateY(-3px);
            text-decoration:none;
            box-shadow: 0 8px 20px rgba(255, 215, 0, 0.3);
        }


        .menu-link.active i {
            color: var(--primary-gold);
        }

        /* ----------------------------------------------------------------------
           TARJETA DE COMPRA (rediseñada con estilo premium)
           ---------------------------------------------------------------------- */
        
        .purchase-card {
            background: white;
            border-radius: var(--border-radius-card);
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(255, 215, 0, 0.2);
            overflow: hidden;
            transition: var(--transition);
            margin-bottom: 2rem;
        }

        .purchase-card:hover {
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            transform: translateY(-4px);
        }

        .purchase-header {
            background: linear-gradient(135deg, #ffffff 0%, var(--gray-light) 100%);
            padding: 1.5rem 2rem;
            border-bottom: 2px solid var(--primary-gold);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            position: relative;
        }

        .purchase-header::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100px;
            height: 2px;
            background: var(--primary-gold);
        }

        .purchase-id {
            display: flex;
            flex-direction: column;
        }

        .purchase-id-label {
            font-size: 0.75rem;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .purchase-id-label i {
            color: var(--primary-gold);
        }

        .purchase-id-value {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--primary-black);
            font-family: 'Courier New', monospace;
            background: white;
            padding: 0.3rem 1rem;
            border-radius: 40px;
            border: 1px solid var(--gray-border);
            box-shadow: var(--shadow-sm);
            margin-top: 0.3rem;
        }

        .purchase-date {
            font-size: 0.95rem;
            color: var(--primary-black);
            background: rgba(255, 215, 0, 0.1);
            padding: 0.5rem 1.2rem;
            border-radius: 40px;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        }

        .purchase-date i {
            color: var(--primary-gold);
        }

        .purchase-body {
            padding: 2rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            align-items: center;
        }

        .purchase-info {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .info-label {
            font-size: 0.75rem;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-label i {
            color: var(--primary-gold);
        }

        .info-value {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-black);
            background: white;
            padding: 0.3rem 1rem;
            border-radius: 40px;
            display: inline-block;
            border: 1px solid var(--gray-border);
            box-shadow: var(--shadow-sm);
        }

        /* ----------------------------------------------------------------------
           BADGES DE ESTADO (mejorados)
           ---------------------------------------------------------------------- */
        
        .status-badge {
            display: inline-block;
            padding: 0.6rem 1.5rem;
            border-radius: var(--border-radius-badge);
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            width: fit-content;
            box-shadow: var(--shadow-sm);
            border: 1px solid transparent;
        }

        .status-completed, .status-approved {
            background: linear-gradient(145deg, #eaf7ea, #d4edda);
            color: #166534;
            border-color: #c3e6cb;
        }

        .status-pending {
            background: linear-gradient(145deg, #fffbeb, #fef3c7);
            color: #92400e;
            border-color: #fcd34d;
        }

        .status-processing {
            background: linear-gradient(145deg, #f0f9ff, #e0f2fe);
            color: #0c4a6e;
            border-color: #bae6fd;
        }

        .status-failed {
            background: linear-gradient(145deg, #fef2f2, #fee2e2);
            color: #991b1b;
            border-color: #fecaca;
        }

        /* ----------------------------------------------------------------------
           BOTONES DE ACCIÓN (refinados)
           ---------------------------------------------------------------------- */
        
        .purchase-actions {
            display: flex;
            gap: 0.8rem;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .btn-small {
            padding: 0.8rem 1.8rem;
            font-size: 0.9rem;
            font-weight: 600;
            border-radius: var(--border-radius-button);
            text-decoration: none;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            border: 2px solid;
            cursor: pointer;
            background: transparent;
            letter-spacing: 0.3px;
        }

        .btn-details {
            background: var(--primary-black);
            color: var(--primary-gold);
            border: none;
            box-shadow: var(--shadow-sm);
        }

        .btn-details:hover {
            background: var(--primary-gold);
            color: var(--primary-black);
            transform: translateY(-3px);
            box-shadow: 0 12px 28px rgba(255, 215, 0, 0.4);
            border-color: var(--gold-dark);
            text-decoration: none;
        }

        .btn-pdf {
            background: var(--gray-light);
            color: var(--text-dark);
            border-color: #dee2e6;
        }

        .btn-pdf:hover {
            background: #e9ecef;
            border-color: #adb5bd;
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
            color: var(--text-dark);
            text-decoration: none;
            transform: translateY(-3px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        }

        .btn-small i {
            transition: transform 0.2s;
        }

        .btn-small:hover i {
            transform: scale(1.1);
        }

        /* ----------------------------------------------------------------------
           ESTADO VACÍO (elegante)
           ---------------------------------------------------------------------- */
        
        .empty-state {
            text-align: center;
            padding: 5rem 2rem;
            background: white;
            border-radius: var(--border-radius-card);
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(255, 215, 0, 0.2);
        }

        .empty-state-icon {
            font-size: 4rem;
            color: var(--primary-gold);
            margin-bottom: 1.5rem;
        }

        .empty-state-text {
            color: var(--primary-black);
            font-size: 1.3rem;
            font-weight: 500;
            margin-bottom: 2rem;
        }

        .btn-shop {
            background: var(--primary-black);
            color: var(--primary-gold);
            border: 2px solid var(--primary-gold);
            padding: 1rem 2.5rem;
            border-radius: var(--border-radius-button);
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            font-size: 1rem;
        }

        .btn-shop:hover {
            background: var(--primary-gold);
            color: var(--primary-black);
            transform: translateY(-3px);
            box-shadow: 0 12px 28px rgba(255, 215, 0, 0.4);
        }

        /* ----------------------------------------------------------------------
           MENSAJES Y ALERTAS (opcional, por si se necesitan)
           ---------------------------------------------------------------------- */
        
        .alert-message {
            padding: 1.2rem 1.8rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            font-weight: 500;
            box-shadow: var(--shadow-sm);
            border-left: 6px solid;
        }

        .alert-info {
            background: linear-gradient(145deg, #f0f9ff, #e0f2fe);
            border-left-color: #0c4a6e;
            color: #0c4a6e;
        }

        .alert-info i {
            color: #0c4a6e;
            font-size: 1.4rem;
        }

        /* ----------------------------------------------------------------------
           RESPONSIVE (optimizado)
           ---------------------------------------------------------------------- */
        
        @media (max-width: 991px) {
            .purchases-hero h1 {
                font-size: 2rem;
            }
        }
        
        @media (max-width: 768px) {
            .purchases-hero h1 {
                font-size: 1.8rem;
            }

            .purchase-body {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .purchase-actions {
                justify-content: flex-start;
            }

            .menu-links {
                flex-direction: column;
                gap: 0.8rem;
            }
            
            .menu-link {
                width: 100%;
                justify-content: center;
            }

            .btn-small {
                width: 100%;
                justify-content: center;
            }

            .purchase-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
        
        @media (max-width: 576px) {
            .purchases-hero h1 {
                font-size: 1.6rem;
                flex-wrap: wrap;
            }
            
            .btn-back {
                width: 100%;
                justify-content: center;
            }

            .purchase-id-value {
                font-size: 0.95rem;
                word-break: break-all;
            }

            .info-value {
                font-size: 1rem;
            }

            .purchase-body {
                padding: 1.5rem;
            }

            .purchase-header {
                padding: 1.2rem 1.5rem;
            }
        }
    </style>
</head>

<body>
    <?php include '../include/header.php'; ?>

    <!-- HERO SECTION (estilo Black & Gold) -->
    <section class="about-hero">
        <div class="container text-center">
            <h1 class="fadeInUp">Mis Co<span>mpras</span></h1>
            <div class="gold-line fadeInUp" style="animation-delay: 0.2s;"></div>
            <p class="fadeInUp" style="animation-delay: 0.4s;">
                Aquí puedes revisar y actualizar tu información personal para mantener tu cuenta al día. 
                Asegúrate de que tus datos estén correctos para una mejor experiencia de compra.
            </p>
        </div>
    </section>

    <main class="section-padding">
        <div class="container">

            <!-- Menú de navegación (idéntico a mi_cuenta.php) -->
            <div class="menu-links">
                <a href="<?php echo SITE_URL; ?>/public/compras.php" class="menu-link active">
                    Mis Compras
                </a>
                <a href="<?php echo SITE_URL; ?>/public/mi_cuenta.php" class="menu-link">
                    Mi Información
                </a>
            </div>

            <!-- Purchases List con nuevo diseño -->
            <?php if (empty($compras)): ?>
                <!-- Estado vacío elegante -->
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="bi bi-bag-x"></i>
                    </div>
                    <p class="empty-state-text">Aún no has realizado ninguna compra</p>
                    <a href="index.php" class="btn-shop">
                        <i class="bi bi-shop"></i> Comenzar a comprar
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($compras as $compra): ?>
                    <!-- Tarjeta de compra rediseñada -->
                    <div class="purchase-card">
                        <div class="purchase-header">
                            <div class="purchase-id">
                                <span class="purchase-id-label">
                                    <i class="bi bi-upc-scan"></i> Orden #
                                </span>
                                <span class="purchase-id-value"><?php echo htmlspecialchars($compra['id_transaccion']); ?></span>
                            </div>
                            <div class="purchase-date">
                                <i class="bi bi-calendar-event"></i> 
                                <?php echo date('d/m/Y H:i', strtotime($compra['fecha'])); ?>
                            </div>
                        </div>

                        <div class="purchase-body">
                            <div class="purchase-info">
                                <div>
                                    <div class="info-label"><i class="bi bi-coin"></i> Total</div>
                                    <div class="info-value"><?php echo MONEDA . ' ' . number_format($compra['total'], 2); ?></div>
                                </div>
                            </div>

                            <div class="purchase-info">
                                <div>
                                    <div class="info-label"><i class="bi bi-credit-card"></i> Método de Pago</div>
                                    <div class="info-value">
                                        <?php 
                                            $metodo = htmlspecialchars($compra['medio_pago']);
                                            echo $metodo === 'Tarjeta de crédito' ? '💳 Crédito' : ($metodo === 'Tarjeta de débito' ? '💳 Débito' : $metodo);
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <div class="purchase-info">
                                <div>
                                    <div class="info-label"><i class="bi bi-check-circle"></i> Estado</div>
                                    <span class="status-badge status-<?php echo strtolower($compra['status']); ?>">
                                        <?php 
                                            $statusText = [
                                                'COMPLETED' => 'Completado',
                                                'APPROVED' => 'Aprobado',
                                                'PENDING' => 'Pendiente',
                                                'PROCESSING' => 'Procesando',
                                                'FAILED' => 'Fallido'
                                            ];
                                            $status = strtoupper($compra['status']);
                                            echo $statusText[$status] ?? ucfirst(strtolower($compra['status']));
                                        ?>
                                    </span>
                                </div>
                            </div>

                            <div class="purchase-actions">
                                <a href="compra_detalle.php?orden=<?php echo htmlspecialchars($compra['id_transaccion']); ?>&token=<?php echo $_SESSION['token']; ?>" class="btn-small btn-details">
                                    <i class="bi bi-eye"></i> Ver Detalles
                                </a>
                                <a href="../include/generar_pdf.php?id_compra=<?php echo $compra['id']; ?>" class="btn-small btn-pdf" target="_blank">
                                    <i class="bi bi-file-pdf"></i> Descargar PDF
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <?php include '../include/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>