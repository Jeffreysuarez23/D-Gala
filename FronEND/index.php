<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Uniformes</title>
<link
  rel="stylesheet"
  href="https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.css"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0&icon_names=arrow_forward"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="estilos.css">
  <link rel="stylesheet" href="nosotros.css">
</head>
<body>

<nav class="navbar navbar-expand-lg bg-white">

  <div class="container-fluid">

    <a class="navbar-brand fw-bold" href="index.php?mod=inicio">D'Gala</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNavDropdown">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" href="#" onclick="openNavSidebar('mujer')">Mujer</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#" onclick="openNavSidebar('hombre')">Hombre</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="index.php?mod=nosotros">Sobre Nosotros</a>
        </li>
      </ul>

      <div class="iconos d-flex ms-auto nav-icons">

        <a href="#" class="nav-link"><i class="fas fa-search"></i></a>
        <a href="#" class="nav-link"><i class="far fa-user"></i></a>
        <a href="#" class="nav-link position-relative" onclick="openCart()">
          <i class="fas fa-shopping-cart"></i>
          <span id="cart-count" class="badge-count bg-dark text-white position-absolute top-0 start-100 translate-middle rounded-pill">1</span>
        </a>

      </div>
    </div>
  </div>
</nav>

<div class="cart-overlay" id="cartOverlay" onclick="closeCart()"></div>

<div class="cart-sidebar" id="cartSidebar">

  <div class="cart-header">
    <h5 class="mb-0">Mi carrito (1)</h5>
    <button class="cart-close" onclick="closeCart()">
      <i class="fas fa-times"></i>
    </button>
  </div>

  <div class="cart-content">

    <div class="cart-item">

      <img src="" alt="Camiseta" class="item-image">

      <div class="item-details">

        <div class="item-name">Camiseta cuello v college...</div>
        <div class="item-specs">Talla XS | Crema Claro</div>
        <div class="item-price">$ 59.900</div>

        <div class="quantity-controls">

          <button class="quantity-btn" onclick="decreaseQuantity()">−</button>
          <span class="quantity-display" id="quantity">1</span>
          <button class="quantity-btn" onclick="increaseQuantity()">+</button>

        </div>
      </div>

      <button class="delete-btn" onclick="removeItem()">
        <i class="far fa-trash-alt"></i>
      </button>
    </div>
  </div>

  <div class="cart-summary">

    <div class="summary-row">
      <span id="item-count">1 artículo</span>
      <span id="item-total">$ 59.900</span>
    </div>

    <div class="summary-row">
      <span>Envío estimado</span>
      <span id="shipping-cost">$ 7.000</span>
    </div>
    
    <div class="free-shipping">
      Faltan $ 90.000 para tu <strong>ENVÍO GRATUITO</strong>
    </div>

    <div class="summary-row total-row">
      <span>Total</span>
      <span id="total-amount">$ 66.900</span>
    </div>

    <button class="checkout-btn mt-3">Finalizar pedido</button>

    <button class="whatsapp-btn">
      <i class="fab fa-whatsapp"></i>
      Asesor
    </button>

  </div>
</div>

<div class="sidebar-overlay" onclick="closeNavSidebar()"></div>

<div class="sidebar" id="sidebar">

  <div class="sidebar-header">
    <h5 class="mb-0">Uniformes</h5>
    <button class="sidebar-close" onclick="closeNavSidebar()">
      <i class="fas fa-times"></i>
    </button>

  </div>

  <div class="gender-tabs">

    <button class="gender-tab" id="mujer-tab" onclick="switchGender('mujer')">Mujer</button>
    <button class="gender-tab" id="hombre-tab" onclick="switchGender('hombre')">Hombre</button>

  </div>

  <div class="sidebar-content">

    <ul class="sidebar-menu" id="sidebar-menu">
    </ul>

  </div>

  <div class="sidebar-bottom">

    <ul class="sidebar-bottom-menu">
      <li><a href="#">Mi perfil</a></li>
      <li><a href="#">Mis favoritos</a></li>
      <li><a href="#">Soporte</a></li>
    </ul>

  </div>
</div>

<?php
    if(@$_GET['mod'] == "") {
      require_once("modulos/inicio.php");
    }else
    if(@$_GET['mod'] == "inicio") {
      require_once("modulos/inicio.php");
    }else
    if(@$_GET['mod'] == "nosotros") {
      require_once("modulos/nosotros.php");
    }else
?>

<footer class="footer">
  <div class="footer-container">
    
    <!-- Columna 1: Logo + descripción -->
    <div class="footer-col">
      <h2 class="footer-logo">D'Gala</h2>
      <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Praesentium, maiores.
      </p>
      <p><strong>Horario:</strong><br>Lunes - Viernes: 8am - 6pm<br>Sábados: 9am - 2pm</p>
    </div>

    <!-- Columna 2: Enlaces -->
    <div class="footer-col">
      <h3>Enlaces rápidos</h3>
      <ul>
        <li><a href="#">Inicio</a></li>
        <li><a href="#">Catálogo</a></li>
        <li><a href="#">Ofertas</a></li>
        <li><a href="#">Nosotros</a></li>
        <li><a href="#">Blog</a></li>
        <li><a href="#">Contacto</a></li>
      </ul>
    </div>

    <!-- Columna 3: Contacto -->
    <div class="footer-col">
      <h3>Contacto</h3>
      <p><i class="fas fa-map-marker-alt"></i> Calle Verde #123, Bogotá, Colombia</p>
      <p><i class="fas fa-phone-alt"></i> +57 310 555 1234</p>
      <p><i class="fas fa-envelope"></i> contacto@uniformes.com</p>
    </div>

    <!-- Columna 4: Newsletter -->
    <div class="footer-col">
      <h3>Suscríbete</h3>
      <p>Recibe promociones y noticias sobre nuestros productos:</p>
      <form class="newsletter">
        <input type="email" placeholder="Tu correo" required>
        <button type="submit">Suscribirme</button>
      </form>
      <div class="social-links">
        <a href="#"><i class="fab fa-facebook-f"></i></a>
        <a href="#"><i class="fab fa-instagram"></i></a>
        <a href="#"><i class="fab fa-twitter"></i></a>
        <a href="#"><i class="fab fa-youtube"></i></a>
      </div>
    </div>
  </div>

  <!-- Línea inferior -->
  <div class="footer-bottom">
    <p>&copy; 2025 D'Gala. Todos los derechos reservados. | Hecho con amor por un mundo más formal</p>
  </div>
</footer>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">






<script src="https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.js"></script>
<script>
  const swiper = new Swiper('.mySwiper', {
    slidesPerView: 3,
    spaceBetween: 24,
    loop: false, // 🚫 no infinito (puedes poner true si quieres loop)
    pagination: {
      el: '.swiper-pagination',
      clickable: true,
    },
    navigation: {
      nextEl: '.swiper-button-next',
      prevEl: '.swiper-button-prev',
    },
    breakpoints: {
      0:   { slidesPerView: 1 },
      768: { slidesPerView: 2 },
      1024:{ slidesPerView: 3 }
    }
  });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>