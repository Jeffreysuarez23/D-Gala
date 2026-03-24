<?php
require_once("include/db.php");
require_once("include/configuracionesAD.php");

$db = new Database();
$conexion = $db->getConexion();
$id = $_POST['id'] ?? '';
$sql = $conexion->prepare("UPDATE usuarios SET activacion = 2 WHERE id = ?");
$sql->execute([$id]);
echo "<script>alert('Usuario deshabilitado correctamente.')</script>";
header("Location: index.php?mod=GestionarUsuarios");

?>