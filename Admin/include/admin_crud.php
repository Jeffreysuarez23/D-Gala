<?php
/**
 * ESTA TAREA LA HIZO: Jhon Perna
 * SU REQUERIMIENTO FUE: RF-005 - Editar Administrador / RF-006 - Eliminar (desactivar) Administrador
 * PERTENECE A ESTE ARCHIVO: Admin/include/admin_crud.php
 */

/**
 * CRUD de Administrador
 * Funciones para gestionar el perfil del administrador
 */

require_once('admin.php');

/**
 * Obtener información del administrador logueado
 */
function obtenerAdminLogin($conexion) {
    try {
        $sql = $conexion->prepare("SELECT id, usuario, nombre, email FROM admin WHERE id = ? LIMIT 1");
        $sql->execute([$_SESSION['user_id']]);
        return $sql->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Actualizar datos personales del admin
 */
function actualizarDatosAdmin($user_id, $nombre, $email, $conexion) {
    try {
        // Validar que no sean nulos
        if (esNulo([$nombre, $email])) {
            return [
                'success' => false,
                'message' => 'Todos los campos son obligatorios'
            ];
        }

        // Validar email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'El email no es válido'
            ];
        }

        // Validar que el email no esté registrado por otro admin
        $sqlCheck = $conexion->prepare("SELECT id FROM admin WHERE email = ? AND id != ? LIMIT 1");
        $sqlCheck->execute([$email, $user_id]);
        if ($sqlCheck->fetchColumn()) {
            return [
                'success' => false,
                'message' => 'Este email ya está registrado por otro admin'
            ];
        }

        // Actualizar datos
        $sql = $conexion->prepare("UPDATE admin SET nombre = ?, email = ? WHERE id = ? LIMIT 1");
        if ($sql->execute([$nombre, $email, $user_id])) {
            return [
                'success' => true,
                'message' => '✓ Datos actualizados correctamente'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al actualizar los datos'
            ];
        }
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
}

/**
 * Verificar contraseña actual
 */
function verificarPasswordActual($user_id, $password, $conexion) {
    try {
        $sql = $conexion->prepare("SELECT password FROM admin WHERE id = ? LIMIT 1");
        $sql->execute([$user_id]);
        $result = $sql->fetch(PDO::FETCH_ASSOC);
        
        if ($result && password_verify($password, $result['password'])) {
            return true;
        }
        return false;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Cambiar contraseña del admin
 */
function cambiarPasswordAdmin($user_id, $password_actual, $password_nueva, $password_confirmar, $conexion) {
    try {
        // Validar que no sean nulos
        if (esNulo([$password_actual, $password_nueva, $password_confirmar])) {
            return [
                'success' => false,
                'message' => 'Todos los campos de contraseña son obligatorios'
            ];
        }

        // Verificar que la contraseña actual sea correcta
        if (!verificarPasswordActual($user_id, $password_actual, $conexion)) {
            return [
                'success' => false,
                'message' => 'La contraseña actual es incorrecta'
            ];
        }

        // Validar que las nuevas contraseñas coincidan
        if (!validarPassword($password_nueva, $password_confirmar)) {
            return [
                'success' => false,
                'message' => 'Las nuevas contraseñas no coinciden'
            ];
        }

        // Validar longitud mínima
        if (strlen($password_nueva) < 4) {
            return [
                'success' => false,
                'message' => 'La contraseña debe tener al menos caracteres'
            ];
        }

        // Validar que no sea igual a la actual
        if ($password_actual === $password_nueva) {
            return [
                'success' => false,
                'message' => 'La nueva contraseña debe ser diferente a la actual'
            ];
        }

        // Encriptar y actualizar
        $password_hash = password_hash($password_nueva, PASSWORD_DEFAULT);
        $sql = $conexion->prepare("UPDATE admin SET password = ? WHERE id = ? LIMIT 1");
        
        if ($sql->execute([$password_hash, $user_id])) {
            return [
                'success' => true,
                'message' => '✓ Contraseña actualizada correctamente'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al actualizar la contraseña'
            ];
        }
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
}

/**
 * Mostrar mensajes de error/éxito
 */
function mostrarMensajeAdmin($mensaje, $tipo = 'error') {
    if (empty($mensaje)) return;
    
    if (strpos($mensaje, '✓') !== false) {
        $tipo = 'success';
    }
    
    $clase = ($tipo === 'success') ? 'alert alert-success' : 'alert alert-danger';
    echo "<div class='$clase alert-dismissible fade show' role='alert'>
        $mensaje
        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
    </div>";
}
?>
