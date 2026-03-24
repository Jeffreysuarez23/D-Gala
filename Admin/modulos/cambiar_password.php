<?php
require_once("include/db.php");
require_once("include/configuracionesAD.php");
require_once("include/admin.php");

$db = new Database();
$conexion = $db->getConexion();

$user_id = $_GET['user_id'] ?? $_POST['user_id'] ?? '';


if($user_id == ''){
    header("Location: index.php");
    exit;
}

$error = [];



if (!empty($_POST)) {
    $password = trim($_POST['password']);
    $repassword = trim($_POST['repassword']);

    // Validar datos
    if (esNulo([$user_id , $password, $repassword])) {
        $error[] = "Debe llenar todos los campos";
    }
    if (!validarPassword($password, $repassword)) {
        $error[] = "Las contraseñas no coinciden";
    }
    if (empty($error) == 0) {
       $pass_hash = password_hash($password, PASSWORD_DEFAULT);
       if(ActualizarPassword($user_id , $pass_hash , $conexion)){
        $error[] = "<script>alert('Contraseña actualizada correctamente.')</script>";
        exit;
       } else {
        $error[] = "Error al actualizar la contraseña. Inténtalo de nuevo.";
    }
   
    }
}
$sql = "SELECT id, nombre_usuario FROM usuarios WHERE id = ?";
$sql = $conexion->prepare($sql);
$sql->execute([$user_id]);
$usuario = $sql->fetch(PDO::FETCH_ASSOC);

?>

<h1>Cambiar Contraseña</h1>
    <br>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card shadow-sm">
          <div class="card-body">
            <h3 class="text-center mb-4">Cambiar Contraseña</h3>
            <?php MostrarError($error); ?>
            
            <form action="index.php?mod=cambiar_password&user_id=<?php echo $user_id; ?>" method="POST" autocomplete="off">
                <!-- Datos Ocultos -->
                 
                <input type ="hidden" name="user_id" value="<?php $usuario['id']; ?>">
          
                <!-- Usuario -->
               <div class="mb-3">
                <label for="text" class="form-label">Usuario</label>
                <input type="text" class="form-control" id="usuario" name="usuario" value="<?php echo $usuario['nombre_usuario']; ?>" disabled>
              </div>
                <!-- Nueva contraseña -->
              <div class="mb-3">
                <label for="" class="form-label">Nueva Contraseña</label>
                <input type="password" class="form-control" id="password" name="password" required>
              </div>
              <!-- Confirmar contraseña -->
              <div class="mb-3">
                <label for="repassword" class="form-label">Confirmar Contraseña</label>
                <input type="password" class="form-control" id="repassword" name="repassword" required>
              </div>
              <!-- Btn Solicitar -->
              <div class="d-grid">
                <button type="submit" class="btn btn-primary">Cambiar Contraseña</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>