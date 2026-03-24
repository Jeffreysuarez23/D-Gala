<?php
require_once '../include/db.php';
require_once '../include/configuraciones.php';
require_once '../include/cliente.php';
require_once '../include/validaciones.php';

$user_id = $_GET['id'] ?? $_POST['user_id'] ?? '';
$token = $_GET['token'] ?? $_POST['token'] ?? '';

if($user_id == '' || $token == ''){
    header("Location: ../public/login.php");
    exit;
}

$db = new Database();
$conexion = $db->getConexion();

$error = [];

if (!VerificarTokenRequest($user_id , $token , $conexion)) {
    echo "<script>alert('❌ Token inválido o ya utilizado.')</script>";  
    
}
if (!empty($_POST)) {
    $password = ValidadorFormularios::limpiar($_POST['password']);
    $repassword = ValidadorFormularios::limpiar($_POST['repassword']);

    // Validar datos
    if (esNulo([$user_id , $token , $password, $repassword])) {
        $error[] = "❌ Debe llenar todos los campos";
    } else {
        // Validar formato password
        $val_pass = ValidadorFormularios::validar($password, 'password');
        if ($val_pass !== true) {
            $error[] = $val_pass['mensaje'];
        }
        
        // Validar coincidencia
        $val_match = ValidadorFormularios::validarCoincidencia($password, $repassword, 'Contraseña', 'Confirmación');
        if ($val_match !== true) {
            $error[] = $val_match['mensaje'];
        }
    }
    
    if (count($error) == 0) {
       $pass_hash = password_hash($password, PASSWORD_DEFAULT);
       if(ActualizarPassword($user_id , $pass_hash , $conexion)){
        echo "<script>alert('✅ Contraseña actualizada correctamente.'); window.location='../public/login.php';</script>";
        exit;
       } else {
        $error[] = "❌ Error al actualizar la contraseña. Inténtalo de nuevo.";
    }
   
    }
}
?>



<!DOCTYPE html>
<html lang="es" data-bs-theme="auto">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Productos</title>
    <?php include '../include/links.php'; ?>

    <meta name="theme-color" content="#60606dff">
</head>

<body>
    <?php include '../include/header.php'; ?>

    <br>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card shadow-sm">
          <div class="card-body">
            <h3 class="text-center mb-4">🔒 Cambiar Contraseña</h3>
            <p class="text-center text-muted mb-4">Ingresa tu nueva contraseña segura</p>
            <?php MostrarError($error); ?>
            
            <form action="reset_password.php?id=<?php echo htmlspecialchars($user_id); ?>&token=<?php echo htmlspecialchars($token); ?>" method="POST" autocomplete="off" id="resetForm" novalidate>
                <!-- Datos Ocultos -->
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                
                <!-- Nueva contraseña -->
              <div class="mb-3">
                <label for="password" class="form-label">Nueva Contraseña *</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Tu nueva contraseña segura" data-validation-type="password" required>
                <small class="text-muted d-block mt-1">🔒 Mínimo 6 caracteres, letras + números</small>
              </div>
              
              <!-- Confirmar contraseña -->
              <div class="mb-3">
                <label for="repassword" class="form-label">Confirmar Contraseña *</label>
                <input type="password" class="form-control" id="repassword" name="repassword" placeholder="Repite tu contraseña" required>
                <small class="text-muted d-block mt-1">🔒 Debe coincidir con la contraseña anterior</small>
              </div>
              
              <!-- Btn Solicitar -->
              <div class="d-grid">
                <button type="submit" class="btn btn-primary">
                  <i class="bi bi-check-circle"></i> Cambiar Contraseña
                </button>
              </div>
            </form>
             <div class="text-center mt-3">
              <small>Volver a <a href="../public/login.php">Iniciar sesión</a></small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Sistema de Validaciones -->
  <script src="../assets/js/validaciones.js"></script>
  
  <script>
    document.addEventListener('DOMContentLoaded', function() {
        const resetForm = document.getElementById('resetForm');
        
        resetForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const repassword = document.getElementById('repassword').value;

            if (!password || !repassword) {
                e.preventDefault();
                AlertSystem.error('Campos Requeridos', '❌ Por favor completa ambos campos');
                return false;
            }

            const validarPass = ValidationSystem.validar(password, 'password', 'Contraseña');
            if (validarPass !== true) {
                e.preventDefault();
                AlertSystem.error('Contraseña Inválida', validarPass.mensaje);
                return false;
            }

            const validarMatch = ValidationSystem.validarCoincidencia(password, repassword, 'Contraseña', 'Confirmación');
            if (validarMatch !== true) {
                e.preventDefault();
                AlertSystem.error('Contraseñas No Coinciden', validarMatch.mensaje);
                return false;
            }

            // Si todo es válido, mostrar confirmación
            AlertSystem.exito('Validación Completada', '✅ Actualizando contraseña...', 1500);
        });
    });
  </script>

</body>
</html>