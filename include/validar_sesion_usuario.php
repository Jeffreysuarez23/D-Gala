<?php
/**
 * Validar que el usuario con sesión activa siga siendo válido
 * Incluir este archivo en la cabecera de páginas protegidas
 * para asegurar que usuarios deshabilitados no puedan usar el sistema
 */

if (isset($_SESSION['user_id'])) {
    if (!isset($conexion)) {
        require_once 'db.php';
        $db = new Database();
        $conexion = $db->getConexion();
    }
    
    require_once 'cliente.php';
    
    // Validar si el usuario sigue siendo activo
    if (!ValidarSesionUsuario($_SESSION['user_id'], $conexion)) {
        // Usuario ha sido deshabilitado o modificado
        session_destroy();
        header("Location: public/login.php?error=deshabilitado");
        exit;
    }
}
?>
