<?php
// ===== INICIO - SIN ESPACIOS ANTES =====
ob_start(); // Buffer de salida para evitar errores de cabeceras

require_once '../include/db.php';
require_once '../include/configuraciones.php';
require_once '../include/cliente.php';

$db = new Database();
$conexion = $db->getConexion();

$error = [];
if (!empty($_POST)) {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $documento = trim($_POST['documento']);
    $usuario = trim($_POST['usuario']);
    $password = trim($_POST['password']);
    $repassword = trim($_POST['repassword']);

    // Validar datos
    if (esNulo([$nombre, $apellido, $email, $telefono, $documento, $usuario, $password, $repassword])) {
        $error[] = "Debe llenar todos los campos";
    }
    if (!validarEmail($email)) {
        $error[] = "Debe ingresar un correo electrónico válido";
    }
    if (!validarPassword($password, $repassword)) {
        $error[] = "Las contraseñas no coinciden";
    }
    if (UsuarioExistente($usuario, $conexion)) {
        $error[] = "El nombre de usuario $usuario ya existe";
    }
    if (EmailExistente($email, $conexion)) {
        $error[] = "El correo electrónico $email ya está registrado";
    }

    if (count($error) == 0) {

        // Registrar cliente
        $id = registrarCliente([$nombre, $apellido, $email, $telefono, $documento], $conexion);

        if ($id > 0) {
            require_once '../include/mailer.php';
            $mailer = new Mailer();
            $token = generarToken();
            $pass_hash = password_hash($password, PASSWORD_DEFAULT);
            $idUsuario = registrarUsuario([$usuario, $pass_hash, $token, $id], $conexion);

            if ($idUsuario > 0) {
                $url = SITE_URL . '/public/activa_cliente.php?id=' . $idUsuario . '&token=' . $token;
                $asunto = "Activa tu cuenta - Tienda Virtual";

                // Cuerpo HTML mejorado
                $cuerpo = "
                <div style='font-family: Arial, sans-serif; background-color:#f8f9fa; padding:20px;'>
                    <div style='max-width:600px; margin:auto; background-color:white; border-radius:10px; overflow:hidden; box-shadow:0 0 10px rgba(0,0,0,0.1);'>
                        <div style='background-color:#000000; color:#ffd700; padding:15px 20px; text-align:center;'>
                            <h2 style='margin:0;'>Tienda Virtual</h2>
                        </div>
                        <div style='padding:20px;'>
                            <p>Hola <strong>$nombre</strong>,</p>
                            <p>¡Gracias por registrarte en nuestra tienda! 🛒</p>
                            <p>Para activar tu cuenta, haz clic en el siguiente botón:</p>
                            <p style='text-align:center; margin:30px 0;'>
                                <a href='$url' style='background-color:#000000; color:#ffd700; text-decoration:none; padding:12px 20px; border-radius:5px; border:1px solid #ffd700;'>Activar cuenta</a>
                            </p>
                            <p>O copia y pega este enlace en tu navegador:</p>
                            <p style='word-break:break-all;'>$url</p>
                            <hr>
                            <p style='font-size:12px; color:#6c757d; text-align:center;'>
                                Si no solicitaste esta cuenta, simplemente ignora este mensaje.
                            </p>
                        </div>
                    </div>
                </div>";
                
                if ($mailer->enviarEmail($email, $asunto, $cuerpo)) {
                    echo "<script>alert('Usuario registrado correctamente. Se ha enviado un correo de activación a $email'); window.location='login.php';</script>";
                    exit();
                } else {
                    echo "<script>alert('No se pudo enviar el correo de activación.');</script>";
                }
            } else {
                $error[] = "No se pudo registrar el usuario";
            }
        } else {
            $error[] = "No se pudo registrar el cliente";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es" data-bs-theme="auto">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear Cuenta - Tienda Online</title>
    <?php include '../include/links.php'; ?>
    <meta name="theme-color" content="#000000">
    <style>
        /* ===== USANDO TUS COLORES PERSONALIZADOS ===== */
        :root {
            --primary-black: #000000;
            --primary-gold: #ffd700;
            --text-light: #ffffff;
            --text-dark: #333333;
            --gray-light: #f8f9fa;
            --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        /* Estilos base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, var(--gray-light) 0%, #ffffff 100%);
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            min-height: 100vh;
        }

        /* Contenedor principal */
        .register-container {
            min-height: calc(100vh - 140px);
            padding: 3rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Tarjeta de registro estilo premium */
        .register-card {
            width: 100%;
            max-width: 900px;
            background: white;
            border-radius: 28px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            transition: var(--transition);
            border: 1px solid rgba(0, 0, 0, 0.05);
            position: relative;
            animation: fadeInUp 0.5s ease;
        }

        /* Detalle dorado superior */
        .register-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gold);
            z-index: 2;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Header */
        .register-header {
            padding: 2rem 2rem 1.5rem 2rem;
            text-align: center;
            background: white;
            border-bottom: 1px solid #e5e7eb;
        }

        .register-header h2 {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: var(--primary-black);
            margin-bottom: 0.75rem;
        }

        .register-header p {
            color: var(--text-dark);
            font-size: 0.95rem;
            opacity: 0.7;
        }

        /* Cuerpo del formulario */
        .register-body {
            padding: 2rem;
        }

        /* Secciones del formulario */
        .form-section {
            margin-bottom: 2rem;
        }

        .form-section-title {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--primary-black);
            margin-bottom: 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-section-title i {
            color: var(--primary-gold);
            font-size: 1.2rem;
        }

        /* Grupos de formulario */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--primary-black);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .required {
            color: var(--primary-gold);
            font-weight: 700;
            font-size: 1rem;
        }

        /* Input groups */
        .input-group-custom {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            color: #999;
            font-size: 1.1rem;
            transition: var(--transition);
            pointer-events: none;
            z-index: 1;
        }

        .form-control-custom {
            width: 100%;
            padding: 0.85rem 1rem 0.85rem 2.8rem;
            font-size: 0.95rem;
            border: 2px solid #e5e7eb;
            border-radius: 16px;
            background-color: #fff;
            transition: var(--transition);
            font-weight: 500;
            color: var(--text-dark);
        }

        .form-control-custom:focus {
            outline: none;
            border-color: var(--primary-gold);
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.1);
        }

        .form-control-custom:hover:not(:focus) {
            border-color: #d1d5db;
        }

        .form-control-custom.is-invalid {
            border-color: #dc2626;
            background-color: #fef2f2;
        }

        .form-control-custom.is-valid {
            border-color: #10b981;
            background-color: #f0fdf4;
        }

        /* Mensajes de error */
        .error-message {
            color: #dc2626;
            font-size: 0.75rem;
            margin-top: 0.5rem;
            display: none;
            align-items: center;
            gap: 0.25rem;
        }

        .error-message.show {
            display: flex;
        }

        .error-message i {
            font-size: 0.8rem;
        }

        /* Botón principal */
        .btn-register {
            width: 100%;
            background: var(--primary-black);
            border: none;
            padding: 1rem;
            font-weight: 700;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--primary-gold);
            border-radius: 50px;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            margin-top: 1rem;
        }

        .btn-register::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 215, 0, 0.1);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn-register:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-register i {
            font-size: 1.2rem;
            transition: transform 0.2s;
            position: relative;
            z-index: 1;
        }

        .btn-register span {
            position: relative;
            z-index: 1;
        }

        .btn-register:hover {
            background: var(--primary-gold);
            color: var(--primary-black);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .btn-register:hover i {
            transform: translateX(4px);
        }

        .btn-register:active {
            transform: translateY(0);
        }

        .btn-register.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        .btn-register.loading::after {
            content: "";
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            right: 20px;
            transform: translateY(-50%);
            border: 2px solid var(--primary-gold);
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to {
                transform: translateY(-50%) rotate(360deg);
            }
        }

        /* Footer */
        .register-footer {
            padding: 1.5rem 2rem;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            background: var(--gray-light);
        }

        .register-footer p {
            margin: 0;
            color: var(--text-dark);
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .register-footer a {
            color: var(--primary-black);
            font-weight: 700;
            text-decoration: none;
            margin-left: 6px;
            transition: var(--transition);
            position: relative;
        }

        .register-footer a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary-gold);
            transition: width 0.3s ease;
        }

        .register-footer a:hover {
            color: var(--primary-gold);
        }

        .register-footer a:hover::after {
            width: 100%;
        }

        /* Alertas personalizadas */
        .alert-custom {
            margin: 0 2rem 1rem 2rem;
            padding: 1rem 1.2rem;
            border-radius: 16px;
            font-size: 0.9rem;
            border-left: 4px solid;
            animation: slideDown 0.3s ease;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .alert-danger-custom {
            background: #fee2e2;
            border-left-color: #dc2626;
            color: #991b1b;
        }

        .alert-success-custom {
            background: #d1fae5;
            border-left-color: #10b981;
            color: #065f46;
        }

        .alert-custom i {
            font-size: 1.2rem;
            margin-top: 2px;
        }

        .alert-custom .alert-content {
            flex: 1;
        }

        .alert-custom ul {
            padding-left: 1.2rem;
            margin-top: 0.5rem;
            margin-bottom: 0;
        }

        .alert-custom li {
            margin-bottom: 0.2rem;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Toast notifications */
        .toast-notify {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 9999;
            background: var(--primary-black);
            color: var(--primary-gold);
            border-radius: 12px;
            padding: 12px 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: var(--shadow-lg);
            border-left: 4px solid var(--primary-gold);
            animation: slideInRight 0.3s ease;
        }

        .toast-notify.success {
            background: #10b981;
            color: white;
            border-left-color: #059669;
        }

        .toast-notify.error {
            background: #ef4444;
            color: white;
            border-left-color: #dc2626;
        }

        .toast-notify.warning {
            background: #f59e0b;
            color: white;
            border-left-color: #d97706;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .toast-remove {
            animation: fadeOut 0.3s forwards;
        }

        @keyframes fadeOut {
            to {
                opacity: 0;
                transform: translateX(100px);
            }
        }

        /* Help text */
        .help-text {
            font-size: 0.7rem;
            color: #6c757d;
            margin-top: 0.5rem;
            display: block;
        }

        /* Input wrapper focus */
        .input-group-custom:focus-within .input-icon {
            color: var(--primary-gold);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .register-container {
                padding: 2rem 1rem;
            }
            .register-card {
                max-width: 100%;
                border-radius: 24px;
            }
            .register-header {
                padding: 1.5rem;
            }
            .register-header h2 {
                font-size: 1.75rem;
            }
            .register-body {
                padding: 1.5rem;
            }
            .form-control-custom {
                padding: 0.75rem 1rem 0.75rem 2.6rem;
                font-size: 0.9rem;
            }
            .btn-register {
                padding: 0.85rem;
                font-size: 0.9rem;
            }
            .alert-custom {
                margin: 0 1rem 1rem;
            }
            .toast-notify {
                top: 16px;
                right: 16px;
                left: 16px;
            }
        }

        /* Grid responsive */
        .row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -0.75rem;
        }

        .col-md-6 {
            flex: 0 0 50%;
            max-width: 50%;
            padding: 0 0.75rem;
        }

        @media (max-width: 576px) {
            .col-md-6 {
                flex: 0 0 100%;
                max-width: 100%;
            }
        }
    </style>
</head>

<body>
    <?php include '../include/header.php'; ?>

    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <h2>Crear Cuenta</h2>
                <p>Completa el formulario para registrarte en nuestra tienda</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert-custom alert-danger-custom">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div class="alert-content">
                        <strong>Error de validación</strong>
                        <ul class="mb-0 mt-1">
                            <?php foreach ($error as $err): ?>
                                <li><?php echo htmlspecialchars($err); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <div class="register-body">
                <form action="register.php" method="post" id="registerForm" novalidate>
                    <!-- Sección de Datos Personales -->
                    <div class="form-section">
                        <h4 class="form-section-title">
                            <i class="bi bi-person-badge"></i> Datos Personales
                        </h4>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        <span class="required">*</span> Nombre
                                    </label>
                                    <div class="input-group-custom">
                                        <i class="bi bi-person input-icon"></i>
                                        <input type="text" class="form-control-custom" id="nombre" name="nombre" 
                                               placeholder="Tu nombre" 
                                               value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>"
                                               required>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        <span class="required">*</span> Apellidos
                                    </label>
                                    <div class="input-group-custom">
                                        <i class="bi bi-person-badge input-icon"></i>
                                        <input type="text" class="form-control-custom" id="apellido" name="apellido" 
                                               placeholder="Tus apellidos"
                                               value="<?php echo isset($_POST['apellido']) ? htmlspecialchars($_POST['apellido']) : ''; ?>"
                                               required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        <span class="required">*</span> Correo Electrónico
                                    </label>
                                    <div class="input-group-custom">
                                        <i class="bi bi-envelope input-icon"></i>
                                        <input type="email" class="form-control-custom" id="email" name="email" 
                                               placeholder="correo@ejemplo.com"
                                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                               required>
                                    </div>
                                    <div id="email-error" class="error-message">
                                        <i class="bi bi-exclamation-circle"></i> <span></span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        <span class="required">*</span> Teléfono
                                    </label>
                                    <div class="input-group-custom">
                                        <i class="bi bi-telephone input-icon"></i>
                                        <input type="tel" class="form-control-custom" id="telefono" name="telefono" 
                                               placeholder="Teléfono"
                                               value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>"
                                               required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <span class="required">*</span> Documento de Identidad
                            </label>
                            <div class="input-group-custom">
                                <i class="bi bi-card-text input-icon"></i>
                                <input type="text" class="form-control-custom" id="documento" name="documento" 
                                       placeholder="Número de documento"
                                       value="<?php echo isset($_POST['documento']) ? htmlspecialchars($_POST['documento']) : ''; ?>"
                                       required>
                            </div>
                        </div>
                    </div>

                    <!-- Sección de Credenciales -->
                    <div class="form-section">
                        <h4 class="form-section-title">
                            <i class="bi bi-lock"></i> Credenciales de Acceso
                        </h4>
                        
                        <div class="form-group">
                            <label class="form-label">
                                <span class="required">*</span> Nombre de Usuario
                            </label>
                            <div class="input-group-custom">
                                <i class="bi bi-person-circle input-icon"></i>
                                <input type="text" class="form-control-custom" id="usuario" name="usuario" 
                                       placeholder="Usuario"
                                       value="<?php echo isset($_POST['usuario']) ? htmlspecialchars($_POST['usuario']) : ''; ?>"
                                       required>
                            </div>
                            <div id="usuario-error" class="error-message">
                                <i class="bi bi-exclamation-circle"></i> <span></span>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        <span class="required">*</span> Contraseña
                                    </label>
                                    <div class="input-group-custom">
                                        <i class="bi bi-lock input-icon"></i>
                                        <input type="password" class="form-control-custom" id="password" name="password" 
                                               placeholder="Tu contraseña segura" required>
                                    </div>
                                    <small class="help-text">Mínimo 6 caracteres</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        <span class="required">*</span> Confirmar Contraseña
                                    </label>
                                    <div class="input-group-custom">
                                        <i class="bi bi-lock-fill input-icon"></i>
                                        <input type="password" class="form-control-custom" id="repassword" name="repassword" 
                                               placeholder="Repite tu contraseña" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-register" id="submitBtn">
                        <i class="bi bi-check-circle"></i>
                        <span>Crear Cuenta</span>
                    </button>
                </form>
            </div>

            <div class="register-footer">
                <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a></p>
            </div>
        </div>
    </div>

    <?php include '../include/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    // Sistema de notificaciones toast
    const ToastManager = {
        show: function(message, type = 'info') {
            const existingToasts = document.querySelectorAll('.toast-notify');
            existingToasts.forEach(toast => toast.remove());
            
            const toast = document.createElement('div');
            toast.className = `toast-notify ${type}`;
            const icon = type === 'success' ? '✓' : (type === 'error' ? '✗' : (type === 'warning' ? '⚠' : 'ℹ'));
            toast.innerHTML = `<strong style="font-size:1.2rem;">${icon}</strong> <span>${message}</span>`;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.add('toast-remove');
                setTimeout(() => toast.remove(), 300);
            }, 3500);
        },
        error: function(msg) { this.show(msg, 'error'); },
        success: function(msg) { this.show(msg, 'success'); },
        warning: function(msg) { this.show(msg, 'warning'); },
        info: function(msg) { this.show(msg, 'info'); }
    };

    // Validar usuario en tiempo real
    const txtUsuario = document.getElementById("usuario");
    if (txtUsuario) {
        txtUsuario.addEventListener("blur", function() {
            if (this.value.trim().length >= 3) {
                existeUsuario(this.value);
            }
        });
    }

    // Validar email en tiempo real
    const txtEmail = document.getElementById("email");
    if (txtEmail) {
        txtEmail.addEventListener("blur", function() {
            if (this.value.trim().length > 0) {
                // Validar formato básico
                const emailRegex = /^[^\s@]+@([^\s@]+\.)+[^\s@]+$/;
                if (!emailRegex.test(this.value)) {
                    const errorDiv = document.getElementById("email-error");
                    errorDiv.querySelector('span').innerText = "Formato de correo inválido";
                    errorDiv.classList.add("show");
                    this.classList.add('is-invalid');
                    return;
                }
                existeEmail(this.value);
            }
        });
    }

    // Validar coincidencia de contraseñas
    const txtRePassword = document.getElementById("repassword");
    const txtPassword = document.getElementById("password");
    
    if (txtRePassword && txtPassword) {
        txtRePassword.addEventListener("blur", function() {
            const pass1 = txtPassword.value;
            const pass2 = this.value;
            
            if (pass2.trim().length > 0) {
                if (pass1 !== pass2) {
                    ToastManager.error('Las contraseñas no coinciden');
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                } else {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                }
            }
        });
        
        txtPassword.addEventListener("blur", function() {
            if (this.value.length > 0 && this.value.length < 6) {
                ToastManager.warning('La contraseña debe tener al menos 6 caracteres');
                this.classList.add('is-invalid');
            } else if (this.value.length >= 6) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
    }

    // Verificar si el usuario ya existe
    function existeUsuario(usuario) {
        let url = "../include/clienteAjax.php";
        let formData = new FormData();
        formData.append("action", "existeUsuario");
        formData.append("usuario", usuario);

        fetch(url, {
            method: 'POST',
            body: formData,
        }).then(response => response.json())
            .then(data => {
                const errorDiv = document.getElementById("usuario-error");
                const usuarioInput = document.getElementById("usuario");
                if (data.ok) {
                    usuarioInput.value = "";
                    errorDiv.querySelector('span').innerText = "Este usuario no está disponible";
                    errorDiv.classList.add("show");
                    usuarioInput.classList.add('is-invalid');
                    usuarioInput.classList.remove('is-valid');
                    ToastManager.warning('Usuario no disponible');
                } else {
                    errorDiv.classList.remove("show");
                    usuarioInput.classList.remove('is-invalid');
                    usuarioInput.classList.add('is-valid');
                }
            })
            .catch(error => console.error('Error:', error));
    }

    // Verificar si el email ya existe
    function existeEmail(email) {
        let url = "../include/clienteAjax.php";
        let formData = new FormData();
        formData.append("action", "existeEmail");
        formData.append("email", email);

        fetch(url, {
            method: 'POST',
            body: formData,
        }).then(response => response.json())
            .then(data => {
                const errorDiv = document.getElementById("email-error");
                const emailInput = document.getElementById("email");
                if (data.ok) {
                    emailInput.value = "";
                    errorDiv.querySelector('span').innerText = "Este correo ya está registrado";
                    errorDiv.classList.add("show");
                    emailInput.classList.add('is-invalid');
                    emailInput.classList.remove('is-valid');
                    ToastManager.warning('Email ya registrado');
                } else {
                    errorDiv.classList.remove("show");
                    emailInput.classList.remove('is-invalid');
                    emailInput.classList.add('is-valid');
                }
            })
            .catch(error => console.error('Error:', error));
    }

    // Validar formulario completo antes de enviar
    const registerForm = document.getElementById("registerForm");
    const submitBtn = document.getElementById("submitBtn");

    if (registerForm) {
        registerForm.addEventListener("submit", function(e) {
            // Obtener valores
            const nombre = document.getElementById("nombre")?.value.trim() || '';
            const apellido = document.getElementById("apellido")?.value.trim() || '';
            const email = document.getElementById("email")?.value.trim() || '';
            const telefono = document.getElementById("telefono")?.value.trim() || '';
            const documento = document.getElementById("documento")?.value.trim() || '';
            const usuario = document.getElementById("usuario")?.value.trim() || '';
            const password = document.getElementById("password")?.value;
            const repassword = document.getElementById("repassword")?.value;

            // Validaciones básicas
            if (!nombre || !apellido || !email || !telefono || !documento || !usuario || !password || !repassword) {
                e.preventDefault();
                ToastManager.error('Por favor completa todos los campos');
                return false;
            }

            // Validar formato de email
            const emailRegex = /^[^\s@]+@([^\s@]+\.)+[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                ToastManager.error('Formato de correo electrónico inválido');
                return false;
            }

            // Validar que las contraseñas coincidan
            if (password !== repassword) {
                e.preventDefault();
                ToastManager.error('Las contraseñas no coinciden');
                return false;
            }

            // Validar longitud de contraseña
            if (password.length < 6) {
                e.preventDefault();
                ToastManager.error('La contraseña debe tener al menos 6 caracteres');
                return false;
            }

            // Mostrar estado de carga
            if (submitBtn) {
                submitBtn.classList.add('loading');
                submitBtn.querySelector('span').textContent = 'Procesando...';
            }
            
            return true;
        });
    }

    // Mostrar errores del servidor como toast también
    window.addEventListener('DOMContentLoaded', function() {
        const alertBox = document.querySelector('.alert-custom');
        if (alertBox && alertBox.classList.contains('alert-danger-custom')) {
            const errorText = alertBox.innerText.trim();
            if (errorText) {
                setTimeout(() => {
                    ToastManager.error(errorText.split('\n')[0].substring(0, 100));
                }, 500);
            }
        }
    });
    </script>
</body>
</html>

<?php
ob_end_flush(); // Enviar el buffer al navegador
?>