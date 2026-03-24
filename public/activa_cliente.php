<?php
require_once '../include/db.php';
require_once '../include/configuraciones.php';
require_once '../include/cliente.php';

$db = new Database();
$conexion = $db->getConexion();

$id = isset($_GET['id']) ? $_GET['id'] : '';
$token = isset($_GET['token']) ? $_GET['token'] : '';

if($id == '' || $token == '') {
    header("Location: index.php");
    exit;
}


echo validarToken($id, $token, $conexion);