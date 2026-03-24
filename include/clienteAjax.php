<?php
require_once 'db.php';
require_once 'cliente.php';
$datos = [];

if (isset($_POST['action'])){
    $action = $_POST['action'];
    
    $bd = new Database();
    $conexion = $bd->getConexion();

    if ($action == 'existeUsuario') {
        $datos['ok'] = UsuarioExistente($_POST['usuario'], $conexion);

    }elseif ($action == 'existeEmail') {
        $datos['ok'] = EmailExistente($_POST['email'], $conexion);
    }
}

echo json_encode($datos);