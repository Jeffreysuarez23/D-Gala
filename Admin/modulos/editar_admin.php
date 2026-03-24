<?php
require_once("include/db.php");
require_once("include/admin_crud.php");

$db = new Database();
$conexion = $db->getConexion();

$admin_info = obtenerAdminLogin($conexion);

// Inicializar variables para el formulario
$nombre = $admin_info['nombre'] ?? '';
$email = $admin_info['email'] ?? '';
$usuario = $admin_info['usuario'] ?? '';
$mensaje = '';
$tipo_mensaje = 'error';

// Procesar formulario de datos personales
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_datos'])) {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    $resultado = actualizarDatosAdmin($_SESSION['user_id'], $nombre, $email, $conexion);
    $mensaje = $resultado['message'];
    $tipo_mensaje = $resultado['success'] ? 'success' : 'error';
    
    if ($resultado['success']) {
        // Actualizar la información en la sesión
        $admin_info = obtenerAdminLogin($conexion);
        $nombre = $admin_info['nombre'];
        $email = $admin_info['email'];
    }
}

// Procesar formulario de cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_password'])) {
    $password_actual = trim($_POST['password_actual'] ?? '');
    $password_nueva = trim($_POST['password_nueva'] ?? '');
    $password_confirmar = trim($_POST['password_confirmar'] ?? '');
    
    $resultado = cambiarPasswordAdmin($_SESSION['user_id'], $password_actual, $password_nueva, $password_confirmar, $conexion);
    $mensaje = $resultado['message'];
    $tipo_mensaje = $resultado['success'] ? 'success' : 'error';
    
    // Limpiar los campos de contraseña
    if (!$resultado['success']) {
        // Mantener el formulario si hay error
    } else {
        $password_actual = '';
        $password_nueva = '';
        $password_confirmar = '';
    }
}
?>

<div class="container-fluid">
    <!-- ========== title-wrapper start ========== -->
    <div class="title-wrapper pt-30">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="title">
                    <h2>Mi Perfil de Administrador</h2>
                </div>
            </div>
            <div class="col-md-6">
                <div class="breadcrumb-wrapper">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="index.php?mod=inicio">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                Mi Perfil
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <!-- ========== title-wrapper end ========== -->

    <?php if (!empty($mensaje)): ?>
        <div class="row mb-30">
            <div class="col-12">
                <?php mostrarMensajeAdmin($mensaje, $tipo_mensaje); ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Información del Admin -->
        <div class="col-lg-4 mb-30">
            <div class="card card-profile elevate-card">
                <div class="card-body">
                    <div class="admin-profile-section text-center pb-30">
                        <!-- Avatar con Iniciales -->
                        <div class="avatar-initials mb-30">
                            <?php
                                $palabras = explode(' ', trim($nombre));
                                $iniciales = '';
                                foreach ($palabras as $palabra) {
                                    if (!empty($palabra)) {
                                        $iniciales .= strtoupper($palabra[0]);
                                    }
                                }
                                $iniciales = substr($iniciales, 0, 2);
                            ?>
                            <div class="avatar-circle">
                                <span><?php echo $iniciales; ?></span>
                            </div>
                        </div>
                        
                        <div class="profile-info-text">
                            <h4 class="text-dark fw-bold"><?php echo htmlspecialchars($nombre); ?></h4>
                            <p class="text-info-username">@<?php echo htmlspecialchars($usuario); ?></p>
                            <p class="text-info-email">
                                <i class="lni lni-envelope me-2"></i>
                                <?php echo htmlspecialchars($email); ?>
                            </p>
                        </div>
                        
                        <div class="status-badge mt-20">
                            <span class="badge badge-status"><i class="lni lni-check-circle me-2"></i>Administrador Activo</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulario de Datos Personales -->
        <div class="col-lg-8 mb-30">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-30">Editar Información Personal</h5>
                    
                    <form action="index.php?mod=editar_admin" method="POST" class="form-horizontal">
                        <div class="row">
                            <div class="col-md-6 mb-30">
                                <div class="input-style-1">
                                    <label for="usuario">Usuario (No editable)</label>
                                    <input type="text" id="usuario" class="form-control" value="<?php echo htmlspecialchars($usuario); ?>" disabled>
                                    <small class="text-muted">El usuario no se puede cambiar</small>
                                </div>
                            </div>

                            <div class="col-md-6 mb-30">
                                <div class="input-style-1">
                                    <label for="nombre">Nombre Completo *</label>
                                    <input type="text" id="nombre" name="nombre" class="form-control" value="<?php echo htmlspecialchars($nombre); ?>" required>
                                </div>
                            </div>

                            <div class="col-12 mb-30">
                                <div class="input-style-1">
                                    <label for="email">Correo Electrónico *</label>
                                    <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
                                </div>
                            </div>

                            <div class="col-12">
                                <button type="submit" name="actualizar_datos" class="main-btn primary-btn btn-hover">
                                    <i class="lni lni-save me-2"></i> Actualizar Información
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Cambiar Contraseña -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-30">Cambiar Contraseña</h5>
                    
                    <form action="index.php?mod=editar_admin" method="POST" class="form-horizontal">
                        <div class="row">
                            <div class="col-12 mb-30">
                                <div class="input-style-1">
                                    <label for="password_actual">Contraseña Actual *</label>
                                    <input type="password" id="password_actual" name="password_actual" class="form-control" required autocomplete="current-password">
                                    <small class="text-muted">Por seguridad, debes verificar tu contraseña actual</small>
                                </div>
                            </div>

                            <div class="col-md-6 mb-30">
                                <div class="input-style-1">
                                    <label for="password_nueva">Nueva Contraseña *</label>
                                    <input type="password" id="password_nueva" name="password_nueva" class="form-control" required autocomplete="new-password" >
                                    
                                </div>
                            </div>

                            <div class="col-md-6 mb-30">
                                <div class="input-style-1">
                                    <label for="password_confirmar">Confirmar Nueva Contraseña *</label>
                                    <input type="password" id="password_confirmar" name="password_confirmar" class="form-control" required autocomplete="new-password" >
                                </div>
                            </div>

                            <div class="col-12">
                                <button type="submit" name="cambiar_password" class="main-btn primary-btn btn-hover">
                                    <i class="lni lni-shield-check me-2"></i> Cambiar Contraseña
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Información de Seguridad -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-30">Recomendaciones de Seguridad</h5>
                    
                    <div class="security-info">
                        <div class="alert alert-info mb-20" role="alert">
                            <h6 class="mb-10">✓ Cuenta Protegida</h6>
                            <p class="text-sm">Tu cuenta de administrador está protegida con contraseña encriptada.</p>
                        </div>

                        <div class="alert alert-warning mb-20" role="alert">
                            <h6 class="mb-10">⚠ Consejo de Seguridad</h6>
                            <ul class="text-sm mb-0">
                                <li>Usa una contraseña fuerte con mayúsculas, minúsculas y números</li>
                                <li>Cambia tu contraseña regularmente (cada 3 meses)</li>
                                <li>No compartas tu cuenta con otros usuarios</li>
                                <li>Cierra sesión después de terminar</li>
                            </ul>
                        </div>

                        <div class="alert alert-info" role="alert">
                            <h6 class="mb-10">ℹ Información Útil</h6>
                            <p class="text-sm mb-0">Si olvidaste tu contraseña, contacta al administrador del servidor.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* ====== Variables de Color ====== */
:root {
    --primary-color: #365cf5;
    --success-color: #4caf50;
    --warning-color: #ff9800;
    --danger-color: #f44336;
    --info-color: #2196f3;
    --gray-light: #f5f5f5;
    --gray-border: #e0e0e0;
    --text-dark: #333;
    --text-muted: #999;
}

/* ====== Card Styles ====== */
.card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    overflow: hidden;
}

.card:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
}

.elevate-card {
    border-top: 4px solid var(--primary-color);
}

.card-body {
    padding: 30px;
}

.card-title {
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid var(--gray-light);
}

/* ====== Avatar con Iniciales ====== */
.avatar-initials {
    display: flex;
    justify-content: center;
    margin-bottom: 25px;
}

.avatar-circle {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-color) 0%, #4a7bff 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 48px;
    font-weight: bold;
    box-shadow: 0 4px 12px rgba(54, 92, 245, 0.3);
    animation: scaleIn 0.5s ease;
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

.avatar-circle span {
    letter-spacing: 2px;
}

/* ====== Profile Info ====== */
.admin-profile-section {
    border-bottom: 2px solid var(--gray-light);
    padding-bottom: 25px;
}

.profile-info-text h4 {
    color: var(--text-dark);
    font-weight: 700;
    margin-bottom: 8px;
    letter-spacing: 0.5px;
}

.text-info-username {
    color: var(--primary-color);
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 10px;
}

.text-info-email {
    color: var(--text-muted);
    font-size: 13px;
    margin-bottom: 0;
}

/* ====== Status Badge ====== */
.status-badge {
    display: flex;
    justify-content: center;
}

.badge-status {
    background: linear-gradient(135deg, var(--success-color), #66bb6a);
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    box-shadow: 0 2px 8px rgba(76, 175, 80, 0.3);
}

/* ====== Input Styles ====== */
.input-style-1 {
    margin-bottom: 0;
}

.input-style-1 label {
    font-weight: 600;
    margin-bottom: 12px;
    color: var(--text-dark);
    display: block;
    font-size: 14px;
    letter-spacing: 0.3px;
}

.form-control {
    border-radius: 8px;
    border: 1.5px solid var(--gray-border);
    padding: 12px 15px;
    font-size: 14px;
    background-color: #fff;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.3rem rgba(54, 92, 245, 0.15);
    background-color: #fff;
}

.form-control:disabled {
    background-color: var(--gray-light);
    cursor: not-allowed;
    color: var(--text-muted);
}

.form-control::placeholder {
    color: #bbb;
}

/* ====== Small Text ====== */
.form-control ~ small {
    display: block;
    margin-top: 6px;
    color: var(--text-muted);
    font-size: 13px;
}

/* ====== Alert Styles ====== */
.alert {
    border: none;
    border-radius: 8px;
    border-left: 4px solid;
    margin-bottom: 20px;
    padding: 15px 20px;
    font-size: 14px;
}

.alert-info {
    background-color: #e3f2fd;
    border-left-color: var(--info-color);
    color: #0d47a1;
}

.alert-warning {
    background-color: #fff3e0;
    border-left-color: var(--warning-color);
    color: #e65100;
}

.alert-success {
    background-color: #e8f5e9;
    border-left-color: var(--success-color);
    color: #1b5e20;
}

.alert-danger {
    background-color: #ffebee;
    border-left-color: var(--danger-color);
    color: #b71c1c;
}

.alert h6 {
    font-weight: 700;
    margin-bottom: 8px;
}

.alert p {
    margin-bottom: 0;
}

/* ====== Security Info ====== */
.security-info {
    margin-top: 15px;
}

.security-info ul {
    padding-left: 20px;
    line-height: 1.8;
    margin-bottom: 0;
}

.security-info li {
    margin-bottom: 10px;
    color: var(--text-muted);
    font-size: 13px;
}

/* ====== Button Styles ====== */
.main-btn {
    border-radius: 8px;
    font-weight: 600;
    padding: 12px 30px;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    font-size: 14px;
    letter-spacing: 0.3px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.primary-btn {
    background: linear-gradient(135deg, var(--primary-color), #4a7bff);
    color: white;
}

.main-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(54, 92, 245, 0.35);
}

.main-btn:active {
    transform: translateY(0);
}

.main-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* ====== Form Horizontal ====== */
.form-horizontal .row > [class*='col-'] {
    display: flex;
    flex-direction: column;
}

/* ====== Title Wrapper ====== */
.title h2 {
    color: var(--text-dark);
    font-weight: 700;
    letter-spacing: 0.5px;
}

.breadcrumb {
    background-color: transparent;
    padding: 0;
}

.breadcrumb-item.active {
    color: var(--primary-color);
    font-weight: 600;
}

/* ====== Responsive ====== */
@media (max-width: 768px) {
    .avatar-circle {
        width: 100px;
        height: 100px;
        font-size: 40px;
    }
    
    .main-btn {
        width: 100%;
        justify-content: center;
    }
    
    .card-body {
        padding: 20px;
    }
}
</style>
