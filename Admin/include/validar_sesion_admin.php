<?php
/**
 * Validar que el administrador con sesión activa siga siendo válido
 * Incluir este archivo en la cabecera del Admin panel
 * para asegurar que administradores deshabilitados no puedan usar el sistema
 */

if (isset($_SESSION['user_id']) && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin') {
    if (!isset($conexion)) {
        require_once 'include/db.php';
        $db = new Database();
        $conexion = $db->getConexion();
    }
    
    require_once 'include/admin.php';
    
    // Validar si el administrador sigue siendo activo
    if (!ValidarSesionAdmin($_SESSION['user_id'], $conexion)) {
        // Administrador ha sido deshabilitado o modificado
        session_destroy();
        header("Location: login.php?error=deshabilitado");
        exit;
    }
}
?>
