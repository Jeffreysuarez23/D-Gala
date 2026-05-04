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

    <style>
        /* ===== ESTILOS CONFIGURACIÓN - BLANCO, NEGRO Y DORADO ===== */
        
        body {
            background: #ffffff;
            min-height: 100vh;
            padding-bottom: 50px;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        /* Header Section */
        .header-section {
            background: #000000;
            color: #ffd700;
            padding: 40px;
            border-radius: 20px;
            margin-bottom: 40px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            border: 1px solid #e0e0e0;
            position: relative;
            overflow: hidden;
        }

        .header-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ffd700, #e6c300, #ffd700);
        }

        .header-section h1 {
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 2.5rem;
            color: #ffd700;
        }

        .header-section h1 i {
            color: #ffd700;
            margin-right: 10px;
        }

        .header-section p {
            margin-bottom: 0;
            font-size: 1.05rem;
            color: #e0e0e0;
        }

        /* Form Container */
        .form-container {
            background: #ffffff;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
        }

        .form-container:hover {
            border-color: #ffd700;
            box-shadow: 0 8px 25px rgba(255, 215, 0, 0.1);
        }

        /* Form Sections */
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
            color: #000000;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-section-title i {
            color: #ffd700;
            font-size: 1.5rem;
        }

        /* Form Labels - Sin efectos al focus */
        .form-label {
            font-weight: 600;
            color: #000000;
            margin-bottom: 10px;
            display: block;
            font-size: 14px;
        }

        /* Form Controls - SOLO EL INPUT TIENE EL EFECTO */
        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 12px 15px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 0.95rem;
            background: #ffffff;
            color: #1a1a1a;
            width: 100%;
        }

        /* Hover state - suave */
        .form-control:hover {
            border-color: #ffd700;
        }

        /* FOCUS STATE - SOLO EL INPUT, TRANSICIÓN SUAVE */
        .form-control:focus {
            border-color: #ffd700 !important;
            background: #ffffff !important;
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.2) !important;
            outline: none !important;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }

        /* Eliminar cualquier efecto en el placeholder */
        .form-control::placeholder {
            color: #cccccc;
            transition: none;
        }

        /* Eliminar cualquier efecto en el texto de ayuda */
        .form-text {
            font-size: 0.85rem;
            color: #999999;
            margin-top: 5px;
        }

        /* Eliminar cualquier animación o efecto extra */
        .form-control:focus {
            animation: none;
        }

        /* Submit Button - Negro normal, Dorado con hover */
        .btn-submit {
            background: #000000;
            color: #ffffff;
            border: 2px solid #ffd700;
            padding: 15px 40px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1.05rem;
            transition: all 0.3s ease;
            width: 100%;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-submit:hover {
            background: #ffd700;
            color: #000000;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 215, 0, 0.3);
        }

        .btn-submit:active {
            transform: translateY(0px);
        }

        .btn-submit i {
            font-size: 1.2rem;
        }

        /* Quitar cualquier estilo de focus del botón */
        .btn-submit:focus,
        .btn-submit:focus-visible,
        .btn-submit:active:focus {
            outline: none !important;
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.3) !important;
        }

        /* Alert Info */
        .alert-info {
            background: #fff9e6;
            border-left: 4px solid #ffd700;
            color: #000000;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 25px;
            border: 1px solid rgba(255, 215, 0, 0.2);
        }

        .alert-info i {
            color: #ffd700;
            margin-right: 8px;
        }

        /* Input Group */
        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            color: #ffd700;
            font-weight: 600;
            border-radius: 12px 0 0 12px;
        }

        /* Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header-section, .form-container {
            animation: fadeInUp 0.5s ease forwards;
        }

        /* Responsive */
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

            .btn-submit {
                padding: 12px 30px;
            }
        }

        /* Password field */
        input[type="password"] {
            letter-spacing: 2px;
        }

        /* Hover effects solo para iconos */
        .form-section:hover .form-section-title i {
            transform: scale(1.05);
            transition: transform 0.3s ease;
        }

        /* Bootstrap icons fallback */
        .bi {
            font-style: normal;
            font-weight: normal;
        }

        /* Eliminar cualquier outline de todos los elementos al focus */
        *:focus,
        *:focus-visible,
        *:active:focus {
            outline: none !important;
        }

        /* Específico para inputs - sin efectos adicionales */
        input:focus,
        textarea:focus,
        select:focus,
        input:focus-visible,
        textarea:focus-visible,
        select:focus-visible {
            outline: none !important;
        }

        /* Transición suave solo para el input */
        .form-control {
            transition-property: border-color, box-shadow;
            transition-duration: 0.4s;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>
<div class="container mt-5 mb-5">
    
    <!-- HEADER -->
    <div class="header-section">
        <h1><i class="bi bi-gear-fill"></i> Configuraciones del Sistema</h1>
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
                <h6 class="form-section-title"><i class="bi bi-envelope-fill"></i> Configuración de Correo</h6>
                
                <div class="alert-info">
                    <i class="bi bi-info-circle-fill"></i> Configura los parámetros SMTP para que los correos de tu tienda se envíen correctamente
                </div>

                <label for="smtp" class="form-label">Servidor SMTP *</label>
                <input class="form-control" type="text" id="smtp" name="smtp" placeholder="ej: smtp.gmail.com" value="<?= htmlspecialchars($config['correo_smtp']) ?>" required />
                <small class="form-text">Servidor de correo saliente</small>

                <label for="puerto" class="form-label">Puerto SMTP *</label>
                <input class="form-control" type="text" id="puerto" name="puerto" placeholder="ej: 587" value="<?= htmlspecialchars($config['correo_puerto']) ?>" required />
                <small class="form-text">Puertos comunes: 587 (TLS) o 465 (SSL)</small>

                <label for="email" class="form-label">Correo Electrónico *</label>
                <input class="form-control" type="email" id="email" name="email" placeholder="correo@tudominio.com" value="<?= htmlspecialchars($config['correo_email']) ?>" required />
                <small class="form-text">Dirección de correo para envíos</small>

                <label for="password" class="form-label">Contraseña de Correo *</label>
                <input class="form-control" type="password" id="password" name="password" placeholder="••••••••" />
                <small class="form-text">Contraseña o token de la cuenta de correo</small>
            </div>

            <button class="btn-submit" type="submit">
                <i class="bi bi-check-circle-fill"></i> Guardar Configuraciones
            </button>
        </form>
    </div>

</div>
