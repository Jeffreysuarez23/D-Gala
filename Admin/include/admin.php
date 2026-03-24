/**
 * ESTA TAREA LA HIZO: Alejandro Olivera
 * SU REQUERIMIENTO FUE: RF-001 - Autenticación Admin / RF-002 - Recuperar Contraseña
 * PERTENECE A ESTE ARCHIVO: Admin/include/admin.php
 */

<?php
/* function generarToken(){
return md5(uniqid(mt_rand(), false));
} */

/* function validarEmail($email){
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return true;
    }
    return false;
} */

/* function validarPassword($password, $repassword){
    if (strcmp($password, $repassword) !== 0) {
        return false;
        }
        return true;
}  */

/* function registrarCliente(array $datos ,$conexion){
    $token = generarToken();
    $sql = $conexion->prepare("INSERT INTO clientes 
    (nombre, apellido, email, telefono, documento, estatus, fecha_alta) VALUES (?,?,?,?,?,1,NOW())");
    if ($sql->execute($datos)) {
        return $conexion->lastInsertId();

    }
    return 0;

} */

/* function registrarUsuario(array $datos, $conexion){
    $sql = $conexion->prepare("INSERT INTO usuarios  
    (nombre_usuario, password, token , id_cliente) VALUES (?,?,?,?)");
    if ($sql->execute($datos)) {
        return $conexion->lastInsertId();

    }
    return 0;
} */

/* function esActivo($usuario, $conexion){
    $sql = $conexion->prepare("SELECT activo FROM admin WHERE usuario = ? LIMIT 1");
    $sql->execute([$usuario]);
    $row = $sql->fetch(PDO::FETCH_ASSOC);
    if ($row && $row['activacion'] == 1) {
        return true;
    }
    return false;
} */


function esNulo(array $parametros){
    foreach ($parametros as $parametro) {
    if (strlen(trim($parametro)) < 1) {
          return true;
       }
    }
    return false;
}


function UsuarioExistente($usuario ,$conexion){
    $sql = $conexion->prepare("SELECT id  FROM usuarios WHERE nombre_usuario LIKE ? limit 1");
    $sql->execute([$usuario]);
    if ($sql->fetchColumn() > 0) {
        return true;

    }
    return false;
}
function EmailExistente($email ,$conexion){
    $sql = $conexion->prepare("SELECT id  FROM clientes WHERE email LIKE ? LIMIT 1");
    $sql->execute([$email]);
    if ($sql->fetchColumn() > 0) {
        return true;

    }
    return false;
}
function MostrarError(array $error){
    if (count($error) > 0) {
        echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">';
        echo '<ul>';
        foreach ($error as $err) {
            echo '<li>' . htmlspecialchars($err) . '</li>';
        }
        echo '</ul>';
        echo '<strong>Hola!</strong> Debes revisar algunos de los campos a continuación.';
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
    }
}
function validarPassword($password, $repassword){
    if (strcmp($password, $repassword) !== 0) {
        return false;
    }
    return true;
} 

function ValidarToken($id, $token, $conexion){
    $msg = '';
    $sql = $conexion->prepare("SELECT id FROM usuarios WHERE id = ? AND token = ? LIMIT 1");
    $sql->execute([$id, $token]);

    if ($sql->fetchColumn() > 0) {

        if (ActivarCliente($id, $conexion)) {
            $msg = "<script>
                        alert('Cuenta activada correctamente');
                        window.location='login.php';
                    </script>";
        } else {
            $msg = "<script>
                        alert('Error al activar la cuenta');
                        window.location='index.php';
                    </script>";
        }

    } else {
        $msg = "<script>
                    alert('No existe el registro o el token es incorrecto');
                    window.location='index.php';
                </script>";
    }

    return $msg;
}
function ActivarCliente($id ,$conexion){
    $sql = $conexion->prepare("UPDATE usuarios SET activacion = 1, token = '' WHERE id = ?");
    return $sql->execute([$id]);
}
function Login($usuario, $password, $conexion ) {
    // Primero buscar el usuario sin filtro de activo para mostrar mensajes específicos
    $sql = $conexion->prepare("SELECT id, usuario, password, nombre, activo FROM admin 
        WHERE usuario = ? OR email = ? LIMIT 1");
    $sql->execute([$usuario, $usuario]);
    $row = $sql->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        // Verificar si está habilitado
        if ($row['activo'] != 1) {
            return "Tu cuenta de administrador ha sido deshabilitada. Por favor, contacta con el superadministrador.";
        }

        // Verificar la contraseña
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['nombre'] = $row['nombre'];
            $_SESSION['user_type'] = 'admin';
            header("Location: index.php");
            exit;

        } else {
            return "El usuario y/o la contraseña son incorrectas.";
        }
    } else {
        return "El usuario de administrador no existe en el sistema.";
    }
    return true;
}


function solicitaPassword($user_id,$conexion){
    $token = generarToken();
    $sql = $conexion->prepare("UPDATE usuarios SET token_password = ?,password_request = 1 WHERE id = ?");
    if($sql->execute([$token, $user_id])){
        return $token;
    }
    return null;
}
function VerificarTokenRequest($user_id , $token , $conexion){
    $sql = $conexion->prepare("SELECT id FROM usuarios WHERE id = ? AND token_password = ? AND password_request = 1 LIMIT 1");
    $sql->execute([$user_id , $token ]);
    if($sql->fetchColumn() > 0){
        return true;
    }
    return false;
}
function ActualizarPassword($user_id , $password , $conexion){
    $sql = $conexion->prepare("UPDATE usuarios SET password = ?, token_password = '', password_request = 0 WHERE id = ?");
    if ($sql->execute([$password , $user_id])) {
        return true;
    }
    return false;
}

// Funciones para crear usuarios y administradores
function generarToken(){
    return md5(uniqid(mt_rand(), false));
}

function UsuarioExistenteAdmin($usuario, $conexion){
    $sql = $conexion->prepare("SELECT id FROM admin WHERE usuario LIKE ? LIMIT 1");
    $sql->execute([$usuario]);
    if ($sql->fetchColumn() > 0) {
        return true;
    }
    return false;
}

function EmailExistenteAdmin($email, $conexion){
    $sql = $conexion->prepare("SELECT id FROM admin WHERE email LIKE ? LIMIT 1");
    $sql->execute([$email]);
    if ($sql->fetchColumn() > 0) {
        return true;
    }
    return false;
}

/**
 * NOTA: Esta función CrearAdmin es parte del RF-005 de Jhon Perna
 * (Crear Administrador: usuario único, nombre y email)
 */
function CrearAdmin($usuario, $nombre, $email, $password, $conexion){
    try {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = $conexion->prepare("INSERT INTO admin (usuario, nombre, email, password, activo, fecha_alta) 
                                   VALUES (?, ?, ?, ?, 1, NOW())");
        if ($sql->execute([$usuario, $nombre, $email, $password_hash])) {
            return true;
        }
        return false;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * NOTA: Esta función CrearUsuarioCliente es parte del RF-009 de Jeffrey Suarez
 * (Registro de Cliente: crear cliente y usuario con validaciones)
 */
function CrearUsuarioCliente($nombre, $apellido, $email, $telefono, $documento, $usuario, $password, $conexion){
    try {
        $conexion->beginTransaction();
        
        // 1. Crear cliente
        $sql = $conexion->prepare("INSERT INTO clientes (nombre, apellido, email, telefono, documento, estatus, fecha_alta) 
                                   VALUES (?, ?, ?, ?, ?, 1, NOW())");
        if (!$sql->execute([$nombre, $apellido, $email, $telefono, $documento])) {
            $conexion->rollBack();
            return false;
        }
        
        $cliente_id = $conexion->lastInsertId();
        
        // 2. Crear usuario
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $token = generarToken();
        $sql = $conexion->prepare("INSERT INTO usuarios (nombre_usuario, password, activacion, token, id_cliente) 
                                   VALUES (?, ?, 1, ?, ?)");
        if (!$sql->execute([$usuario, $password_hash, $token, $cliente_id])) {
            $conexion->rollBack();
            return false;
        }
        
        $conexion->commit();
        return true;
    } catch (Exception $e) {
        $conexion->rollBack();
        return false;
    }
}

// Validar si el administrador con sesión activa sigue siendo válido (habilitado)
function ValidarSesionAdmin($user_id, $conexion) {
    $sql = $conexion->prepare("SELECT activo FROM admin WHERE id = ? LIMIT 1");
    $sql->execute([$user_id]);
    $row = $sql->fetch(PDO::FETCH_ASSOC);
    
    if ($row && $row['activo'] == 1) {
        return true;
    }
    return false;
}