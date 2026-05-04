<?php
require_once 'include/db.php';
require_once 'include/admin.php';
require_once 'include/configuracionesAD.php';

$db = new Database();
$conexion = $db->getConexion();

/* $password = password_hash('admin',PASSWORD_DEFAULT);
$sql = "INSERT INTO admin (usuario,password,nombre,email,activo,fecha_alta) 
VALUES ('admin','$password','Administrador','alesfornarvaez@gmail.com','1',NOW())";
$conexion->query($sql); */
$error = [];
if(!empty($_POST)){
  $usuario = trim($_POST['usuario']); 
  $password = trim($_POST['password']);

 if(esNulo([$usuario, $password])){
    $error[]= "Debe llenar todos los campos.";
}

  if(count($error)==0){
    $error[] = Login($usuario, $password, $conexion);

  }
}

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes" />
    <link rel="shortcut icon" href="assets/images/favicon.svg" type="image/x-icon" />
    <title>Login | Panel Administrativo</title>

    <!-- ========== All CSS files linkup ========= -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/lineicons.css" />
    <link rel="stylesheet" href="assets/css/materialdesignicons.min.css" />
    <link rel="stylesheet" href="assets/css/fullcalendar.css" />
    <link rel="stylesheet" href="assets/css/main.css" />
    
    <!-- ========== Estilos personalizados Negro + Dorado ========= -->
    <style>
      /* ===== COLORES PERSONALIZADOS: NEGRO (#000000) y DORADO (#ffd700) ===== */
      :root {
        --primary-black: #000000;
        --primary-gold: #ffd700;
        --gold-light: #ffed4e;
        --gold-dark: #e6c300;
        --text-dark: #1f2937;
        --text-gray: #4b5563;
        --bg-light: #f9fafb;
      }

      /* Reset y estilos base */
      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
      }

      /* Preloader con colores negro/dorado */
      #preloader {
        background-color: #000000;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
      }
      
      #preloader .spinner {
        width: 50px;
        height: 50px;
        border: 3px solid rgba(255, 215, 0, 0.2);
        border-top-color: var(--primary-gold);
        border-radius: 50%;
        animation: spin 1s linear infinite;
      }
      
      @keyframes spin {
        to { transform: rotate(360deg); }
      }

      /* Sección de login */
      .signin-section {
        background: linear-gradient(135deg, #fef9e6 0%, #fff5e0 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        width: 100%;
        overflow-x: hidden;
      }

      /* Ajuste de columnas responsive */
      .auth-row {
        display: flex;
        flex-wrap: wrap;
        width: 100%;
      }
      
      .col-left, .col-right {
        transition: all 0.3s ease;
      }
      
      /* Responsive: Desktop (1200px+) */
      @media (min-width: 1200px) {
        .col-left {
          width: 50%;
          flex: 0 0 50%;
        }
        .col-right {
          width: 50%;
          flex: 0 0 50%;
        }
      }
      
      /* Responsive: Tablet (768px - 1199px) */
      @media (min-width: 768px) and (max-width: 1199px) {
        .col-left {
          width: 50%;
          flex: 0 0 50%;
        }
        .col-right {
          width: 50%;
          flex: 0 0 50%;
        }
      }
      
      /* Responsive: Móvil (hasta 767px) */
      @media (max-width: 767px) {
        .col-left, .col-right {
          width: 100%;
          flex: 0 0 100%;
        }
      }

      /* Lado izquierdo - Dashboard interactivo */
      .auth-cover-wrapper {
        background: linear-gradient(135deg, #000000 0%, #0a0a0a 100%) !important;
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        transition: all 0.3s ease;
      }
      
      /* Responsive: Altura del lado izquierdo */
      @media (min-width: 1200px) {
        .auth-cover-wrapper {
          min-height: 100vh;
        }
      }
      
      @media (min-width: 768px) and (max-width: 1199px) {
        .auth-cover-wrapper {
          min-height: 100vh;
        }
      }
      
      @media (max-width: 767px) {
        .auth-cover-wrapper {
          min-height: auto;
          padding: 2rem 1rem;
        }
      }
      
      .auth-cover-wrapper::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary-gold), var(--gold-light), var(--primary-gold));
        z-index: 2;
      }
      
      .auth-cover-wrapper::after {
        content: "";
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary-gold), var(--gold-light), var(--primary-gold));
        z-index: 2;
      }
      
      /* Patrón de fondo sutil */
      .auth-cover-wrapper .bg-pattern {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-image: radial-gradient(var(--primary-gold) 1px, transparent 1px);
        background-size: 40px 40px;
        opacity: 0.08;
        pointer-events: none;
      }
      
      .auth-cover {
        position: relative;
        z-index: 2;
        text-align: center;
        padding: 1.5rem;
        width: 100%;
        max-width: 500px;
        margin: 0 auto;
      }
      
      /* Responsive: Ajustes del contenido izquierdo */
      @media (max-width: 767px) {
        .auth-cover {
          padding: 1rem;
        }
      }
      
      /* Logo/Icono responsive */
      .logo-icon {
        background: rgba(255, 215, 0, 0.1);
        border-radius: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        border: 2px solid rgba(255, 215, 0, 0.3);
        animation: pulse 2s ease-in-out infinite;
        transition: all 0.3s ease;
      }
      
      @media (min-width: 1200px) {
        .logo-icon {
          width: 80px;
          height: 80px;
        }
        .logo-icon i {
          font-size: 2.5rem;
        }
      }
      
      @media (min-width: 768px) and (max-width: 1199px) {
        .logo-icon {
          width: 70px;
          height: 70px;
        }
        .logo-icon i {
          font-size: 2.2rem;
        }
      }
      
      @media (max-width: 767px) {
        .logo-icon {
          width: 60px;
          height: 60px;
        }
        .logo-icon i {
          font-size: 1.8rem;
        }
      }
      
      @keyframes pulse {
        0%, 100% {
          transform: scale(1);
          box-shadow: 0 0 0 0 rgba(255, 215, 0, 0.4);
        }
        50% {
          transform: scale(1.05);
          box-shadow: 0 0 0 8px rgba(255, 215, 0, 0);
        }
      }
      
      .auth-cover-wrapper .title h1 {
        color: var(--primary-gold) !important;
        font-weight: 800;
        letter-spacing: -0.5px;
        transition: all 0.3s ease;
      }
      
      @media (min-width: 1200px) {
        .auth-cover-wrapper .title h1 {
          font-size: 2rem;
          margin-bottom: 0.5rem;
        }
        .auth-cover-wrapper .title p {
          font-size: 0.9rem;
        }
      }
      
      @media (min-width: 768px) and (max-width: 1199px) {
        .auth-cover-wrapper .title h1 {
          font-size: 1.8rem;
          margin-bottom: 0.4rem;
        }
        .auth-cover-wrapper .title p {
          font-size: 0.85rem;
        }
      }
      
      @media (max-width: 767px) {
        .auth-cover-wrapper .title h1 {
          font-size: 1.5rem;
          margin-bottom: 0.3rem;
        }
        .auth-cover-wrapper .title p {
          font-size: 0.8rem;
          margin-bottom: 1rem;
        }
      }
      
      /* Imagen Dashboard Profesional responsive */
      .dashboard-visual {
        margin: 1rem 0;
        position: relative;
        animation: float 4s ease-in-out infinite;
        display: flex;
        justify-content: center;
      }
      
      .dashboard-visual svg {
        width: 100%;
        height: auto;
        filter: drop-shadow(0 0 20px rgba(255, 215, 0, 0.2));
        transition: all 0.3s ease;
      }
      
      @media (min-width: 1200px) {
        .dashboard-visual svg {
          max-width: 360px;
        }
      }
      
      @media (min-width: 768px) and (max-width: 1199px) {
        .dashboard-visual svg {
          max-width: 320px;
        }
      }
      
      @media (max-width: 767px) {
        .dashboard-visual {
          margin: 0.5rem 0;
        }
        .dashboard-visual svg {
          max-width: 280px;
        }
      }
      
      @keyframes float {
        0%, 100% {
          transform: translateY(0px);
        }
        50% {
          transform: translateY(-10px);
        }
      }
      
      /* Stats responsive */
      .simple-stats {
        display: flex;
        justify-content: center;
        gap: 2rem;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid rgba(255, 215, 0, 0.2);
        flex-wrap: wrap;
      }
      
      @media (max-width: 767px) {
        .simple-stats {
          gap: 1.2rem;
          margin-top: 1rem;
          padding-top: 1rem;
        }
      }
      
      .stat-item {
        text-align: center;
      }
      
      .stat-number {
        color: var(--primary-gold);
        font-weight: 800;
        display: block;
      }
      
      @media (min-width: 1200px) {
        .stat-number {
          font-size: 1.4rem;
        }
        .stat-label {
          font-size: 0.7rem;
        }
      }
      
      @media (min-width: 768px) and (max-width: 1199px) {
        .stat-number {
          font-size: 1.3rem;
        }
        .stat-label {
          font-size: 0.65rem;
        }
      }
      
      @media (max-width: 767px) {
        .stat-number {
          font-size: 1.1rem;
        }
        .stat-label {
          font-size: 0.6rem;
        }
      }
      
      .stat-label {
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-top: 0.25rem;
        display: block;
      }
      
      /* Lado derecho - Formulario responsive */
      .signin-wrapper {
        background: white;
        position: relative;
        display: flex;
        align-items: center;
        width: 100%;
        transition: all 0.3s ease;
      }
      
      @media (min-width: 1200px) {
        .signin-wrapper {
          min-height: 100vh;
        }
      }
      
      @media (min-width: 768px) and (max-width: 1199px) {
        .signin-wrapper {
          min-height: 100vh;
        }
      }
      
      @media (max-width: 767px) {
        .signin-wrapper {
          min-height: auto;
          padding: 2rem 0;
        }
      }
      
      .signin-wrapper::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary-gold), var(--gold-light), var(--primary-gold));
        z-index: 2;
      }
      
      .form-wrapper {
        margin: 0 auto;
        width: 90%;
        transition: all 0.3s ease;
      }
      
      @media (min-width: 1200px) {
        .form-wrapper {
          max-width: 520px;
          padding: 2.5rem;
        }
      }
      
      @media (min-width: 992px) and (max-width: 1199px) {
        .form-wrapper {
          max-width: 480px;
          padding: 2rem;
        }
      }
      
      @media (min-width: 768px) and (max-width: 991px) {
        .form-wrapper {
          max-width: 450px;
          padding: 2rem;
        }
      }
      
      @media (max-width: 767px) {
        .form-wrapper {
          max-width: 100%;
          padding: 1.5rem;
        }
      }
      
      .form-wrapper h6 {
        color: var(--primary-black);
        font-weight: 800;
        letter-spacing: -0.5px;
        transition: all 0.3s ease;
      }
      
      @media (min-width: 1200px) {
        .form-wrapper h6 {
          font-size: 2rem;
          margin-bottom: 0.75rem;
        }
        .form-wrapper .text-sm {
          font-size: 1rem;
          margin-bottom: 2rem;
        }
      }
      
      @media (min-width: 768px) and (max-width: 1199px) {
        .form-wrapper h6 {
          font-size: 1.8rem;
          margin-bottom: 0.6rem;
        }
        .form-wrapper .text-sm {
          font-size: 0.95rem;
          margin-bottom: 1.8rem;
        }
      }
      
      @media (max-width: 767px) {
        .form-wrapper h6 {
          font-size: 1.6rem;
          margin-bottom: 0.5rem;
          text-align: center;
        }
        .form-wrapper .text-sm {
          font-size: 0.9rem;
          margin-bottom: 1.5rem;
          text-align: center;
        }
      }
      
      .form-wrapper .text-sm {
        color: var(--text-gray);
      }
      
      /* Input styles responsive */
      .input-group-modern {
        margin-bottom: 1.5rem;
        transition: all 0.3s ease;
      }
      
      @media (max-width: 767px) {
        .input-group-modern {
          margin-bottom: 1.2rem;
        }
      }
      
      .input-group-modern label {
        display: block;
        color: var(--primary-black);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
      }
      
      @media (min-width: 1200px) {
        .input-group-modern label {
          font-size: 0.85rem;
          margin-bottom: 0.6rem;
        }
      }
      
      @media (min-width: 768px) and (max-width: 1199px) {
        .input-group-modern label {
          font-size: 0.8rem;
          margin-bottom: 0.55rem;
        }
      }
      
      @media (max-width: 767px) {
        .input-group-modern label {
          font-size: 0.75rem;
          margin-bottom: 0.5rem;
        }
      }
      
      .input-modern {
        position: relative;
      }
      
      .input-modern input {
        width: 100%;
        border: 2px solid #e5e7eb;
        transition: all 0.3s ease;
        background: white;
      }
      
      @media (min-width: 1200px) {
        .input-modern input {
          border-radius: 1.2rem;
          padding: 1rem 1rem 1rem 3.2rem;
          font-size: 1rem;
        }
        .input-modern i {
          left: 1.2rem;
          font-size: 1.3rem;
        }
      }
      
      @media (min-width: 768px) and (max-width: 1199px) {
        .input-modern input {
          border-radius: 1rem;
          padding: 0.9rem 0.9rem 0.9rem 3rem;
          font-size: 0.95rem;
        }
        .input-modern i {
          left: 1rem;
          font-size: 1.2rem;
        }
      }
      
      @media (max-width: 767px) {
        .input-modern input {
          border-radius: 1rem;
          padding: 0.8rem 0.8rem 0.8rem 2.8rem;
          font-size: 0.9rem;
        }
        .input-modern i {
          left: 0.9rem;
          font-size: 1.1rem;
        }
      }
      
      .input-modern input:focus {
        outline: none;
        border-color: var(--primary-gold);
        box-shadow: 0 0 0 4px rgba(255, 215, 0, 0.1);
      }
      
      .input-modern i {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        color: var(--primary-gold);
      }
      
      /* Enlace olvidé contraseña responsive */
      .forgot-link {
        text-align: right;
        margin-bottom: 1.5rem;
      }
      
      @media (max-width: 767px) {
        .forgot-link {
          margin-bottom: 1.2rem;
        }
        .forgot-link a {
          font-size: 0.8rem;
        }
      }
      
      .forgot-link a {
        color: var(--text-gray);
        text-decoration: none;
        transition: color 0.3s ease;
      }
      
      @media (min-width: 1200px) {
        .forgot-link a {
          font-size: 0.9rem;
        }
      }
      
      @media (min-width: 768px) and (max-width: 1199px) {
        .forgot-link a {
          font-size: 0.85rem;
        }
      }
      
      .forgot-link a:hover {
        color: var(--primary-gold);
      }
      
      /* Botón principal responsive */
      .btn-modern {
        background: var(--primary-black);
        color: var(--primary-gold);
        border: none;
        transition: all 0.3s ease;
        width: 100%;
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-weight: 800;
        text-transform: uppercase;
      }
      
      @media (min-width: 1200px) {
        .btn-modern {
          border-radius: 3rem;
          padding: 1.1rem 1.8rem;
          letter-spacing: 1.5px;
          gap: 0.8rem;
          font-size: 1rem;
        }
      }
      
      @media (min-width: 768px) and (max-width: 1199px) {
        .btn-modern {
          border-radius: 2.8rem;
          padding: 1rem 1.6rem;
          letter-spacing: 1.2px;
          gap: 0.7rem;
          font-size: 0.95rem;
        }
      }
      
      @media (max-width: 767px) {
        .btn-modern {
          border-radius: 2.5rem;
          padding: 0.9rem 1.4rem;
          letter-spacing: 1px;
          gap: 0.6rem;
          font-size: 0.9rem;
        }
      }
      
      .btn-modern::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 215, 0, 0.2);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
      }
      
      .btn-modern:hover::before {
        width: 400px;
        height: 400px;
      }
      
      .btn-modern:hover {
        background: var(--primary-gold);
        color: var(--primary-black);
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.25);
      }
      
      .btn-modern:active {
        transform: translateY(0);
      }
      
      /* Alertas personalizadas responsive */
      .alert-custom {
        display: flex;
        align-items: flex-start;
        gap: 0.8rem;
        animation: slideDown 0.3s ease;
        transition: all 0.3s ease;
      }
      
      @media (min-width: 1200px) {
        .alert-custom {
          padding: 1rem 1.2rem;
          border-radius: 1rem;
          margin-bottom: 1.8rem;
        }
        .alert-custom .alert-content {
          font-size: 0.95rem;
        }
        .alert-custom i {
          font-size: 1.2rem;
        }
      }
      
      @media (min-width: 768px) and (max-width: 1199px) {
        .alert-custom {
          padding: 0.9rem 1.1rem;
          border-radius: 0.9rem;
          margin-bottom: 1.5rem;
        }
        .alert-custom .alert-content {
          font-size: 0.9rem;
        }
        .alert-custom i {
          font-size: 1.1rem;
        }
      }
      
      @media (max-width: 767px) {
        .alert-custom {
          padding: 0.8rem 1rem;
          border-radius: 0.8rem;
          margin-bottom: 1.2rem;
        }
        .alert-custom .alert-content {
          font-size: 0.85rem;
        }
        .alert-custom i {
          font-size: 1rem;
        }
      }
      
      .alert-danger-custom {
        background: #fee2e2;
        border-left: 4px solid #dc2626;
        color: #991b1b;
      }
      
      /* Texto footer responsive */
      .text-gray {
        transition: all 0.3s ease;
      }
      
      @media (min-width: 1200px) {
        .text-gray {
          font-size: 0.9rem !important;
        }
      }
      
      @media (min-width: 768px) and (max-width: 1199px) {
        .text-gray {
          font-size: 0.85rem !important;
        }
      }
      
      @media (max-width: 767px) {
        .text-gray {
          font-size: 0.8rem !important;
        }
      }
      
      /* Animaciones */
      @keyframes slideDown {
        from {
          opacity: 0;
          transform: translateY(-15px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }
      
      @keyframes fadeInUp {
        from {
          opacity: 0;
          transform: translateY(30px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }
      
      @keyframes fadeInLeft {
        from {
          opacity: 0;
          transform: translateX(-30px);
        }
        to {
          opacity: 1;
          transform: translateX(0);
        }
      }
      
      .signin-wrapper {
        animation: fadeInUp 0.6s ease;
      }
      
      .auth-cover-wrapper {
        animation: fadeInLeft 0.6s ease;
      }
      
      /* Estilo para el campo cuando tiene error */
      .input-modern input.error-field {
        border-color: #dc2626;
        background-color: #fef2f2;
      }
      
      /* Botón con estado de carga */
      .btn-modern.loading {
        pointer-events: none;
        opacity: 0.8;
      }
      
      .btn-modern.loading i {
        animation: spinBtn 1s linear infinite;
      }
      
      @keyframes spinBtn {
        to {
          transform: rotate(360deg);
        }
      }
      
      /* Toast notifications responsive */
      .toast-notify {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        background: var(--primary-black);
        color: var(--primary-gold);
        border-radius: 12px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        border-left: 4px solid var(--primary-gold);
        animation: slideInRight 0.3s ease;
        transition: all 0.3s ease;
      }
      
      @media (min-width: 768px) {
        .toast-notify {
          padding: 14px 24px;
          font-size: 0.95rem;
        }
      }
      
      @media (max-width: 767px) {
        .toast-notify {
          top: 10px;
          right: 10px;
          left: 10px;
          padding: 12px 16px;
          font-size: 0.85rem;
        }
      }
      
      .toast-notify.success {
        background: #10b981;
        color: white;
        border-left-color: #059669;
      }
      
      .toast-notify.error {
        background: #ef4444;
        color: white;
        border-left-color: #dc2626;
      }
      
      @keyframes slideInRight {
        from {
          opacity: 0;
          transform: translateX(100px);
        }
        to {
          opacity: 1;
          transform: translateX(0);
        }
      }
      
      .toast-remove {
        animation: fadeOut 0.3s forwards;
      }
      
      @keyframes fadeOut {
        to {
          opacity: 0;
          transform: translateX(100px);
        }
      }
      
      /* Ajustes adicionales para móviles muy pequeños */
      @media (max-width: 480px) {
        .dashboard-visual svg {
          max-width: 240px;
        }
        
        .simple-stats {
          gap: 1rem;
        }
        
        .stat-number {
          font-size: 1rem;
        }
        
        .stat-label {
          font-size: 0.55rem;
        }
        
        .form-wrapper {
          padding: 1rem;
        }
        
        .input-modern input {
          padding: 0.75rem 0.75rem 0.75rem 2.5rem;
          font-size: 0.85rem;
        }
        
        .btn-modern {
          padding: 0.85rem 1.2rem;
          font-size: 0.85rem;
        }
      }
    </style>
  </head>
  <body>
    <!-- ======== Preloader =========== -->
    <div id="preloader">
      <div class="spinner"></div>
    </div>
    <!-- ======== Preloader =========== -->

    <!-- ========== signin-section start ========== -->
    <section class="signin-section">
      <div class="container-fluid p-0">
        <div class="row g-0 auth-row">
          <div class="col-left">
            <div class="auth-cover-wrapper">
              <div class="bg-pattern"></div>
              <div class="auth-cover">
                <div class="logo-icon">
                  <i class="lni lni-dashboard"></i>
                </div>
                <div class="title">
                  <h1>Admin Dashboard</h1>
                  <p>Sistema de gestión empresarial</p>
                </div>
                
                <!-- Dashboard Visual - Representación profesional -->
                <div class="dashboard-visual">
                  <svg viewBox="0 0 380 280" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                      <linearGradient id="goldGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#ffd700;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#e6c300;stop-opacity:1" />
                      </linearGradient>
                      <linearGradient id="darkGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#1a1a1a;stop-opacity:0.9" />
                        <stop offset="100%" style="stop-color:#000000;stop-opacity:0.9" />
                      </linearGradient>
                    </defs>
                    
                    <!-- Panel principal del Dashboard -->
                    <rect x="20" y="20" width="340" height="240" rx="16" fill="url(#darkGrad)" stroke="url(#goldGrad)" stroke-width="2"/>
                    
                    <!-- Header del Dashboard con título -->
                    <rect x="35" y="35" width="310" height="30" rx="8" fill="rgba(255,215,0,0.1)" stroke="#ffd700" stroke-width="1"/>
                    <text x="180" y="55" text-anchor="middle" fill="#ffd700" font-size="12" font-weight="bold">PANEL DE CONTROL</text>
                    
                    <!-- Tarjetas de métricas (3 cards) -->
                    <rect x="35" y="80" width="90" height="55" rx="8" fill="rgba(255,215,0,0.05)" stroke="#ffd700" stroke-width="1"/>
                    <text x="80" y="105" text-anchor="middle" fill="#ffd700" font-size="10">Usuarios</text>
                    <text x="80" y="125" text-anchor="middle" fill="#ffd700" font-size="16" font-weight="bold">1,284</text>
                    
                    <rect x="145" y="80" width="90" height="55" rx="8" fill="rgba(255,215,0,0.05)" stroke="#ffd700" stroke-width="1"/>
                    <text x="190" y="105" text-anchor="middle" fill="#ffd700" font-size="10">Ventas</text>
                    <text x="190" y="125" text-anchor="middle" fill="#ffd700" font-size="16" font-weight="bold">$45.2K</text>
                    
                    <rect x="255" y="80" width="90" height="55" rx="8" fill="rgba(255,215,0,0.05)" stroke="#ffd700" stroke-width="1"/>
                    <text x="300" y="105" text-anchor="middle" fill="#ffd700" font-size="10">Productos</text>
                    <text x="300" y="125" text-anchor="middle" fill="#ffd700" font-size="16" font-weight="bold">342</text>
                    
                    <!-- Gráfico de barras dinámico -->
                    <text x="70" y="165" fill="#9ca3af" font-size="8">Ene</text>
                    <rect x="60" y="170" width="18" height="45" fill="url(#goldGrad)" rx="3">
                      <animate attributeName="height" values="45;55;45" dur="2s" repeatCount="indefinite"/>
                      <animate attributeName="y" values="170;160;170" dur="2s" repeatCount="indefinite"/>
                    </rect>
                    
                    <text x="110" y="165" fill="#9ca3af" font-size="8">Feb</text>
                    <rect x="100" y="155" width="18" height="60" fill="url(#goldGrad)" rx="3">
                      <animate attributeName="height" values="60;70;60" dur="2s" repeatCount="indefinite" begin="0.2s"/>
                      <animate attributeName="y" values="155;145;155" dur="2s" repeatCount="indefinite" begin="0.2s"/>
                    </rect>
                    
                    <text x="150" y="165" fill="#9ca3af" font-size="8">Mar</text>
                    <rect x="140" y="165" width="18" height="50" fill="url(#goldGrad)" rx="3">
                      <animate attributeName="height" values="50;40;50" dur="2s" repeatCount="indefinite" begin="0.4s"/>
                      <animate attributeName="y" values="165;175;165" dur="2s" repeatCount="indefinite" begin="0.4s"/>
                    </rect>
                    
                    <text x="190" y="165" fill="#9ca3af" font-size="8">Abr</text>
                    <rect x="180" y="150" width="18" height="65" fill="url(#goldGrad)" rx="3">
                      <animate attributeName="height" values="65;75;65" dur="2s" repeatCount="indefinite" begin="0.6s"/>
                      <animate attributeName="y" values="150;140;150" dur="2s" repeatCount="indefinite" begin="0.6s"/>
                    </rect>
                    
                    <text x="230" y="165" fill="#9ca3af" font-size="8">May</text>
                    <rect x="220" y="160" width="18" height="55" fill="url(#goldGrad)" rx="3">
                      <animate attributeName="height" values="55;65;55" dur="2s" repeatCount="indefinite" begin="0.8s"/>
                      <animate attributeName="y" values="160;150;160" dur="2s" repeatCount="indefinite" begin="0.8s"/>
                    </rect>
                    
                    <text x="270" y="165" fill="#9ca3af" font-size="8">Jun</text>
                    <rect x="260" y="145" width="18" height="70" fill="url(#goldGrad)" rx="3">
                      <animate attributeName="height" values="70;80;70" dur="2s" repeatCount="indefinite" begin="1s"/>
                      <animate attributeName="y" values="145;135;145" dur="2s" repeatCount="indefinite" begin="1s"/>
                    </rect>
                    
                    <text x="310" y="165" fill="#9ca3af" font-size="8">Jul</text>
                    <rect x="300" y="155" width="18" height="60" fill="url(#goldGrad)" rx="3">
                      <animate attributeName="height" values="60;70;60" dur="2s" repeatCount="indefinite" begin="1.2s"/>
                      <animate attributeName="y" values="155;145;155" dur="2s" repeatCount="indefinite" begin="1.2s"/>
                    </rect>
                    
                    <!-- Línea de tendencia -->
                    <polyline points="60,210 85,195 110,205 135,190 160,200 185,185 210,195 235,180 260,190 285,175 310,185" stroke="#ffd700" fill="none" stroke-width="2" opacity="0.8">
                      <animate attributeName="points" values="60,210 85,195 110,205 135,190 160,200 185,185 210,195 235,180 260,190 285,175 310,185;60,205 85,190 110,200 135,185 160,195 185,180 210,190 235,175 260,185 285,170 310,180;60,210 85,195 110,205 135,190 160,200 185,185 210,195 235,180 260,190 285,175 310,185" dur="3s" repeatCount="indefinite"/>
                    </polyline>
                    
                    <!-- Pie del Dashboard -->
                    <rect x="35" y="215" width="310" height="30" rx="8" fill="rgba(255,215,0,0.05)" stroke="#ffd700" stroke-width="1"/>
                    <text x="190" y="235" text-anchor="middle" fill="#ffd700" font-size="9">⚡ Sistema en línea | 24/7 Monitoreo ⚡</text>
                    
                    <!-- Animación de brillo en esquinas -->
                    <circle cx="30" cy="30" r="3" fill="#ffd700" opacity="0.6">
                      <animate attributeName="opacity" values="0.6;1;0.6" dur="1.5s" repeatCount="indefinite"/>
                    </circle>
                    <circle cx="350" cy="30" r="3" fill="#ffd700" opacity="0.6">
                      <animate attributeName="opacity" values="0.6;1;0.6" dur="1.5s" repeatCount="indefinite" begin="0.5s"/>
                    </circle>
                    <circle cx="30" cy="250" r="3" fill="#ffd700" opacity="0.6">
                      <animate attributeName="opacity" values="0.6;1;0.6" dur="1.5s" repeatCount="indefinite" begin="1s"/>
                    </circle>
                    <circle cx="350" cy="250" r="3" fill="#ffd700" opacity="0.6">
                      <animate attributeName="opacity" values="0.6;1;0.6" dur="1.5s" repeatCount="indefinite" begin="1.5s"/>
                    </circle>
                  </svg>
                </div>
                
                <!-- Stats -->
                <div class="simple-stats">
                  <div class="stat-item">
                    <span class="stat-number">99.9%</span>
                    <span class="stat-label">Uptime</span>
                  </div>
                  <div class="stat-item">
                    <span class="stat-number">24/7</span>
                    <span class="stat-label">Soporte</span>
                  </div>
                  <div class="stat-item">
                    <span class="stat-number">SSL</span>
                    <span class="stat-label">Seguro</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- end col-left -->
          
          <div class="col-right">
            <div class="signin-wrapper">
              <div class="form-wrapper">
                <h6>Iniciar Sesión</h6>
                <p class="text-sm">
                  Accede al panel de administración
                </p>
                
                <?php 
                // Mostrar errores con estilo personalizado
                if(!empty($error)):
                  foreach($error as $err):
                    if(is_array($err)){
                      foreach($err as $e){
                        echo '<div class="alert-custom alert-danger-custom">
                                <i class="lni lni-warning"></i>
                                <div class="alert-content">' . htmlspecialchars($e) . '</div>
                              </div>';
                      }
                    } else {
                      echo '<div class="alert-custom alert-danger-custom">
                              <i class="lni lni-warning"></i>
                              <div class="alert-content">' . htmlspecialchars($err) . '</div>
                            </div>';
                    }
                  endforeach;
                endif;
                ?>
                
                <form action="login.php" method="post" autocomplete="off" id="adminLoginForm" novalidate>
                  <div class="input-group-modern">
                    <label>USUARIO O CORREO</label>
                    <div class="input-modern">
                      <i class="lni lni-user"></i>
                      <input type="text" id="usuario" name="usuario" placeholder="usuario@ejemplo.com" value="<?php echo isset($_POST['usuario']) ? htmlspecialchars($_POST['usuario']) : ''; ?>" />
                    </div>
                  </div>
                  
                  <div class="input-group-modern">
                    <label>CONTRASEÑA</label>
                    <div class="input-modern">
                      <i class="lni lni-lock"></i>
                      <input type="password" id="password" name="password" placeholder="Ingrese su contraseña" />
                    </div>
                  </div>
                  
                  <div class="forgot-link">
                    <a href="reset-password.html">
                      <i class="lni lni-question-circle"></i> ¿Olvidaste tu contraseña?
                    </a>
                  </div>
                  
                  <button class="btn-modern" type="submit" id="submitBtn">
                    <i class="lni lni-arrow-right-circle"></i>
                    ACCEDER AL PANEL
                  </button>
                </form>
                
                <div class="text-center mt-4">
                  <p class="text-sm text-gray">
                    <i class="lni lni-shield"></i> Área administrativa segura
                  </p>
                </div>
              </div>
            </div>
          </div>
          <!-- end col-right -->
        </div>
        <!-- end row -->
      </div>
    </section>
    <!-- ========== signin-section end ========== -->

    <!-- ========= All Javascript files linkup ======== -->
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/Chart.min.js"></script>
    <script src="assets/js/dynamic-pie-chart.js"></script>
    <script src="assets/js/moment.min.js"></script>
    <script src="assets/js/fullcalendar.js"></script>
    <script src="assets/js/jvectormap.min.js"></script>
    <script src="assets/js/world-merc.js"></script>
    <script src="assets/js/polyfill.js"></script>
    <script src="assets/js/main.js"></script>
    
    <!-- Sistema de Validaciones -->
    <script src="../assets/js/validaciones.js"></script>
    
    <script>
        // Ocultar preloader cuando la página carga
        window.addEventListener('load', function() {
            const preloader = document.getElementById('preloader');
            if (preloader) {
                setTimeout(() => {
                    preloader.style.opacity = '0';
                    setTimeout(() => {
                        preloader.style.display = 'none';
                    }, 300);
                }, 500);
            }
        });
        
        // Toast notification system
        const ToastManager = {
            show: function(message, type = 'info') {
                const existingToasts = document.querySelectorAll('.toast-notify');
                existingToasts.forEach(toast => toast.remove());
                
                const toast = document.createElement('div');
                toast.className = `toast-notify ${type}`;
                const icon = type === 'success' ? '✓' : (type === 'error' ? '✗' : 'ℹ');
                toast.innerHTML = `<strong style="font-size:1.2rem;">${icon}</strong> <span>${message}</span>`;
                document.body.appendChild(toast);
                
                setTimeout(() => {
                    toast.classList.add('toast-remove');
                    setTimeout(() => toast.remove(), 300);
                }, 3500);
            },
            error: function(msg) { this.show(msg, 'error'); },
            success: function(msg) { this.show(msg, 'success'); },
            info: function(msg) { this.show(msg, 'info'); }
        };

        document.addEventListener('DOMContentLoaded', function() {
            const adminLoginForm = document.getElementById('adminLoginForm');
            const submitBtn = document.getElementById('submitBtn');
            const usuarioInput = document.getElementById('usuario');
            const passwordInput = document.getElementById('password');
            
            // Limpiar estilos de error
            const clearFieldError = (input) => {
                input.classList.remove('error-field');
            };
            
            if (usuarioInput) {
                usuarioInput.addEventListener('input', () => clearFieldError(usuarioInput));
            }
            if (passwordInput) {
                passwordInput.addEventListener('input', () => clearFieldError(passwordInput));
            }
            
            if (adminLoginForm) {
                adminLoginForm.addEventListener('submit', function(e) {
                    const usuario = usuarioInput ? usuarioInput.value.trim() : '';
                    const password = passwordInput ? passwordInput.value.trim() : '';
                    let hasError = false;
                    
                    if (usuarioInput) usuarioInput.classList.remove('error-field');
                    if (passwordInput) passwordInput.classList.remove('error-field');
                    
                    if (!usuario) {
                        if (usuarioInput) usuarioInput.classList.add('error-field');
                        hasError = true;
                    }
                    if (!password) {
                        if (passwordInput) passwordInput.classList.add('error-field');
                        hasError = true;
                    }
                    
                    if (hasError) {
                        e.preventDefault();
                        ToastManager.error('❌ Completa todos los campos del formulario');
                        return false;
                    }
                    
                    // Mostrar estado de carga
                    if (submitBtn) {
                        submitBtn.classList.add('loading');
                        submitBtn.innerHTML = '<i class="lni lni-spinner"></i> VERIFICANDO...';
                    }
                    return true;
                });
            }
        });
        
        // Mostrar errores del servidor como toast
        window.addEventListener('DOMContentLoaded', function() {
            const alertBox = document.querySelector('.alert-custom');
            if (alertBox && alertBox.classList.contains('alert-danger-custom')) {
                const errorText = alertBox.innerText.trim();
                if (errorText) {
                    setTimeout(() => {
                        ToastManager.error(errorText.split('\n')[0].substring(0, 100));
                    }, 500);
                }
            }
        });
    </script>
  </body>
</html>