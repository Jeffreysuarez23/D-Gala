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

// Obtener información del cliente
$sql = $conexion->prepare("SELECT c.id, c.nombre, c.apellido, c.email, c.telefono, c.documento, u.nombre_usuario 
                          FROM clientes c 
                          LEFT JOIN usuarios u ON u.id_cliente = c.id 
                          WHERE c.id = ?");
$sql->execute([$idCliente]);
$cliente = $sql->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
    header("Location: " . SITE_URL . "/public/index.php");
    exit();
}

$errores = [];
$exito = false;

// Procesar formulario si se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['token']) {
        $errores[] = "Token de seguridad inválido";
    }
    
    if (empty($errores)) {
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $documento = trim($_POST['documento'] ?? '');
        
        // Validaciones básicas
        if (esNulo([$nombre, $apellido, $email, $telefono, $documento])) {
            $errores[] = "Todos los campos son obligatorios";
        }
        
        if (!validarEmail($email)) {
            $errores[] = "El email no es válido";
        }
        
        // Verificar si el email ya existe en otro usuario
        $sqlEmail = $conexion->prepare("SELECT id FROM clientes WHERE email = ? AND id != ?");
        $sqlEmail->execute([$email, $idCliente]);
        if ($sqlEmail->fetchColumn() > 0) {
            $errores[] = "El email ya está registrado";
        }
        
        // Si no hay errores, actualizar
        if (empty($errores)) {
            try {
                $sqlUpdate = $conexion->prepare("UPDATE clientes 
                                                SET nombre = ?, apellido = ?, email = ?, telefono = ?, documento = ?, fecha_modifica = NOW() 
                                                WHERE id = ?");
                
                if ($sqlUpdate->execute([$nombre, $apellido, $email, $telefono, $documento, $idCliente])) {
                    // Actualizar la sesión con los nuevos datos
                    $_SESSION['user_name'] = $nombre . ' ' . $apellido;
                    $_SESSION['user_email'] = $email;
                    
                    // Actualizar datos del cliente
                    $cliente['nombre'] = $nombre;
                    $cliente['apellido'] = $apellido;
                    $cliente['email'] = $email;
                    $cliente['telefono'] = $telefono;
                    $cliente['documento'] = $documento;
                    
                    $exito = true;
                    
                    // Generar nuevo token
                    $token = generarToken();
                    $_SESSION['token'] = $token;
                } else {
                    $errores[] = "Error al actualizar los datos";
                }
            } catch (Exception $e) {
                $errores[] = "Error en la base de datos: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es" data-bs-theme="auto">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="tt.css">
    <title>Mi Cuenta - Tienda Exclusiva</title>
    <?php include '../include/links.php'; ?>
    <style>
        /* ======================================================================
           DISEÑO BLACK & GOLD PREMIUM - MI CUENTA (VERSIÓN MEJORADA)
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
            --border-radius-input: 14px;
            --border-radius-button: 40px;
            --border-radius-badge: 40px;
        }

        /* ----------------------------------------------------------------------
           HERO SECTION (refinado)
           ---------------------------------------------------------------------- */
        
        .account-hero {
            background: var(--primary-black);
            padding: 2.5rem 0;
            margin-bottom: 2rem;
            position: relative;
            border-bottom: 3px solid var(--primary-gold);
        }
        
        .account-hero .container {
            position: relative;
            z-index: 2;
        }
        
        .account-hero h1 {
            color: var(--text-light);
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 0.3rem;
            letter-spacing: -0.02em;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }
        
        .account-hero h1 i {
            color: var(--primary-gold);
            font-size: 2rem;
        }
        
        .account-hero h1 span {
            color: var(--primary-gold);
            font-weight: 800;
            background: rgba(255, 215, 0, 0.1);
            padding: 0.1rem 0.6rem;
            border-radius: 40px;
        }
        
        .account-hero .breadcrumb {
            background: transparent;
            padding: 0;
            margin-bottom: 0.5rem;
        }
        
        .account-hero .breadcrumb-item {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
        }
        
        .account-hero .breadcrumb-item a {
            color: var(--primary-gold);
            text-decoration: none;
            transition: var(--transition);
            font-weight: 500;
        }
        
        .account-hero .breadcrumb-item a:hover {
            color: var(--text-light);
            text-decoration: underline;
            text-underline-offset: 4px;
        }
        
        .account-hero .breadcrumb-item.active {
            color: var(--text-light);
        }
        
        .account-hero .breadcrumb-item + .breadcrumb-item::before {
            color: var(--primary-gold);
            content: "›";
            font-size: 1.2rem;
            line-height: 1;
        }

        /* ----------------------------------------------------------------------
           BOTÓN VOLVER (más elegante)
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
           MENÚ DE NAVEGACIÓN (más sofisticado)
           ---------------------------------------------------------------------- */
        
        .menu-links {
            display: flex;
            gap: 1rem;
            margin-bottom: 2.5rem;
            flex-wrap: wrap;
        }

        .menu-link {
            padding: 0.9rem 2.2rem;
            background: #f9f9f9;
            border-radius: var(--border-radius-button);
            border: 2px solid #dee2e6;
            text-decoration: none;
            color: var(--primary-black);
            font-weight: 600;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            box-shadow: var(--shadow-sm);
            font-size: 0.95rem;
            letter-spacing: 0.3px;
        }

        .menu-link i {
            color: var(--primary-gold);
            transition: var(--transition);
            font-size: 1.1rem;
        }

        .menu-link.active {
            background: var(--primary-black);
            color: var(--primary-gold);
            border-color: var(--primary-gold);
            box-shadow: var(--shadow-gold);
        }

        .menu-link.active i {
            color: var(--primary-gold);
        }

        .menu-link:hover i{
            color: var(--primary-black);
        }

        .menu-link:hover {
            text-decoration: none;
            background: #e9e8e8;
            border: 2px solid #d7d9db;
            color: var(--primary-black);
            transform: translateY(-3px);
        }

        /* ----------------------------------------------------------------------
           TARJETA PRINCIPAL (rediseñada con más refinamiento)
           ---------------------------------------------------------------------- */
        
        .account-card {
            background: white;
            border-radius: var(--border-radius-card);
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(255, 215, 0, 0.2);
            overflow: hidden;
            transition: var(--transition);
        }

        .account-card:hover {
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .card-header-custom {
            background: linear-gradient(135deg, #ffffff 0%, var(--gray-light) 100%);
            padding: 1.8rem 2rem;
            border-bottom: 2px solid var(--primary-gold);
            position: relative;
        }

        .card-header-custom::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100px;
            height: 2px;
            background: var(--primary-gold);
        }

        .card-header-custom h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-black);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .card-header-custom h2 i {
            color: var(--primary-gold);
            font-size: 1.8rem;
        }

        .card-body-custom {
            padding: 2.5rem;
        }

        /* ----------------------------------------------------------------------
           SECCIÓN DE INFORMACIÓN (más elegante)
           ---------------------------------------------------------------------- */
        
        .info-section {
            background: linear-gradient(145deg, var(--gray-light), #ffffff);
            padding: 2rem;
            border-radius: 20px;
            margin-bottom: 2.5rem;
            border: 1px solid rgba(255, 215, 0, 0.3);
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.02);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 2rem;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            padding: 0.5rem 0;
            border-bottom: 1px dashed rgba(0,0,0,0.05);
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
            font-size: 1rem;
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
           FORMULARIO (estilo más limpio y moderno)
           ---------------------------------------------------------------------- */
        
        .form-group-custom {
            margin-bottom: 1.8rem;
        }

        .form-group-custom label {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--primary-black);
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: var(--transition);
        }

        .form-group-custom label i {
            color: var(--primary-gold);
            width: 20px;
            font-size: 1rem;
        }

        .form-group-custom input {
            width: 100%;
            padding: 0.9rem 1.2rem;
            border: 2px solid var(--gray-border);
            border-radius: var(--border-radius-input);
            font-size: 1rem;
            color: var(--primary-black);
            background: white;
            transition: var(--transition);
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
        }

        .form-group-custom input:focus {
            outline: none;
            border-color: var(--primary-gold);
            box-shadow: 0 0 0 4px rgba(255, 215, 0, 0.15), inset 0 2px 4px rgba(0,0,0,0.02);
            background-color: #fffdf5;
        }

        .form-group-custom input:hover {
            border-color: var(--gold-dark);
            background-color: #fefef8;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        /* ----------------------------------------------------------------------
           BOTONES (más refinados)
           ---------------------------------------------------------------------- */
        
        .form-actions {
            display: flex;
            gap: 1.2rem;
            margin-top: 2.5rem;
            flex-wrap: wrap;
        }

        .btn-custom {
            padding: 1rem 2.5rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: var(--border-radius-button);
            text-decoration: none;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            border: 2px solid;
            cursor: pointer;
            background: transparent;
            font-family: inherit;
            letter-spacing: 0.3px;
            justify-content: center;
        }

        .btn-save {
            background: var(--primary-black);
            color: var(--primary-gold);
            border-color: var(--primary-gold);
            border: none;
        }

        .btn-save:hover {
            background: var(--primary-gold);
            color: var(--primary-black);
            transform: translateY(-3px);
            box-shadow: 0 12px 28px rgba(255, 215, 0, 0.4);
            border-color: var(--gold-dark);
        }

        .btn-cancel {
            background: white;
            color: var(--primary-black);
            border-color: var(--gray-border);
        }

        .btn-cancel:hover {
            background: var(--primary-black);
            color: var(--primary-gold);
            border-color: var(--primary-black);
            transform: translateY(-3px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        }

        .btn-custom i {
            transition: transform 0.2s;
        }

        .btn-custom:hover i {
            transform: scale(1.1);
        }

        /* ----------------------------------------------------------------------
           MENSAJES (mejorados)
           ---------------------------------------------------------------------- */
        
        .success-message {
            background: linear-gradient(145deg, #eaf7ea, #d4edda);
            border: 1px solid #c3e6cb;
            border-left: 6px solid #28a745;
            color: #155724;
            padding: 1.2rem 1.8rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            font-weight: 500;
            box-shadow: var(--shadow-sm);
            backdrop-filter: blur(2px);
        }

        .success-message i {
            color: #28a745;
            font-size: 1.6rem;
        }

        .error-message {
            background: linear-gradient(145deg, #fbe9eb, #f8d7da);
            border: 1px solid #f5c6cb;
            border-left: 6px solid #dc3545;
            color: #721c24;
            padding: 1.2rem 1.8rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-sm);
        }

        .error-message ul {
            margin: 0.5rem 0 0 1.2rem;
            padding-left: 0;
        }

        .error-message li {
            margin-bottom: 0.4rem;
            font-weight: 500;
        }

        .error-message li:last-child {
            margin-bottom: 0;
        }

        /* ----------------------------------------------------------------------
           RESPONSIVE (optimizado)
           ---------------------------------------------------------------------- */
        
        @media (max-width: 991px) {
            .account-hero h1 {
                font-size: 2rem;
            }
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .account-hero h1 {
                font-size: 1.8rem;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn-custom {
                width: 100%;
            }
            
            .menu-links {
                flex-direction: column;
                gap: 0.8rem;
            }
            
            .menu-link {
                width: 100%;
                justify-content: center;
            }
            
            .card-body-custom {
                padding: 1.8rem;
            }

            .info-grid {
                gap: 1rem;
            }
        }
        
        @media (max-width: 576px) {
            .account-hero h1 {
                font-size: 1.6rem;
                flex-wrap: wrap;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .btn-back {
                width: 100%;
                justify-content: center;
            }
            
            .card-header-custom h2 {
                font-size: 1.3rem;
            }

            .card-header-custom {
                padding: 1.5rem;
            }

            .info-value {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include '../include/header.php'; ?>

    <!-- HERO SECTION MEJORADO -->
    <section class="about-hero">
        <div class="container text-center">
            <h1 class="fadeInUp">Informacion <span>Personal</span></h1>
            <div class="gold-line fadeInUp" style="animation-delay: 0.2s;"></div>
            <p class="fadeInUp" style="animation-delay: 0.4s;">
                Aquí puedes revisar y actualizar tu información personal para mantener tu cuenta al día. 
                Asegúrate de que tus datos estén correctos para una mejor experiencia de compra.
            </p>
        </div>
    </section>

    <main class="section-padding">
        <div class="container">

            <!-- Menú de navegación refinado -->
            <div class="menu-links">
                <a href="<?php echo SITE_URL; ?>/public/mi_cuenta.php" class="menu-link active">
                    <i class="bi bi-person"></i> Mi Información
                </a>
                <a href="<?php echo SITE_URL; ?>/public/compras.php" class="menu-link">
                    <i class="bi bi-bag-check"></i> Mis Compras
                </a>
            </div>

            <!-- Mensaje de éxito con animación sutil -->
            <?php if ($exito): ?>
                <div class="success-message animate__animated animate__fadeIn">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>¡Excelente! Tu información personal ha sido actualizada correctamente.</span>
                </div>
            <?php endif; ?>

            <!-- Mensajes de error con mejor presentación -->
            <?php if (!empty($errores)): ?>
                <div class="error-message animate__animated animate__fadeIn">
                    <i class="bi bi-exclamation-triangle-fill" style="font-size: 1.4rem; margin-right: 0.5rem;"></i>
                    <strong>Por favor, revisa:</strong>
                    <ul>
                        <?php foreach ($errores as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Tarjeta principal con diseño refinado -->
            <div class="account-card">
                <div class="card-header-custom">
                    <h2>
                        <i class="bi bi-pencil-square"></i> Editar información personal
                    </h2>
                </div>

                <div class="card-body-custom">
                    <!-- Badge de información de usuario mejorado -->
                    <div class="info-section">
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label"><i class="bi bi-person-badge"></i> Usuario</span>
                                <span class="info-value">@<?php echo htmlspecialchars($cliente['nombre_usuario']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label"><i class="bi bi-hash"></i> ID Cliente</span>
                                <span class="info-value">#<?php echo str_pad(htmlspecialchars($cliente['id']), 5, '0', STR_PAD_LEFT); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Formulario con microinteracciones -->
                    <form method="POST" action="">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                        <div class="form-row">
                            <div class="form-group-custom">
                                <label for="nombre"><i class="bi bi-person"></i> Nombre</label>
                                <input type="text" id="nombre" name="nombre" 
                                       value="<?php echo htmlspecialchars($cliente['nombre']); ?>" 
                                       placeholder="Ej. Juan"
                                       required>
                            </div>

                            <div class="form-group-custom">
                                <label for="apellido"><i class="bi bi-person"></i> Apellido</label>
                                <input type="text" id="apellido" name="apellido" 
                                       value="<?php echo htmlspecialchars($cliente['apellido']); ?>" 
                                       placeholder="Ej. Pérez"
                                       required>
                            </div>
                        </div>

                        <div class="form-group-custom">
                            <label for="email"><i class="bi bi-envelope"></i> Correo electrónico</label>
                            <input type="email" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($cliente['email']); ?>" 
                                   placeholder="ejemplo@correo.com"
                                   readonly>
                        </div>

                        <div class="form-row">
                            <div class="form-group-custom">
                                <label for="telefono"><i class="bi bi-telephone"></i> Teléfono</label>
                                <input type="tel" id="telefono" name="telefono" 
                                       value="<?php echo htmlspecialchars($cliente['telefono']); ?>" 
                                       placeholder="+54 9 11 1234-5678"
                                       required>
                            </div>

                            <div class="form-group-custom">
                                <label for="documento"><i class="bi bi-card-text"></i> Documento</label>
                                <input type="text" id="documento" name="documento" 
                                       value="<?php echo htmlspecialchars($cliente['documento']); ?>" 
                                       placeholder="DNI / Pasaporte"
                                       readonly>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-custom btn-save">
                                <i class="bi bi-check-lg"></i> Guardar cambios
                            </button>
                            <a href="<?php echo SITE_URL; ?>/public/index.php" class="btn-custom btn-cancel">
                                <i class="bi bi-x-lg"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <?php include '../include/footer.php'; ?>

    <!-- Bootstrap JS y animaciones opcionales (animate.css si se desea) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Opcional: para animaciones más suaves (se puede quitar si no se necesita) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
</body>
</html>