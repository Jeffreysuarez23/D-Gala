<?php
function generarToken(){
return md5(uniqid(mt_rand(), false));
}

function esNulo(array $parametros){
    foreach ($parametros as $parametro) {
        if (strlen(trim($parametro)) < 1) {
            return true;
        }
    }
    return false;
}
function validarEmail($email){
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return true;
    }
    return false;
}
function validarPassword($password, $repassword){
    if (strcmp($password, $repassword) !== 0) {
        return false;
    }
    return true;
} 


function registrarCliente(array $datos ,$conexion){
    $token = generarToken();
    $sql = $conexion->prepare("INSERT INTO clientes 
    (nombre, apellido, email, telefono, documento, estatus, fecha_alta) VALUES (?,?,?,?,?,1,NOW())");
    if ($sql->execute($datos)) {
        return $conexion->lastInsertId();

    }
    return 0;

}
function registrarUsuario(array $datos, $conexion){
    $sql = $conexion->prepare("INSERT INTO usuarios  
    (nombre_usuario, password, token , id_cliente) VALUES (?,?,?,?)");
    if ($sql->execute($datos)) {
        return $conexion->lastInsertId();

    }
    return 0;
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

function Login($usuario, $password, $conexion , $proceso) {
    $sql = $conexion->prepare("
        SELECT u.id, u.nombre_usuario, u.id_cliente, u.password, u.activacion 
        FROM usuarios u
        INNER JOIN clientes c ON u.id_cliente = c.id
        WHERE u.nombre_usuario = ? OR c.email = ?
        LIMIT 1
    ");
    $sql->execute([$usuario, $usuario]);
    $row = $sql->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        // Verificar el estado del usuario
        if ($row['activacion'] == 0) {
            return "Tu cuenta aún no ha sido activada. Por favor, verifica tu correo electrónico para activar tu cuenta.";
        } elseif ($row['activacion'] == 2) {
            return "Tu cuenta ha sido deshabilitada. Por favor, contacta con el administrador.";
        } elseif ($row['activacion'] != 1) {
            return "El usuario no está activo. Por favor, contacta con soporte.";
        }

        // Verificar la contraseña
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['nombre_usuario'];
            $_SESSION['user_cliente'] = $row['id_cliente'];
            if($proceso == 'pago'){
                header("Location: checkout.php");
            }else{
                header("Location: index.php");
            }
            exit;

        } else {
            return "El usuario y/o la contraseña son incorrectas.";
        }
    } else {
        return "El usuario o correo electrónico no existen en el sistema.";
    }

    return true;
}


function esActivo($usuario, $conexion){
    $sql = $conexion->prepare("SELECT activacion FROM usuarios WHERE nombre_usuario = ? LIMIT 1");
    $sql->execute([$usuario]);
    $row = $sql->fetch(PDO::FETCH_ASSOC);
    if ($row && $row['activacion'] == 1) {
        return true;
    }
    return false;
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

// Validar si el usuario con sesión activa sigue siendo válido (activo)
function ValidarSesionUsuario($user_id, $conexion) {
    $sql = $conexion->prepare("SELECT activacion FROM usuarios WHERE id = ? LIMIT 1");
    $sql->execute([$user_id]);
    $row = $sql->fetch(PDO::FETCH_ASSOC);
    
    if ($row && $row['activacion'] == 1) {
        return true;
    }
    return false;
}