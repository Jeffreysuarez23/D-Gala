<?php
/**
 * ESTA TAREA LA HIZO: Alejandro Olivera
 * SU REQUERIMIENTO FUE: RF-001 - Autenticación Admin (usuario y contraseña encriptada con bcrypt)
 * PERTENECE A ESTE ARCHIVO: Admin/login.php
 */

require_once 'include/db.php';
require_once 'include/admin.php';
require_once 'include/configuracionesAD.php';

$db = new Database();
$conexion = $db->getConexion();

/* $password = password_hash('admin',PASSWORD_DEFAULT);
$sql = "INSERT INTO admin (usuario,password,nombre,email,activo,fecha_alta) 
VALUES ('admin','$password','Administrador','alesfornarvaez@gmail.com','1',NOW())";
$conexion->query($sql); */
$error = [];
if(!empty($_POST)){
  $usuario = trim($_POST['usuario']); 
  $password = trim($_POST['password']);

 if(esNulo([$usuario, $password])){
    $error[]= "Debe llenar todos los campos.";
}

  if(count($error)==0){
    $error[] = Login($usuario, $password, $conexion);

  }
}

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="shortcut icon" href="assets/images/favicon.svg" type="image/x-icon" />
    <title>Login</title>

    <!-- ========== All CSS files linkup ========= -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/lineicons.css" />
    <link rel="stylesheet" href="assets/css/materialdesignicons.min.css" />
    <link rel="stylesheet" href="assets/css/fullcalendar.css" />
    <link rel="stylesheet" href="assets/css/main.css" />
  </head>
  <body>
    <!-- ======== Preloader =========== -->
    <div id="preloader">
      <div class="spinner"></div>
    </div>
    <!-- ======== Preloader =========== -->

   

      <!-- ========== signin-section start ========== -->
      <section class="signin-section">
        <div class="container-fluid">
          <!-- ========== title-wrapper start ========== -->
          <div class="title-wrapper pt-30">
            <div class="row align-items-center">
              <div class="col-md-6">
                <div class="title">
                  <h2>Sign in</h2>
                </div>
              </div>
              <!-- end col -->
              <div class="col-md-6">
                <div class="breadcrumb-wrapper">
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a href="#0">Dashboard</a>
                      </li>
                      <li class="breadcrumb-item"><a href="#0">Auth</a></li>
                      <li class="breadcrumb-item active" aria-current="page">
                        Sign in
                      </li>
                    </ol>
                  </nav>
                </div>
              </div>
              <!-- end col -->
            </div>
            <!-- end row -->
          </div>
          <!-- ========== title-wrapper end ========== -->

          <div class="row g-0 auth-row">
            <div class="col-lg-6">
              <div class="auth-cover-wrapper bg-primary-100">
                <div class="auth-cover">
                  <div class="title text-center">
                    <h1 class="text-primary mb-10">Welcome Back</h1>
                    <p class="text-medium">
                      Sign in to your Existing account to continue
                    </p>
                  </div>
                  <div class="cover-image">
                    <img src="assets/images/auth/signin-image.svg" alt="" />
                  </div>
                  <div class="shape-image">
                    <img src="assets/images/auth/shape.svg" alt="" />
                  </div>
                </div>
              </div>
            </div>
            <!-- end col -->
            <div class="col-lg-6">
              <div class="signin-wrapper">
                <div class="form-wrapper">
                  <h6 class="mb-15">Iniciar Sesion</h6>
                  <p class="text-sm mb-25">
                    Bienvenido de nuevo! Por favor ingrese sus datos
                  </p>
                  <?php MostrarError($error); ?>
                    <form action="login.php" method="post" autocomplete="off" id="adminLoginForm" novalidate>
                    <div class="row">
                      <div class="col-12">
                        <div class="input-style-1">
                          <label>Email o Usuario</label>
                          <input type="text" id="usuario" name="usuario" placeholder="Email o Usuario" data-validation-type="usuario" />
                  
                        </div>
                      </div>
                      <!-- end col -->
                      <div class="col-12">
                        <div class="input-style-1">
                          <label>Password</label>
                          <input type="password" id="password" name="password" placeholder="Contraseña" data-validation-type="password" />
                        </div>
                      </div>
                      <!-- end col -->
                      <!-- end col -->
                      <div class="col-xxl-6 col-lg-12 col-md-6">
                        <div class="text-start text-md-end text-lg-start text-xxl-end mb-30">
                          <a href="reset-password.html" class="hover-underline">
                            Olvide mi contraseña?
                          </a>
                        </div>
                      </div>
                      <!-- end col -->
                      <div class="col-12">
                        <div class="button-group d-flex justify-content-center flex-wrap">
                          <button class="main-btn primary-btn btn-hover w-100 text-center" type="submit">
                            Iniciar Sesion
                          </button>
                        </div>
                      </div>
                    </div>
                    <!-- end row -->
                  </form>
                  <div class="singin-option pt-40">
                    <p class="text-sm text-medium text-center text-gray">
                      ADMIN
                    </p>
                  </div>
                </div>
              </div>
            </div>
            <!-- end col -->
          </div>
          <!-- end row -->
        </div>
      </section>
      
      <!-- ========== signin-section end ========== -->

     

    <!-- ========= All Javascript files linkup ======== -->
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/Chart.min.js"></script>
    <script src="assets/js/dynamic-pie-chart.js"></script>
    <script src="assets/js/moment.min.js"></script>
    <script src="assets/js/fullcalendar.js"></script>
    <script src="assets/js/jvectormap.min.js"></script>
    <script src="assets/js/world-merc.js"></script>
    <script src="assets/js/polyfill.js"></script>
    <script src="assets/js/main.js"></script>
    
    <!-- Sistema de Validaciones -->
    <script src="../assets/js/validaciones.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const adminLoginForm = document.getElementById('adminLoginForm');
            
            adminLoginForm.addEventListener('submit', function(e) {
                // Solo validar que los campos no estén vacíos
                const usuario = document.getElementById('usuario').value;
                const password = document.getElementById('password').value;

                if (!usuario.trim() || !password.trim()) {
                    e.preventDefault();
                    AlertSystem.error('Campos Requeridos', '❌ Completa todos los campos del formulario');
                    return false;
                }
            });
        });
    </script>
  </body>
</html>
