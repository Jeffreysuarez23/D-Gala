<?php
require_once("include/db.php");
require_once("include/configuracionesAD.php");
require_once("include/admin.php");
require_once(dirname(dirname(__DIR__)) . '/include/validaciones.php');

$db = new Database();
$conexion = $db->getConexion();
$errores = [];
$tipo_usuario = isset($_POST['tipo_usuario']) ? $_POST['tipo_usuario'] : 'cliente';

// Procesar creación de usuario o admin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo_usuario = trim($_POST['tipo_usuario'] ?? 'cliente');
    
    if ($tipo_usuario === 'admin') {
        // Crear administrador
        $usuario = ValidadorFormularios::limpiar($_POST['usuario'] ?? '');
        $nombre = ValidadorFormularios::limpiar($_POST['nombre'] ?? '');
        $email = ValidadorFormularios::limpiar($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $repassword = $_POST['repassword'] ?? '';
        
        // Validaciones
        if (esNulo([$usuario, $nombre, $email, $password, $repassword])) {
            $errores[] = "❌ Todos los campos son obligatorios";
        } else {
            // Validar usuario
            $val_user = ValidadorFormularios::validar($usuario, 'usuario');
            if ($val_user !== true) {
                $errores[] = $val_user['mensaje'];
            }
            
            // Validar nombre
            $val_nombre = ValidadorFormularios::validar($nombre, 'nombre');
            if ($val_nombre !== true) {
                $errores[] = $val_nombre['mensaje'];
            }
            
            // Validar email
            $val_email = ValidadorFormularios::validar($email, 'email');
            if ($val_email !== true) {
                $errores[] = $val_email['mensaje'];
            }
            
            // Validar password
            $val_pass = ValidadorFormularios::validar($password, 'password');
            if ($val_pass !== true) {
                $errores[] = $val_pass['mensaje'];
            }
            
            // Validar coincidencia
            $val_match = ValidadorFormularios::validarCoincidencia($password, $repassword, 'Contraseña', 'Confirmación');
            if ($val_match !== true) {
                $errores[] = $val_match['mensaje'];
            }
        }
        
        if (UsuarioExistenteAdmin($usuario, $conexion)) {
            $errores[] = "❌ El usuario de administrador ya existe";
        }
        
        if (EmailExistenteAdmin($email, $conexion)) {
            $errores[] = "❌ El email ya está registrado en administradores";
        }
        
        if (empty($errores)) {
            if (CrearAdmin($usuario, $nombre, $email, $password, $conexion)) {
                echo "<script>alert('✅ Administrador creado correctamente'); window.location.href='index.php?mod=GestionarUsuarios';</script>";
                exit;
            } else {
                $errores[] = "❌ Error al crear el administrador";
            }
        }
        
    } else {
        // Crear usuario cliente
        $nombre = ValidadorFormularios::limpiar($_POST['nombre_cliente'] ?? '');
        $apellido = ValidadorFormularios::limpiar($_POST['apellido_cliente'] ?? '');
        $email = ValidadorFormularios::limpiar($_POST['email_cliente'] ?? '');
        $telefono = ValidadorFormularios::limpiar($_POST['telefono_cliente'] ?? '');
        $documento = ValidadorFormularios::limpiar($_POST['documento_cliente'] ?? '');
        $usuarioCliente = ValidadorFormularios::limpiar($_POST['usuario_cliente'] ?? '');
        $passwordCliente = $_POST['password_cliente'] ?? '';
        $repasswordCliente = $_POST['repassword_cliente'] ?? '';
        
        // Validaciones
        if (esNulo([$nombre, $apellido, $email, $telefono, $documento, $usuarioCliente, $passwordCliente, $repasswordCliente])) {
            $errores[] = "❌ Todos los campos son obligatorios";
        } else {
            // Validar nombres
            $val_nombre = ValidadorFormularios::validar($nombre, 'nombre');
            if ($val_nombre !== true) {
                $errores[] = "Nombre: " . $val_nombre['mensaje'];
            }
            
            $val_apellido = ValidadorFormularios::validar($apellido, 'nombre');
            if ($val_apellido !== true) {
                $errores[] = "Apellido: " . $val_apellido['mensaje'];
            }
            
            // Validar email
            $val_email = ValidadorFormularios::validar($email, 'email');
            if ($val_email !== true) {
                $errores[] = $val_email['mensaje'];
            }
            
            // Validar teléfono
            $val_tel = ValidadorFormularios::validar($telefono, 'telefono');
            if ($val_tel !== true) {
                $errores[] = $val_tel['mensaje'];
            }
            
            // Validar documento
            $val_doc = ValidadorFormularios::validar($documento, 'documento');
            if ($val_doc !== true) {
                $errores[] = $val_doc['mensaje'];
            }
            
            // Validar usuario
            $val_user = ValidadorFormularios::validar($usuarioCliente, 'usuario');
            if ($val_user !== true) {
                $errores[] = "Usuario: " . $val_user['mensaje'];
            }
            
            // Validar password
            $val_pass = ValidadorFormularios::validar($passwordCliente, 'password');
            if ($val_pass !== true) {
                $errores[] = $val_pass['mensaje'];
            }
            
            // Validar coincidencia
            $val_match = ValidadorFormularios::validarCoincidencia($passwordCliente, $repasswordCliente, 'Contraseña', 'Confirmación');
            if ($val_match !== true) {
                $errores[] = $val_match['mensaje'];
            }
        }
        
        if (EmailExistente($email, $conexion)) {
            $errores[] = "❌ El email del cliente ya existe";
        }
        
        if (UsuarioExistente($usuarioCliente, $conexion)) {
            $errores[] = "❌ El nombre de usuario ya existe";
        }
        
        if (empty($errores)) {
            if (CrearUsuarioCliente($nombre, $apellido, $email, $telefono, $documento, $usuarioCliente, $passwordCliente, $conexion)) {
                echo "<script>alert('✅ Usuario cliente creado correctamente'); window.location.href='index.php?mod=GestionarUsuarios';</script>";
                exit;
            } else {
                $errores[] = "❌ Error al crear el usuario cliente";
            }
        }
    }
}
?>

<style>
    /* ===== ESTILOS CREAR USUARIOS - BLANCO, NEGRO Y DORADO ===== */
    
    :root {
        --primary: #ffd700;
        --primary-dark: #e6c300;
        --secondary: #000000;
        --dark: #1a1a1a;
        --light: #ffffff;
        --gray: #f5f5f5;
        --border: #e0e0e0;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
    }

    * {
        transition: all 0.3s ease;
    }

    body {
        background: var(--light);
        min-height: 100vh;
    }

    /* Header Section */
    .page-header {
        background: var(--secondary);
        color: var(--primary);
        padding: 2.5rem 2rem;
        border-radius: 20px;
        margin-bottom: 2rem;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        border: 1px solid var(--border);
        position: relative;
        overflow: hidden;
    }

    .page-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary), var(--primary-dark), var(--primary));
    }

    .page-header h1 {
        margin: 0;
        font-size: 2.5rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 1rem;
        color: var(--primary);
    }

    .page-header h1 i {
        color: var(--primary);
        font-size: 2.5rem;
    }

    /* Container Form */
    .container-form {
        background: var(--light);
        border-radius: 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        padding: 2rem;
        border: 1px solid var(--border);
        transition: all 0.3s ease;
    }

    .container-form:hover {
        border-color: var(--primary);
        box-shadow: 0 8px 25px rgba(255, 215, 0, 0.1);
    }

    /* Tabs Navigation */
    .tabs-navs {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
        border-bottom: 2px solid var(--border);
        padding-bottom: 1rem;
    }

    .tab-button {
        padding: 0.75rem 1.5rem;
        border: none;
        background: transparent;
        color: var(--dark);
        cursor: pointer;
        font-weight: 600;
        position: relative;
        font-size: 1rem;
        border-radius: 12px;
        transition: all 0.3s ease;
    }

    .tab-button i {
        margin-right: 8px;
        color: var(--primary);
    }

    .tab-button.active {
        color: var(--primary);
        background: rgba(255, 215, 0, 0.1);
    }

    .tab-button.active::after {
        content: '';
        position: absolute;
        bottom: -1rem;
        left: 0;
        right: 0;
        height: 2px;
        background: var(--primary);
    }

    .tab-button:hover:not(.active) {
        background: rgba(0, 0, 0, 0.05);
        color: var(--primary);
    }

    /* Tab Content */
    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
        animation: fadeIn 0.4s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Form Groups */
    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        color: var(--secondary);
        font-weight: 600;
        font-size: 0.95rem;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid var(--border);
        border-radius: 12px;
        font-size: 1rem;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        background: var(--light);
        color: var(--dark);
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.2) !important;
    }

    .form-group input:hover,
    .form-group select:hover {
        border-color: var(--primary);
    }

    /* Form Row */
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }

    .form-group.full {
        grid-column: 1 / -1;
    }

    /* Button Group */
    .button-group {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        margin-top: 2rem;
    }

    /* Buttons */
    .btn {
        padding: 0.75rem 2rem;
        border: none;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-primary {
        background: var(--secondary);
        color: var(--light);
        border: 2px solid var(--primary);
    }

    .btn-primary:hover {
        background: var(--primary);
        color: var(--secondary);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 215, 0, 0.3);
    }

    .btn-primary:active {
        transform: translateY(0);
    }

    .btn-secondary {
        background: var(--light);
        color: var(--secondary);
        border: 2px solid var(--border);
    }

    .btn-secondary:hover {
        border-color: var(--primary);
        color: var(--primary);
        transform: translateY(-2px);
    }

    /* Error Messages */
    .error-messages {
        background: #fff9e6;
        border-left: 4px solid var(--primary);
        color: var(--secondary);
        padding: 1rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        border: 1px solid rgba(255, 215, 0, 0.2);
    }

    .error-messages ul {
        margin: 0;
        padding-left: 1.5rem;
    }

    .error-messages li {
        margin-bottom: 0.5rem;
    }

    /* Headers de sección */
    h3 {
        color: var(--secondary) !important;
        margin-bottom: 1.5rem !important;
        margin-top: 2rem !important;
        font-weight: 700 !important;
        position: relative;
        display: inline-block;
    }

    h3::after {
        content: '';
        position: absolute;
        bottom: -8px;
        left: 0;
        width: 50px;
        height: 2px;
        background: linear-gradient(90deg, var(--primary), transparent);
        border-radius: 2px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }

        .page-header h1 {
            font-size: 1.8rem;
        }

        .button-group {
            flex-direction: column;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }

        .tabs-navs {
            flex-direction: column;
            gap: 0.5rem;
        }

        .tab-button.active::after {
            display: none;
        }

        .tab-button.active {
            background: rgba(255, 215, 0, 0.2);
        }
    }

    /* Placeholder styling */
    .form-group input::placeholder {
        color: #cccccc;
    }

    /* Password field */
    input[type="password"] {
        letter-spacing: 2px;
    }

    /* Focus state - solo input */
    .form-group input:focus,
    .form-group select:focus {
        transition-property: border-color, box-shadow;
        transition-duration: 0.4s;
        transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    }
</style>

<!-- Font Awesome Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<main class="container mt-4 mb-5">
    <div class="page-header">
        <h1>
            <i class="fas fa-user-plus"></i>
            Crear Usuarios
        </h1>
    </div>

    <div class="container-form">
        <!-- Mostrar errores -->
        <?php if (!empty($errores)): ?>
            <div class="error-messages">
                <ul>
                    <?php foreach ($errores as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Navegación de pestañas -->
        <div class="tabs-navs">
            <button class="tab-button active" onclick="cambiarTab('usuario', this)">
                <i class="fas fa-user"></i> Usuario Cliente
            </button>
            <button class="tab-button" onclick="cambiarTab('admin', this)">
                <i class="fas fa-lock"></i> Administrador
            </button>
        </div>

        <!-- TAB 1: Crear Usuario Cliente -->
        <div id="usuario" class="tab-content active">
            <form method="POST" action="" class="form">
                <input type="hidden" name="tipo_usuario" value="cliente">

                <h3>Información del Cliente</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre_cliente">Nombre *</label>
                        <input type="text" id="nombre_cliente" name="nombre_cliente" required 
                               value="<?php echo htmlspecialchars($_POST['nombre_cliente'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="apellido_cliente">Apellido *</label>
                        <input type="text" id="apellido_cliente" name="apellido_cliente" required 
                               value="<?php echo htmlspecialchars($_POST['apellido_cliente'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email_cliente">Email *</label>
                        <input type="email" id="email_cliente" name="email_cliente" required 
                               value="<?php echo htmlspecialchars($_POST['email_cliente'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="telefono_cliente">Teléfono *</label>
                        <input type="text" id="telefono_cliente" name="telefono_cliente" required 
                               value="<?php echo htmlspecialchars($_POST['telefono_cliente'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-group full">
                    <label for="documento_cliente">Documento / Cédula *</label>
                    <input type="text" id="documento_cliente" name="documento_cliente" required 
                           value="<?php echo htmlspecialchars($_POST['documento_cliente'] ?? ''); ?>">
                </div>

                <h3>Datos de Usuario</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="usuario_cliente">Nombre de Usuario *</label>
                        <input type="text" id="usuario_cliente" name="usuario_cliente" required 
                               value="<?php echo htmlspecialchars($_POST['usuario_cliente'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password_cliente">Contraseña *</label>
                        <input type="password" id="password_cliente" name="password_cliente" required>
                    </div>
                    <div class="form-group">
                        <label for="repassword_cliente">Confirmar Contraseña *</label>
                        <input type="password" id="repassword_cliente" name="repassword_cliente" required>
                    </div>
                </div>

                <div class="button-group">
                    <a href="index.php?mod=GestionarUsuarios" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Crear Usuario
                    </button>
                </div>
            </form>
        </div>

        <!-- TAB 2: Crear Administrador -->
        <div id="admin" class="tab-content">
            <form method="POST" action="" class="form">
                <input type="hidden" name="tipo_usuario" value="admin">

                <h3>Información del Administrador</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="usuario">Nombre de Usuario *</label>
                        <input type="text" id="usuario" name="usuario" required 
                               value="<?php echo htmlspecialchars($_POST['usuario'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="nombre">Nombre Completo *</label>
                        <input type="text" id="nombre" name="nombre" required 
                               value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-group full">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Contraseña *</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="repassword">Confirmar Contraseña *</label>
                        <input type="password" id="repassword" name="repassword" required>
                    </div>
                </div>

                <div class="button-group">
                    <a href="index.php?mod=GestionarUsuarios" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Crear Administrador
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
    function cambiarTab(tabId, button) {
        // Ocultar todos los tabs
        const tabContents = document.querySelectorAll('.tab-content');
        tabContents.forEach(tab => tab.classList.remove('active'));

        // Remover clase active de todos los botones
        const buttons = document.querySelectorAll('.tab-button');
        buttons.forEach(btn => btn.classList.remove('active'));

        // Mostrar el tab seleccionado
        document.getElementById(tabId).classList.add('active');
        button.classList.add('active');
    }
</script>