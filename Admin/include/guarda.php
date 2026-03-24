<?php
  require_once("./include/db.php");
  require_once("./include/configuraciones.php");

$db = new Database();
$conexion = $db->getConexion();
// Obtener los datos del formulario
$site_name = $_POST['site_name'];
$smtp = $_POST['smtp'];
$puerto = $_POST['puerto'];
$email = $_POST['email'];
$password = $_POST['password'];

$sql =$conexion->prepare("UPDATE configuraciones SET valor = ? WHERE nombre = ?");
$sql->execute([$site_name, 'tienda_nombre']);
$sql->execute([$smtp, 'correo_smtp']);
$sql->execute([$puerto, 'correo_puerto']);
$sql->execute([$email, 'correo_email']);
$sql->execute([$password, 'correo_password']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    
    <h1>Configuraciones guardadas correctamente</h1>
    <a href="configuracion.php">Volver a Configuraciones</a>
    
</body>
</html> 