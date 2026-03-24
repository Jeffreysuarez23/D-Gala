<?php
$path = dirname(__FILE__);

require_once $path . "/db.php";
require_once $path . "/../Admin/include/cifrado.php";
  $db = new Database();
  $conexion = $db->getConexion();
  
  // Consultar la configuración actual de la base de datos
  $sql = "SELECT nombre, valor FROM configuraciones";
  $resultado = $conexion->query($sql);
  $datos = $resultado->fetchAll(PDO::FETCH_ASSOC);

  $config = [];
  foreach($datos as $dato){
      $config[$dato['nombre']] = $dato['valor'];
}



ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//configuraciones del sistema
define("SITE_URL","http://localhost/e-comerse2");
define("KEY_TOKEN","APR.wqc-354*");
define("MONEDA","$");

//configuraciones PayPal
define("CLIENT_ID","AfxpHsm1P-5ELgjGsvIUWhcnwda6PIIahiRoy9bJzDuzsRbR8Xh60vH3d7qROyJ7oeVZ_f__ntm9b3kp");
define("CURRENCY","COP");

//Datos para enviar correos
/* define("MAIL_HOST","smtp.gmail.com");
define("MAIL_USER","alesfornarvaez@gmail.com");
define("MAIL_PASS","wzqvixpwhuapbqei");
define("MAIL_PORT","587"); */

define("MAIL_HOST",$config['correo_smtp']);
define("MAIL_USER",$config['correo_email']);
define("MAIL_PASS",descifrar($config['correo_password']));
define("MAIL_PORT",$config['correo_puerto']);

//configuraciones sesion
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}


?>