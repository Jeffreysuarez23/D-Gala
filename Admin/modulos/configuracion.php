<?php
  require_once("include/db.php");
  require_once("include/configuracionesAD.php");
  require_once("include/cifrado.php");

  $db = new Database();
  $conexion = $db->getConexion();

  
  // Verificar si el formulario fue enviado
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      // Obtener los datos del formulario
      $site_name = $_POST['site_name'];
      $smtp = $_POST['smtp'];
      $puerto = $_POST['puerto'];
      $email = $_POST['email'];
      $password = cifrar($_POST['password']);

      try {
          // Actualizar las configuraciones en la base de datos
          $sql = $conexion->prepare("UPDATE configuraciones SET valor = ? WHERE nombre = ?");
          $sql->execute([$site_name, 'tienda_nombre']);
          $sql->execute([$smtp, 'correo_smtp']);
          $sql->execute([$puerto, 'correo_puerto']);
          $sql->execute([$email, 'correo_email']);
          $sql->execute([$password, 'correo_password']);
          
          // Mostrar el mensaje de éxito en un alert() con JavaScript
          echo "<script type='text/javascript'>alert('Configuraciones guardadas correctamente');</script>";

      } catch (Exception $e) {
          // En caso de error, mostrar un mensaje de alerta
          echo "<script type='text/javascript'>alert('Error al guardar las configuraciones: " . $e->getMessage() . "');</script>";
      }
  }

  // Consultar la configuración actual de la base de datos
  $sql = "SELECT nombre, valor FROM configuraciones";
  $resultado = $conexion->query($sql);
  $datos = $resultado->fetchAll(PDO::FETCH_ASSOC);

  $config = [];
  foreach($datos as $dato){
      $config[$dato['nombre']] = $dato['valor'];
}
?> 


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuraciones del Sistema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding-bottom: 50px;
        }

        .header-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 10px;
            margin-bottom: 40px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .header-section h1 {
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 2.5rem;
        }

        .header-section p {
            margin-bottom: 0;
            font-size: 1.05rem;
            opacity: 0.95;
        }

        .form-container {
            background: white;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .form-section {
            margin-bottom: 35px;
            padding-bottom: 25px;
            border-bottom: 2px solid #f0f0f0;
        }

        .form-section:last-child {
            border-bottom: none;
        }

        .form-section-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 10px;
            display: block;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 15px;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .form-text {
            font-size: 0.85rem;
            color: #7f8c8d;
            margin-top: 5px;
        }

        .row {
            margin-bottom: 15px;
        }

        .row .col-md-6, .row .col-12 {
            margin-bottom: 0;
            padding-right: 15px;
        }

        .row .col-md-6:nth-child(even) {
            padding-right: 0;
        }

        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1.05rem;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .btn-submit:active {
            transform: translateY(-1px);
        }

        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            color: #667eea;
            font-weight: 600;
        }

        .alert-info {
            background: #e8f4f8;
            border-left: 4px solid #667eea;
            color: #2c3e50;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        @media (max-width: 768px) {
            .header-section {
                padding: 25px;
            }

            .header-section h1 {
                font-size: 1.8rem;
            }

            .form-container {
                padding: 20px;
            }

            .form-section-title {
                font-size: 1.1rem;
            }

            .row .col-md-6 {
                padding-right: 0 !important;
            }

            .btn-submit {
                padding: 12px 30px;
            }
        }
    </style>
</head>
<body>

<div class="container mt-5 mb-5">
    
    <!-- HEADER -->
    <div class="header-section">
        <h1><i class="bi bi-gear"></i> Configuraciones del Sistema</h1>
        <p>Gestiona la configuración global y parámetros de correo de tu tienda</p>
    </div>

    <!-- FORMULARIO -->
    <div class="form-container">
        <form action="index.php?mod=configuracion" method="post">
            
            <!-- INFORMACIÓN GENERAL -->
            <div class="form-section">
                <h6 class="form-section-title"><i class="bi bi-shop"></i> Información General</h6>
                
                <label for="site_name" class="form-label">Nombre del Sitio *</label>
                <input class="form-control" type="text" id="site_name" name="site_name" value="<?= htmlspecialchars($config['tienda_nombre']) ?>" required />
                <small class="form-text">Nombre que aparecerá en tu tienda</small>
            </div>

            <!-- CONFIGURACIÓN DE CORREO -->
            <div class="form-section">
                <h6 class="form-section-title"><i class="bi bi-envelope"></i> Configuración de Correo</h6>
                
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Configura los parámetros SMTP para que los correos de tu tienda se envíen correctamente
                </div>

                <label for="smtp" class="form-label">Servidor SMTP *</label>
                <input class="form-control" type="text" id="smtp" name="smtp" placeholder="ej: smtp.gmail.com" value="<?= htmlspecialchars($config['correo_smtp']) ?>" required />
                <small class="form-text">Servidor de correo saliente</small>

                <label for="puerto" class="form-label mt-3">Puerto SMTP *</label>
                <input class="form-control" type="text" id="puerto" name="puerto" placeholder="ej: 587" value="<?= htmlspecialchars($config['correo_puerto']) ?>" required />
                <small class="form-text">Puertos comunes: 587 (TLS) o 465 (SSL)</small>

                <label for="email" class="form-label mt-3">Correo Electrónico *</label>
                <input class="form-control" type="email" id="email" name="email" placeholder="correo@tudominio.com" value="<?= htmlspecialchars($config['correo_email']) ?>" required />
                <small class="form-text">Dirección de correo para envíos</small>

                <label for="password" class="form-label mt-3">Contraseña de Correo *</label>
                <input class="form-control" type="password" id="password" name="password" placeholder="••••••••" />
                <small class="form-text">Contraseña o token de la cuenta de correo</small>
            </div>

            <button class="btn btn-submit" type="submit">
                <i class="bi bi-check-circle"></i> Guardar Configuraciones
            </button>
        </form>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
