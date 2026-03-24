<?php
require_once 'configuraciones.php';

// Destruye la sesión completamente
  unset($_SESSION['user_id']);
  unset($_SESSION['user_name']);
  unset($_SESSION['user_cliente']);

// Redirige al login u otra página
header("Location: ../public/index.php");
exit;
?>
