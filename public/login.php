<?php
// ===== INICIO - SIN ESPACIOS ANTES =====
ob_start(); // Buffer de salida para evitar errores de cabeceras

require_once '../include/db.php';
require_once '../include/configuraciones.php';
require_once '../include/cliente.php';

$db = new Database();
$conexion = $db->getConexion();

$proceso = isset($_GET['pago']) ? 'pago' : 'login';
$error = [];

if (!empty($_POST)) {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $proceso = $_POST['proceso'] ?? 'login';

    if (esNulo([$usuario, $password])) {
        $error[] = "Debe llenar todos los campos.";
    } else {
        $loginMsg = Login($usuario, $password, $conexion, $proceso);

        if ($loginMsg !== true) {
            $error[] = $loginMsg;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es" data-bs-theme="auto">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar Sesión - Tienda Online</title>
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
        .login-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: calc(100vh - 140px);
            padding: 2rem 1.5rem;
        }

        /* Tarjeta de login estilo premium */
        .login-card {
            width: 100%;
            max-width: 480px;
            background: white;
            border-radius: 24px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            transition: var(--transition);
            border: 1px solid rgba(0, 0, 0, 0.05);
            position: relative;
        }

        /* Detalle dorado superior */
        .login-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gold);
            z-index: 2;
        }

        /* Header */
        .login-header {
            padding: 2.5rem 2rem 1.5rem 2rem;
            text-align: center;
            background: white;
        }

        .login-header h2 {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: var(--primary-black);
            margin-bottom: 0.75rem;
        }

        .login-header p {
            color: var(--text-dark);
            font-size: 0.95rem;
            opacity: 0.7;
        }

        /* Cuerpo del formulario */
        .login-body {
            padding: 0 2rem 1.5rem 2rem;
        }

        /* Grupos de formulario */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--primary-black);
            margin-bottom: 0.5rem;
            display: block;
        }

        /* Wrapper para inputs con iconos */
        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            color: #999;
            font-size: 1.2rem;
            transition: var(--transition);
            pointer-events: none;
            z-index: 1;
        }

        /* Inputs personalizados */
        .form-control-custom {
            width: 100%;
            padding: 0.9rem 1rem 0.9rem 2.8rem;
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

        /* Enlace olvidó contraseña */
        .forgot-link {
            text-align: right;
            margin-bottom: 1.5rem;
        }

        .forgot-link a {
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--text-dark);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: var(--transition);
            opacity: 0.7;
        }

        .forgot-link a i {
            font-size: 0.85rem;
        }

        .forgot-link a:hover {
            color: var(--primary-gold);
            opacity: 1;
        }

        /* Botón principal - Estilo dorado/negro */
        .btn-login {
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
        }

        .btn-login::before {
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

        .btn-login:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-login i {
            font-size: 1.2rem;
            transition: transform 0.2s;
            position: relative;
            z-index: 1;
        }

        .btn-login span {
            position: relative;
            z-index: 1;
        }

        .btn-login:hover {
            background: var(--primary-gold);
            color: var(--primary-black);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .btn-login:hover i {
            transform: translateX(4px);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        /* Footer */
        .login-footer {
            padding: 1.5rem 2rem 2rem;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            background: var(--gray-light);
        }

        .login-footer p {
            margin: 0;
            color: var(--text-dark);
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .login-footer a {
            color: var(--primary-black);
            font-weight: 700;
            text-decoration: none;
            margin-left: 6px;
            transition: var(--transition);
            position: relative;
        }

        .login-footer a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary-gold);
            transition: width 0.3s ease;
        }

        .login-footer a:hover {
            color: var(--primary-gold);
        }

        .login-footer a:hover::after {
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

        /* Estado de carga del botón */
        .btn-login.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        .btn-login.loading::after {
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

        /* Mejoras en inputs */
        .input-wrapper:focus-within .input-icon {
            color: var(--primary-gold);
        }

        /* Campo con error */
        .field-error {
            border-color: #dc2626 !important;
            background-color: #fef2f2 !important;
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

        /* Responsive */
        @media (max-width: 576px) {
            .login-container {
                padding: 1rem;
            }
            .login-card {
                border-radius: 20px;
            }
            .login-header {
                padding: 2rem 1.5rem 1rem;
            }
            .login-header h2 {
                font-size: 1.75rem;
            }
            .login-body {
                padding: 0 1.5rem 1.5rem;
            }
            .form-control-custom {
                padding: 0.8rem 1rem 0.8rem 2.6rem;
                font-size: 0.9rem;
            }
            .btn-login {
                padding: 0.85rem;
                font-size: 0.9rem;
            }
            .alert-custom {
                margin: 0 1.2rem 1rem;
            }
            .toast-notify {
                top: 16px;
                right: 16px;
                left: 16px;
                padding: 10px 16px;
            }
        }

        /* Animación de entrada para la tarjeta */
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

        .login-card {
            animation: fadeInUp 0.5s ease;
        }
    </style>
</head>
<body>

<?php include '../include/header.php'; ?>

<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <h2>Bienvenido</h2>
            <p>Accede a tu cuenta y descubre lo mejor en estilo</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert-custom alert-danger-custom">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div class="alert-content">
                    <strong>Error de autenticación</strong>
                    <ul class="mb-0 mt-1">
                        <?php foreach ($error as $err): ?>
                            <li><?php echo htmlspecialchars($err); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <div class="login-body">
            <form action="login.php" method="POST" autocomplete="off" id="loginForm" novalidate>
                <input type="hidden" name="proceso" value="<?php echo htmlspecialchars($proceso); ?>">
                
                <div class="form-group">
                    <label class="form-label" for="usuario">USUARIO O CORREO</label>
                    <div class="input-wrapper">
                        <i class="bi bi-person-circle input-icon"></i>
                        <input type="text" 
                               class="form-control-custom" 
                               id="usuario" 
                               name="usuario" 
                               placeholder="ejemplo@correo.com / usuario"
                               value="<?php echo isset($_POST['usuario']) ? htmlspecialchars($_POST['usuario']) : ''; ?>"
                               autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">CONTRASEÑA</label>
                    <div class="input-wrapper">
                        <i class="bi bi-shield-lock input-icon"></i>
                        <input type="password" 
                               class="form-control-custom" 
                               id="password" 
                               name="password" 
                               placeholder="••••••••">
                    </div>
                </div>

                <div class="forgot-link">
                    <a href="../include/recuperar.php">
                        <i class="bi bi-key"></i> ¿Olvidaste tu contraseña?
                    </a>
                </div>

                <button type="submit" class="btn-login" id="submitBtn">
                    <i class="bi bi-box-arrow-in-right"></i>
                    <span>Ingresar</span>
                </button>
            </form>
        </div>

        <div class="login-footer">
            <p>¿No tienes cuenta? <a href="register.php">Regístrate aquí</a></p>
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
        const icon = type === 'success' ? '✓' : (type === 'error' ? '✗' : 'ℹ');
        toast.innerHTML = `<strong style="font-size:1.2rem;">${icon}</strong> <span>${message}</span>`;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('toast-remove');
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    },
    error: function(msg) { this.show(msg, 'error'); },
    success: function(msg) { this.show(msg, 'success'); },
    info: function(msg) { this.show(msg, 'info'); }
};

// Validación del formulario
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    const submitBtn = document.getElementById('submitBtn');
    const usuarioInput = document.getElementById('usuario');
    const passwordInput = document.getElementById('password');

    const clearFieldError = (input) => {
        input.classList.remove('field-error');
    };

    if (usuarioInput) {
        usuarioInput.addEventListener('input', () => clearFieldError(usuarioInput));
    }
    if (passwordInput) {
        passwordInput.addEventListener('input', () => clearFieldError(passwordInput));
    }

    if (form) {
        form.addEventListener('submit', function(e) {
            const usuario = usuarioInput ? usuarioInput.value.trim() : '';
            const password = passwordInput ? passwordInput.value.trim() : '';
            let hasError = false;
            
            if (usuarioInput) usuarioInput.classList.remove('field-error');
            if (passwordInput) passwordInput.classList.remove('field-error');
            
            if (!usuario) {
                if (usuarioInput) usuarioInput.classList.add('field-error');
                hasError = true;
            }
            if (!password) {
                if (passwordInput) passwordInput.classList.add('field-error');
                hasError = true;
            }
            
            if (hasError) {
                e.preventDefault();
                ToastManager.error('Completa todos los campos');
                return false;
            }
            
            if (submitBtn) {
                submitBtn.classList.add('loading');
                submitBtn.querySelector('span').textContent = 'Verificando...';
            }
            return true;
        });
    }
});

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
    
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('registered') === '1') {
        ToastManager.success('¡Cuenta creada exitosamente! Inicia sesión');
    }
});
</script>

<?php
ob_end_flush(); // Enviar el buffer al navegador
?>