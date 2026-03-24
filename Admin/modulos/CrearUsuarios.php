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
    :root {
        --primary-gradient: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        --primary: #6366f1;
        --secondary: #8b5cf6;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --info: #3b82f6;
        --dark: #1f2937;
        --light: #f9fafb;
    }

    * {
        transition: all 0.3s ease;
    }

    body {
        background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
        min-height: 100vh;
    }

    .page-header {
        background: var(--primary-gradient);
        color: white;
        padding: 2.5rem 2rem;
        border-radius: 16px;
        margin-bottom: 2rem;
        box-shadow: 0 20px 50px rgba(99, 102, 241, 0.2);
        position: relative;
        overflow: hidden;
    }

    .page-header h1 {
        margin: 0;
        font-size: 2.5rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 1rem;
        color: white;
    }

    .page-header i {
        font-size: 2.5rem;
    }

    .container-form {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        padding: 2rem;
    }

    .tabs-navs {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
        border-bottom: 2px solid #e5e7eb;
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
    }

    .tab-button.active {
        color: var(--primary);
    }

    .tab-button.active::after {
        content: '';
        position: absolute;
        bottom: -1rem;
        left: 0;
        right: 0;
        height: 2px;
        background: var(--primary-gradient);
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
        animation: fadeIn 0.3s ease;
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

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        color: var(--dark);
        font-weight: 600;
        font-size: 0.95rem;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }

    .form-group.full {
        grid-column: 1 / -1;
    }

    .button-group {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        margin-top: 2rem;
    }

    .btn {
        padding: 0.75rem 2rem;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background: var(--primary-gradient);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(99, 102, 241, 0.2);
    }

    .btn-secondary {
        background: #e5e7eb;
        color: var(--dark);
    }

    .btn-secondary:hover {
        background: #d1d5db;
    }

    .error-messages {
        background: #fee2e2;
        border-left: 4px solid var(--danger);
        color: #991b1b;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }

    .error-messages ul {
        margin: 0;
        padding-left: 1.5rem;
    }

    .error-messages li {
        margin-bottom: 0.5rem;
    }

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
        }
    }
</style>

<main class="container">
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

                <h3 style="color: var(--dark); margin-bottom: 1.5rem;">Información del Cliente</h3>

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

                <h3 style="color: var(--dark); margin-bottom: 1.5rem; margin-top: 2rem;">Datos de Usuario</h3>

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

                <h3 style="color: var(--dark); margin-bottom: 1.5rem;">Información del Administrador</h3>

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
