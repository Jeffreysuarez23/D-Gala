<?php
// ===== INICIO - SIN ESPACIOS ANTES =====
ob_start(); // Buffer de salida para evitar errores de cabeceras

require_once '../include/db.php';
require_once '../include/configuraciones.php';
require_once '../include/cliente.php';
require_once '../include/validaciones.php';

$db = new Database();
$conexion = $db->getConexion();

$error = [];
if (!empty($_POST)) {
    $email = ValidadorFormularios::limpiar($_POST['email']);

    // Validar datos
    if (esNulo([$email])) {
        $error[] = "❌ El correo es requerido";
    } else {
        // Validar formato email
        $val_email = ValidadorFormularios::validar($email, 'email');
        if ($val_email !== true) {
            $error[] = $val_email['mensaje'];
        }
    }
    
    if (count($error) == 0) {
        if(EmailExistente($email, $conexion)){
          $sql = $conexion->prepare("SELECT usuarios.id, clientes.nombre FROM usuarios 
            INNER JOIN clientes ON usuarios.id_cliente = clientes.id 
            WHERE clientes.email LIKE ? LIMIT 1");

              $sql->execute([$email]);
              $row = $sql->fetch(PDO::FETCH_ASSOC);
              $user_id = $row['id'];
              $user_nombre = $row['nombre'];
              $token = solicitaPassword($user_id, $conexion);

              if($token != null){
                  require_once '../include/mailer.php';
                  $mailer = new Mailer();
                  $url = SITE_URL . '/include/reset_password.php?id=' . $user_id . '&token=' . $token;
                  $asunto = "Recuperar contraseña - Tienda Online";
                  $cuerpo = "
                    <table style='width:100%; max-width:600px; margin:auto; background:#ffffff; border-radius:10px; 
                    box-shadow:0 4px 20px rgba(0,0,0,0.1); padding:30px; font-family:Arial, sans-serif; color:#333;'>
                        <tr>
                          <td style='text-align:center;'>
                              <div style='width:80px; height:80px; background:#000000; border-radius:50%;
                              display:flex; align-items:center; justify-content:center; margin:auto;'>
                                  <img src='https://cdn-icons-png.flaticon.com/512/2889/2889676.png'
                                  style='display:block; margin:auto; max-width:45px; height:auto; object-fit:contain;'>
                              </div>
                              <h2 style='color:#ffd700; margin-top:15px;'>
                                  🔒 Recuperar Contraseña
                              </h2>
                          </td>
                        </tr>
                        <tr>
                            <td style='font-size:16px; padding:15px; line-height:1.6;'>
                                Hola <strong>$user_nombre</strong>,<br><br>
                                Hemos recibido una solicitud para restablecer la contraseña de tu cuenta.
                                Si fuiste tú, continúa con el proceso haciendo clic en el siguiente botón:
                            </td>
                        </tr>
                        <tr>
                            <td style='text-align:center; padding:20px;'>
                                <a href='$url' 
                                style='background:#000000; color:#ffd700; padding:14px 28px; text-decoration:none; 
                                border-radius:6px; font-size:16px; display:inline-block; border:1px solid #ffd700;'>
                                ✅ Restablecer Contraseña
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td style='font-size:15px; padding:15px; color:#555;'>
                                También puedes copiar y pegar este enlace en tu navegador:<br>
                                <span style='color:#ffd700;'>$url</span>
                            </td>
                        </tr>
                        <tr>
                            <td style='font-size:14px; padding:15px; color:#777;'>
                                ⏱️ <strong>Nota:</strong> Este enlace expira en 1 hora por seguridad.
                            </td>
                        </tr>
                        <tr>
                            <td style='font-size:14px; padding:15px; color:#777;'>
                                ❌ Si no solicitaste un cambio de contraseña, simplemente ignora este mensaje.
                            </td>
                        </tr>
                        <tr>
                            <td style='text-align:center; padding-top:20px; font-size:14px; color:#aaa;'>
                                D'gala © " . date('Y') . "
                            </td>
                        </tr>
                    </table>
                    ";

                  if ($mailer->EnviarEmail($email, $asunto, $cuerpo)) {
                    echo "<script>alert('✅ Correo de recuperación enviado a $email'); window.location.href = '../public/login.php';</script>";
                    exit;
                  }
              }
            echo "<script>alert('✅ Se ha enviado un correo de recuperación a $email');</script>";
        } else {
            $error[] = "❌ El correo electrónico no está registrado en el sistema.";
        }
    }

}
?>

<!DOCTYPE html>
<html lang="es" data-bs-theme="auto">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recuperar Contraseña - Tienda Online</title>
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
        .recover-container {
            min-height: calc(100vh - 140px);
            padding: 3rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Tarjeta de recuperación estilo premium */
        .recover-card {
            width: 100%;
            max-width: 520px;
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
        .recover-card::before {
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
        .recover-header {
            padding: 2rem 2rem 1rem 2rem;
            text-align: center;
            background: white;
        }

        .recover-header h2 {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: var(--primary-black);
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .recover-header h2 i {
            color: var(--primary-gold);
            font-size: 2rem;
        }

        .recover-header p {
            color: var(--text-dark);
            font-size: 0.95rem;
            opacity: 0.7;
            margin-top: 0.5rem;
        }

        /* Cuerpo del formulario */
        .recover-body {
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
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-label i {
            color: var(--primary-gold);
            font-size: 1rem;
        }

        /* Input wrapper con icono */
        .input-wrapper {
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

        .input-wrapper:focus-within .input-icon {
            color: var(--primary-gold);
        }

        /* Texto de ayuda */
        .help-text {
            font-size: 0.7rem;
            color: #6c757d;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .help-text i {
            font-size: 0.7rem;
        }

        /* Botón principal */
        .btn-recover {
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
            margin-top: 0.5rem;
        }

        .btn-recover::before {
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

        .btn-recover:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-recover i {
            font-size: 1.2rem;
            transition: transform 0.2s;
            position: relative;
            z-index: 1;
        }

        .btn-recover span {
            position: relative;
            z-index: 1;
        }

        .btn-recover:hover {
            background: var(--primary-gold);
            color: var(--primary-black);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .btn-recover:hover i {
            transform: translateX(4px);
        }

        .btn-recover:active {
            transform: translateY(0);
        }

        .btn-recover.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        .btn-recover.loading::after {
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
        .recover-footer {
            padding: 1.5rem 2rem;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            background: var(--gray-light);
        }

        .recover-footer p {
            margin: 0;
            color: var(--text-dark);
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .recover-footer a {
            color: var(--primary-black);
            font-weight: 700;
            text-decoration: none;
            transition: var(--transition);
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .recover-footer a i {
            font-size: 0.85rem;
        }

        .recover-footer a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary-gold);
            transition: width 0.3s ease;
        }

        .recover-footer a:hover {
            color: var(--primary-gold);
        }

        .recover-footer a:hover::after {
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

        .toast-notify.info {
            background: #3b82f6;
            color: white;
            border-left-color: #2563eb;
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

        /* Animación de entrada para el icono */
        .icon-animation {
            animation: bounce 0.5s ease;
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-5px);
            }
        }

        /* Responsive */
        @media (max-width: 576px) {
            .recover-container {
                padding: 2rem 1rem;
            }
            .recover-card {
                border-radius: 24px;
            }
            .recover-header {
                padding: 1.5rem;
            }
            .recover-header h2 {
                font-size: 1.5rem;
            }
            .recover-header h2 i {
                font-size: 1.5rem;
            }
            .recover-body {
                padding: 0 1.5rem 1.5rem;
            }
            .form-control-custom {
                padding: 0.75rem 1rem 0.75rem 2.6rem;
                font-size: 0.9rem;
            }
            .btn-recover {
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

        /* Modo oscuro para inputs */
        .form-control-custom:-webkit-autofill,
        .form-control-custom:-webkit-autofill:hover,
        .form-control-custom:-webkit-autofill:focus {
            -webkit-box-shadow: 0 0 0px 1000px white inset;
            transition: background-color 5000s ease-in-out 0s;
        }
    </style>
</head>

<body>
    <?php include '../include/header.php'; ?>

    <div class="recover-container">
        <div class="recover-card">
            <div class="recover-header">
                <h2>
                    <i class="bi bi-shield-lock"></i>
                    Recuperar Contraseña
                </h2>
                <p>Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert-custom alert-danger-custom">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div class="alert-content">
                        <strong>Error de recuperación</strong>
                        <ul class="mb-0 mt-1">
                            <?php foreach ($error as $err): ?>
                                <li><?php echo htmlspecialchars($err); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <div class="recover-body">
                <form action="recuperar.php" method="POST" autocomplete="off" id="recuperarForm" novalidate>
                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-envelope"></i> Correo Electrónico *
                        </label>
                        <div class="input-wrapper">
                            <i class="bi bi-envelope-fill input-icon"></i>
                            <input type="email" 
                                   class="form-control-custom" 
                                   id="email" 
                                   name="email" 
                                   placeholder="correo@ejemplo.com"
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                   required>
                        </div>
                        <div class="help-text">
                            <i class="bi bi-info-circle"></i> Ingresa el correo con el que te registraste
                        </div>
                    </div>

                    <button type="submit" class="btn-recover" id="submitBtn">
                        <i class="bi bi-key"></i>
                        <span>Solicitar Recuperación</span>
                    </button>
                </form>
            </div>

            <div class="recover-footer">
                <p>
                    <i class="bi bi-arrow-return-left"></i>
                    ¿Recuerdas tu contraseña? 
                    <a href="../public/login.php">
                        <i class="bi bi-box-arrow-in-right"></i> Inicia sesión
                    </a>
                </p>
            </div>
        </div>
    </div>

    <?php include '../include/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    // Sistema de notificaciones toast mejorado
    const ToastManager = {
        show: function(message, type = 'info') {
            const existingToasts = document.querySelectorAll('.toast-notify');
            existingToasts.forEach(toast => toast.remove());
            
            const toast = document.createElement('div');
            toast.className = `toast-notify ${type}`;
            let icon = 'ℹ';
            if(type === 'success') icon = '✓';
            if(type === 'error') icon = '✗';
            if(type === 'warning') icon = '⚠';
            if(type === 'info') icon = 'ℹ';
            
            toast.innerHTML = `<strong style="font-size:1.2rem;">${icon}</strong> <span>${message}</span>`;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.add('toast-remove');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        },
        error: function(msg) { this.show(msg, 'error'); },
        success: function(msg) { this.show(msg, 'success'); },
        warning: function(msg) { this.show(msg, 'warning'); },
        info: function(msg) { this.show(msg, 'info'); }
    };

    // Validación del formulario
    document.addEventListener('DOMContentLoaded', function() {
        const recuperarForm = document.getElementById('recuperarForm');
        const submitBtn = document.getElementById('submitBtn');
        const emailInput = document.getElementById('email');
        
        // Limpiar estilos al escribir
        if (emailInput) {
            emailInput.addEventListener('input', function() {
                this.classList.remove('is-invalid');
            });
        }
        
        if (recuperarForm) {
            recuperarForm.addEventListener('submit', function(e) {
                const email = emailInput ? emailInput.value.trim() : '';
                
                // Validar que no esté vacío
                if (!email) {
                    e.preventDefault();
                    ToastManager.error('Por favor ingresa tu correo electrónico');
                    if (emailInput) emailInput.classList.add('is-invalid');
                    return false;
                }
                
                // Validar formato de email
                const emailRegex = /^[^\s@]+@([^\s@]+\.)+[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    e.preventDefault();
                    ToastManager.error('Formato de correo electrónico inválido');
                    if (emailInput) emailInput.classList.add('is-invalid');
                    return false;
                }
                
                // Si todo es válido, mostrar estado de carga
                if (submitBtn) {
                    submitBtn.classList.add('loading');
                    submitBtn.querySelector('span').textContent = 'Enviando...';
                }
                
                ToastManager.info('Verificando correo...');
                return true;
            });
        }
    });
    
    // Mostrar mensajes de éxito/error del servidor como toast
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
        
        // Si hay parámetro de éxito en la URL (después de redirección)
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('sent') === '1') {
            ToastManager.success('Correo de recuperación enviado exitosamente');
        }
    });
    </script>
</body>
</html>

<?php
ob_end_flush(); // Enviar el buffer al navegador
?>