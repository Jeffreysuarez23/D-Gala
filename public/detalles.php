<?php
require_once '../include/configuraciones.php';
require_once '../include/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$db = new Database();
$pdo = $db->getConexion();

$id = isset($_GET['id']) ? $_GET['id'] : '';
$token = isset($_GET['token']) ? $_GET['token'] : '';

if ($id === '' || $token === '') {
    echo "Error al procesar la petición";
    exit;
}

$token_tmp = hash_hmac('sha1', $id, KEY_TOKEN);

if ($token !== $token_tmp) {
    echo "Error al procesar la petición";
    exit;
}

// Verificar si el producto existe
$stmt = $pdo->prepare("SELECT COUNT(id) FROM productos WHERE id = ? AND activo = 1");
$stmt->execute([$id]);
if ($stmt->fetchColumn() == 0) {
    echo "Producto no encontrado o inactivo.";
    exit;
}

// Obtener datos del producto
$stmt = $pdo->prepare("SELECT titulo, descripcion, precio, descuento, imagen FROM productos WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$titulo       = $row['titulo'];
$descripcion  = $row['descripcion'];
$precio       = $row['precio'];
$descuento    = $row['descuento'];
$imagen_principal = $row['imagen'];
$precio_desc  = $precio - (($precio * $descuento) / 100);

// Obtener imágenes adicionales del producto
$stmtImg = $pdo->prepare("SELECT imagen FROM producto_imagenes WHERE producto_id = ?");
$stmtImg->execute([$id]);
$imagenes_adicionales = $stmtImg->fetchAll(PDO::FETCH_COLUMN);

$ruta_base = '../assets/img/';

// Construir array final de imágenes
$imagenes = [];

if ($imagen_principal && $imagen_principal !== 'noadd.jpg') {
    $imagenes[] = $imagen_principal;
}

foreach ($imagenes_adicionales as $img) {
    if ($img !== $imagen_principal) {
        $imagenes[] = $img;
    }
}

if (empty($imagenes)) {
    $imagenes[] = 'noadd.jpg';
}
?>
<!DOCTYPE html>
<html lang="es" data-bs-theme="auto">
<head>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="tt.css">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?php echo htmlspecialchars($titulo); ?> - Tienda Exclusiva</title>
    <?php include '../include/links.php'; ?>
    <style>
        /* ======================================================================
           DISEÑO MODERNO - BLACK & GOLD PREMIUM
           ====================================================================== */
  /* ======================================================================
   DISEÑO MODERNO - BLACK & GOLD PREMIUM
   ====================================================================== */

:root {
    --primary-black: #000000;
    --primary-gold: #ffd700;
    --text-light: #ffffff;
    --text-dark: #333333;
    --gray-light: #f8f9fa;
    --gray-medium: #e9ecef;
    --shadow-sm: 0 4px 6px rgba(0, 0, 0, 0.05);
    --shadow-md: 0 10px 15px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 20px 25px rgba(0, 0, 0, 0.15);
    --shadow-gold: 0 10px 30px rgba(255, 215, 0, 0.2);
    --transition: all 0.3s ease;
    --border-radius: 16px;
}

/* ----------------------------------------------------------------------
   HERO SECTION MODERNO
   ---------------------------------------------------------------------- */

.product-hero {
    background: var(--primary-black);
    padding: 2rem 0;
    margin-bottom: 1.5rem;
    position: relative;
    border-bottom: 3px solid var(--primary-gold);
}

.product-hero .container {
    position: relative;
    z-index: 2;
}

.product-hero h1 {
    color: var(--text-light);
    font-size: 2rem;
    font-weight: 600;
    margin-bottom: 0.3rem;
    letter-spacing: -0.5px;
}

.product-hero h1 span {
    color: var(--primary-gold);
    font-weight: 700;
}

.product-hero .breadcrumb {
    background: transparent;
    padding: 0;
    margin-bottom: 0.5rem;
}

.product-hero .breadcrumb-item {
    color: rgba(255, 255, 255, 0.6);
    font-size: 0.85rem;
}

.product-hero .breadcrumb-item a {
    color: var(--primary-gold);
    text-decoration: none;
    transition: var(--transition);
}

.product-hero .breadcrumb-item a:hover {
    color: var(--text-light);
}

.product-hero .breadcrumb-item.active {
    color: var(--text-light);
}

.product-hero .breadcrumb-item + .breadcrumb-item::before {
    color: var(--primary-gold);
    content: "/";
}

/* ----------------------------------------------------------------------
   BOTÓN VOLVER MODERNO
   ---------------------------------------------------------------------- */

.btn-back {
    background: white;
    color: black;
    border: 2px solid #dee2e6;
    padding: 0.6rem 1.5rem;
    border-radius: 50px;
    font-weight: 500;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
    text-decoration: none;
    font-size: 0.95rem;
}

.btn-back:hover {
    background: #e9ecef;
    border-color: #adb5bd;
    transform: translateY(-3px);
    box-shadow: var(--shadow-sm);
    color: var(--text-dark);
    text-decoration: none;
}

/* ----------------------------------------------------------------------
   VISOR DE IMÁGENES MODERNO
   ---------------------------------------------------------------------- */

.product-gallery {
    background: white;
    border-radius: var(--border-radius);
    padding: 1.2rem;
    box-shadow: var(--shadow-md);
    border: 1px solid #f0f0f0;
    margin-bottom: 0;
}

.main-image-container {
    position: relative;
    width: 100%;
    height: 380px;
    background: #fafafa;
    border-radius: 12px;
    overflow: hidden;
    cursor: pointer;
    margin-bottom: 1rem;
    border: 1px solid #e0e0e0;
    transition: var(--transition);
}

.main-image-container:hover {
    border-color: var(--primary-gold);
    box-shadow: var(--shadow-gold);
}

.main-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
    transition: transform 0.3s ease;
}

.main-image-container:hover .main-image {
    transform: scale(1.05);
}

.zoom-hint {
    position: absolute;
    bottom: 10px;
    right: 10px;
    background: rgba(0, 0, 0, 0.7);
    color: var(--primary-gold);
    padding: 0.4rem 0.8rem;
    border-radius: 30px;
    font-size: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    border: 1px solid var(--primary-gold);
    opacity: 0;
    transition: var(--transition);
    pointer-events: none;
}

.main-image-container:hover .zoom-hint {
    opacity: 1;
}

.thumbnails-container {
    width: 100%;
    overflow-x: auto;
    padding-bottom: 0.3rem;
}

.thumbnails-grid {
    display: flex;
    gap: 0.6rem;
    padding: 0.1rem;
}

.thumbnail-item {
    flex: 0 0 70px;
    height: 70px;
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    border: 2px solid transparent;
    transition: var(--transition);
    background: white;
    box-shadow: var(--shadow-sm);
}

.thumbnail-item:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.thumbnail-item.active {
    border-color: var(--primary-gold);
    box-shadow: 0 0 0 2px rgba(255, 215, 0, 0.3);
}

.thumbnail-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* ----------------------------------------------------------------------
   MODAL DE ZOOM MODERNO
   ---------------------------------------------------------------------- */

.image-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.95);
    z-index: 99999;
    justify-content: center;
    align-items: center;
}

.image-modal.active {
    display: flex;
    animation: fadeIn 0.3s ease;
}

.modal-content {
    position: relative;
    width: 90%;
    height: 90%;
    display: flex;
    justify-content: center;
    align-items: center;
}

.modal-image {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    border-radius: 8px;
    box-shadow: var(--shadow-lg);
}

.modal-close {
    position: absolute;
    top: -40px;
    right: 0;
    background: transparent;
    border: none;
    color: white;
    font-size: 2rem;
    cursor: pointer;
    transition: var(--transition);
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.modal-close:hover {
    color: var(--primary-gold);
    transform: scale(1.1);
    background: rgba(255, 255, 255, 0.1);
}

.modal-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(0, 0, 0, 0.5);
    border: 2px solid var(--primary-gold);
    color: white;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: var(--transition);
    font-size: 1.5rem;
    z-index: 10;
}

.modal-nav:hover {
    background: var(--primary-gold);
    color: var(--primary-black);
    transform: translateY(-50%) scale(1.1);
}

.modal-nav.prev { left: 20px; }
.modal-nav.next { right: 20px; }

.modal-counter {
    position: absolute;
    bottom: -40px;
    left: 50%;
    transform: translateX(-50%);
    color: white;
    font-size: 1rem;
    background: rgba(0, 0, 0, 0.5);
    padding: 0.5rem 1.5rem;
    border-radius: 30px;
    border: 1px solid var(--primary-gold);
}

/* ----------------------------------------------------------------------
   CONTENEDOR PRINCIPAL MODERNO
   ---------------------------------------------------------------------- */

@media (min-width: 992px) {
    .col-lg-6:last-child {
        position: sticky;
        top: 20px;
        align-self: flex-start;
        height: fit-content;
    }
}

.product-main {
    background: white;
    border-radius: var(--border-radius);
    padding: 1.5rem;
    box-shadow: var(--shadow-md);
    border: 1px solid #f0f0f0;
    max-height: calc(100vh - 120px);
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: var(--primary-gold) #f0f0f0;
}

.product-main::-webkit-scrollbar { width: 6px; }
.product-main::-webkit-scrollbar-track { background: #f0f0f0; border-radius: 10px; }
.product-main::-webkit-scrollbar-thumb { background: var(--primary-gold); border-radius: 10px; }
.product-main::-webkit-scrollbar-thumb:hover { background: #e6c200; }

.product-title {
    font-size: 1.8rem;
    font-weight: 600;
    color: var(--primary-black);
    margin-bottom: 1rem;
    line-height: 1.2;
    letter-spacing: -0.5px;
}

/* ----------------------------------------------------------------------
   PRECIO MODERNO
   ---------------------------------------------------------------------- */

.product-price {
    margin-bottom: 0.75rem;
    width: 100%;
    display: flex;
    justify-content: flex-end !important;
}

.price-current {
    font-size: clamp(0.95rem, 4vw, 1.2rem);
    font-weight: 700;
    color: var(--primary-gold) !important;
    text-shadow: 1px 1px 0 rgba(0,0,0,0.05);
    text-align: right !important;
    display: inline-block;
}

@media (min-width: 768px) {
    .price-current { font-size: clamp(1.1rem, 3vw, 1.3rem); }
}

.product-card .card-body { display: flex; flex-direction: column; }

.product-card .product-price {
    margin-top: auto;
    margin-bottom: 0.5rem;
    width: 100%;
    display: flex;
    justify-content: flex-end;
}

.price-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    padding: 1.2rem;
    border-radius: 12px;
    margin: 1rem 0;
    border: 1px solid rgba(255, 215, 0, 0.3);
    position: relative;
}

.price-original {
    font-size: 1rem;
    color: #999;
    text-decoration: line-through;
    margin-bottom: 0.2rem;
}

.price-current {
    font-size: 2.4rem;
    font-weight: 700;
    color: var(--primary-black);
    line-height: 1.1;
    margin-bottom: 0.3rem;
}

.discount-badge {
    display: inline-block;
    background: var(--primary-black);
    color: var(--primary-gold);
    padding: 0.3rem 1rem;
    border-radius: 30px;
    font-weight: 600;
    font-size: 0.85rem;
}

.info-note {
    margin-top: 0.8rem;
    padding: 0.6rem 0.8rem;
    background: rgba(255, 215, 0, 0.05);
    border-radius: 8px;
    color: #666;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.info-note i { color: var(--primary-gold); }

/* ---------------------------------------------------------------------- */

.description-section {
    background: #f8f9fa;
    padding: 1.2rem;
    border-radius: 12px;
    margin: 1rem 0;
    border: 1px solid #e0e0e0;
}

.description-section p {
    color: #555;
    margin: 0;
    line-height: 1.5;
    font-size: 0.95rem;
}

.variantes-section { margin: 1rem 0; }

.variantes-section h4 {
    color: var(--primary-black);
    font-weight: 600;
    margin-bottom: 1rem;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.variantes-section h4 i { color: var(--primary-gold); }

.tallas-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(60px, 1fr));
    gap: 0.6rem;
    margin-bottom: 1.5rem;
}

.talla-btn {
    padding: 0.7rem 0.2rem;
    border: 1px solid #e0e0e0;
    background: white;
    color: var(--primary-black);
    font-weight: 500;
    border-radius: 8px;
    cursor: pointer;
    transition: var(--transition);
    text-align: center;
    font-size: 0.85rem;
    box-shadow: var(--shadow-sm);
}

.talla-btn:hover { border-color: var(--primary-gold); transform: translateY(-1px); box-shadow: 0 3px 10px rgba(255, 215, 0, 0.15); }
.talla-btn.active { background: var(--primary-black); color: var(--primary-gold); border-color: var(--primary-gold); }
.talla-btn:disabled { opacity: 0.4; cursor: not-allowed; }

.colores-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(75px, 1fr));
    gap: 0.6rem;
    margin-bottom: 1.5rem;
}

.color-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.3rem;
    cursor: pointer;
    transition: var(--transition);
    padding: 0.3rem;
    border-radius: 8px;
    background: white;
    border: 1px solid transparent;
}

.color-btn:hover { transform: translateY(-2px); border-color: var(--primary-gold); box-shadow: 0 3px 10px rgba(255, 215, 0, 0.1); }

.color-circle {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    border: 2px solid #e0e0e0;
    transition: var(--transition);
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.color-btn:hover .color-circle { border-color: var(--primary-gold); transform: scale(1.03); }
.color-btn.active .color-circle { border-color: var(--primary-gold); border-width: 3px; box-shadow: 0 0 0 2px rgba(255, 215, 0, 0.2); }
.color-btn:disabled { opacity: 0.4; cursor: not-allowed; }

.color-name { font-size: 0.7rem; color: var(--primary-black); font-weight: 500; }

.variante-info {
    background: linear-gradient(135deg, #fff9e6 0%, #ffffff 100%);
    padding: 1.2rem;
    border-radius: 10px;
    margin: 1rem 0;
    border: 1px solid var(--primary-gold);
    display: none;
}

.variante-info.activa { display: block; animation: fadeIn 0.3s ease; }

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}

.variante-detalles p {
    margin: 0.5rem 0;
    color: #555;
    display: flex;
    align-items: center;
    gap: 0.6rem;
    font-size: 0.95rem;
}

.variante-detalles p i { color: var(--primary-gold); width: 18px; font-size: 0.9rem; }
.variante-precio { font-size: 1.5rem; font-weight: 700; color: var(--primary-black); }

.variante-stock {
    display: inline-block;
    padding: 0.3rem 0.8rem;
    border-radius: 30px;
    font-weight: 500;
    font-size: 0.85rem;
}

.variante-stock.disponible { background: #e8f5e9; color: #2e7d32; }
.variante-stock.agotado { background: #ffebee; color: #c62828; }

.quantity-input {
    width: 100px;
    height: 42px;
    padding: 0.4rem 0.8rem;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    text-align: center;
    font-weight: 500;
    font-size: 0.95rem;
    transition: var(--transition);
}

.quantity-input:focus { outline: none; border-color: var(--primary-gold); box-shadow: 0 0 0 2px rgba(255, 215, 0, 0.1); }

.variantes-carrito {
    background: white;
    border: 1px solid var(--primary-gold);
    border-radius: 12px;
    padding: 1.2rem;
    margin: 1rem 0;
    box-shadow: var(--shadow-md);
}

.variantes-carrito h5 {
    color: var(--primary-black);
    font-weight: 600;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1rem;
}

.variantes-carrito h5 i { color: var(--primary-gold); }

.variante-item {
    background: #f8f9fa;
    padding: 0.8rem;
    border-radius: 8px;
    margin-bottom: 0.8rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border: 1px solid #e0e0e0;
    transition: var(--transition);
}

.variante-item:hover { border-color: var(--primary-gold); box-shadow: var(--shadow-sm); }

.variante-item-info { flex-grow: 1; }
.variante-item-titulo { font-weight: 600; color: var(--primary-black); margin-bottom: 0.1rem; font-size: 0.95rem; }
.variante-item-precio { font-size: 0.85rem; color: #666; }

.variante-item-cantidad {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.variante-item-cantidad input {
    width: 60px;
    padding: 0.4rem;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    text-align: center;
    font-weight: 500;
    font-size: 0.9rem;
}

.btn-remove-variante {
    background: white;
    border: 1px solid #dc3545;
    color: #dc3545;
    padding: 0.4rem 0.8rem;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    gap: 0.2rem;
}

.btn-remove-variante:hover { background: #dc3545; color: white; }

.total-variantes {
    text-align: right;
    margin-top: 1rem;
    padding-top: 0.8rem;
    border-top: 1px dashed #e0e0e0;
    font-weight: 700;
    font-size: 1.3rem;
    color: var(--primary-black);
}

.total-variantes span { font-size: 0.85rem; font-weight: 500; color: #666; margin-right: 0.5rem; }

.btn-gold {
    background: var(--primary-black);
    color: var(--primary-gold);
    padding: 0.8rem 1.5rem;
    font-weight: 600;
    border-radius: 10px;
    transition: var(--transition);
    font-size: 0.95rem;
    cursor: pointer;
    box-shadow: var(--shadow-sm);
}

.btn-gold:hover:not(:disabled) { background: var(--primary-gold); color: var(--primary-black); transform: translateY(-1px); box-shadow: 0 5px 15px rgba(255, 215, 0, 0.25); }
.btn-gold:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-gold i { margin-right: 0.4rem; }

.form-simple {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 12px;
    margin-top: 1rem;
    border: 1px solid #e0e0e0;
}

.loading { text-align: center; padding: 2rem; display: none; }
.loading.activo { display: block; }
.loading .spinner-border { color: var(--primary-gold); width: 2.5rem; height: 2.5rem; }
.loading p { margin-top: 0.5rem; color: #666; font-size: 0.9rem; }

.notification {
    position: fixed;
    top: 100px;
    right: 20px;
    z-index: 9999;
    min-width: 320px;
    padding: 0.8rem 1.2rem;
    border-radius: 10px;
    background: white;
    color: var(--primary-black);
    border-left: 4px solid var(--primary-gold);
    box-shadow: var(--shadow-lg);
    animation: slideInRight 0.3s ease;
    font-weight: 500;
    border: 1px solid #f0f0f0;
    font-size: 0.95rem;
}

@keyframes slideInRight {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

@keyframes slideOutRight {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(100%); opacity: 0; }
}

.notification.success { background: #e8f5e9; border-left-color: #4caf50; color: #2e7d32; }
.notification.warning { background: #fff3e0; border-left-color: #ff9800; color: #ef6c00; }
.notification.error { background: #ffebee; border-left-color: #f44336; color: #c62828; }
.notification i { margin-right: 0.6rem; font-size: 1.1rem; }
.notification.fade-out { animation: slideOutRight 0.3s ease forwards; }

@media (max-width: 991px) {
    .product-hero h1 { font-size: 1.8rem; }
    .product-title { font-size: 1.6rem; }
    .price-current { font-size: 2.2rem; }
    .main-image-container { height: 320px; }
    .col-lg-6:last-child { position: static; }
    .product-main { max-height: none; overflow-y: visible; }
}

@media (max-width: 768px) {
    .product-hero { padding: 1.5rem 0; }
    .product-hero h1 { font-size: 1.6rem; }
    .product-main { padding: 1.2rem; }
    .product-title { font-size: 1.4rem; }
    .price-current { font-size: 2rem; }
    .tallas-grid { grid-template-columns: repeat(4, 1fr); }
    .colores-grid { grid-template-columns: repeat(3, 1fr); }
    .variante-item { flex-direction: column; align-items: flex-start; gap: 0.8rem; }
    .variante-item-cantidad { width: 100%; justify-content: space-between; }
    .notification { min-width: auto; right: 10px; left: 10px; max-width: calc(100% - 20px); }
    .main-image-container { height: 280px; }
    .thumbnail-item { flex: 0 0 55px; height: 55px; }
    .modal-nav { width: 35px; height: 35px; font-size: 1rem; }
}

@media (max-width: 576px) {
    .product-hero h1 { font-size: 1.3rem; }
    .product-title { font-size: 1.2rem; }
    .price-current { font-size: 1.8rem; }
    .tallas-grid { grid-template-columns: repeat(3, 1fr); }
    .colores-grid { grid-template-columns: repeat(2, 1fr); }
    .color-circle { width: 40px; height: 40px; }
    .btn-back { width: 100%; justify-content: center; padding: 0.5rem 1rem; }
    .btn-gold { padding: 0.7rem 1.2rem; font-size: 0.9rem; }
    .main-image-container { height: 220px; }
    .thumbnail-item { flex: 0 0 45px; height: 45px; }
    .modal-nav { width: 30px; height: 30px; font-size: 0.9rem; }
    .modal-close { top: -25px; font-size: 1.3rem; width: 40px; height: 40px; }
}
    </style>
</head>
<body>

<?php include '../include/header.php'; ?>

<section class="about-hero">
    <div class="container text-center">
        <h1 class="fadeInUp">Nuestros <span>Productos</span></h1>
        <div class="gold-line fadeInUp" style="animation-delay: 0.2s;"></div>
        <p class="fadeInUp" style="animation-delay: 0.4s;">
            Ofrecemos uniformes de alta calidad para empresas, instituciones y negocios
        </p>
    </div>
</section>

<main class="section-padding">
    <div class="container">
        <a href="index.php" class="btn-back">
            <i style="text-decoration: none;" class="bi bi-arrow-left"></i> Volver al catálogo
        </a>

        <div class="row g-4">
            <!-- COLUMNA IZQUIERDA - VISOR DE IMÁGENES -->
            <div class="col-lg-6">
                <?php if (!empty($imagenes)): ?>
                    <div class="product-gallery">
                        <div class="main-image-container" onclick="openModal(imagenActual)">
                            <img src="<?php echo $ruta_base . htmlspecialchars($imagenes[0]); ?>" 
                                 alt="Imagen principal del producto" 
                                 class="main-image"
                                 id="mainImage">
                            <div class="zoom-hint">
                                <i class="bi bi-arrows-fullscreen"></i> Click para zoom
                            </div>
                        </div>
                        
                        <?php if (count($imagenes) > 1): ?>
                            <div class="thumbnails-container">
                                <div class="thumbnails-grid">
                                    <?php foreach ($imagenes as $index => $img): ?>
                                        <div class="thumbnail-item <?php echo ($index === 0) ? 'active' : ''; ?>" 
                                             onclick="cambiarImagen(<?php echo $index; ?>)">
                                            <img src="<?php echo $ruta_base . htmlspecialchars($img); ?>" 
                                                 alt="Miniatura <?php echo $index + 1; ?>" 
                                                 class="thumbnail-image">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- COLUMNA DERECHA - DETALLES -->
            <div class="col-lg-6">
                <div class="product-main">
                    <h1 class="product-title"><?php echo htmlspecialchars($titulo); ?></h1>

                    <div class="price-section">
                        <?php if ($descuento > 0): ?>
                            <div class="price-original">
                                <?php echo MONEDA . number_format($precio, 2, '.', ','); ?>
                            </div>
                            <div class="price-current">
                                <?php echo MONEDA . number_format($precio_desc, 2, '.', ','); ?>
                            </div>
                            <span class="discount-badge">
                                <i class="bi bi-tag-fill"></i> Ahorra <?php echo $descuento; ?>%
                            </span>
                        <?php else: ?>
                            <div class="price-current">
                                <?php echo MONEDA . number_format($precio, 2, '.', ','); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="info-note">
                            <i class="bi bi-info-circle-fill"></i>
                            El precio final puede variar según las opciones seleccionadas
                        </div>
                    </div>

                    <div class="description-section">
                        <p><?php echo nl2br(htmlspecialchars($descripcion)); ?></p>
                    </div>

                    <!-- VARIANTES -->
                    <div class="variantes-section" id="variantesSection" style="display:none;">
                        <div class="loading activo" id="loadingVariantes">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p>Cargando opciones disponibles...</p>
                        </div>

                        <div id="variantesContent" style="display:none;">
                            <div id="tallasContainer" style="display:none;">
                                <h4><i class="bi bi-rulers"></i> Selecciona Talla</h4>
                                <div class="tallas-grid" id="tallasGrid"></div>
                            </div>

                            <div id="coloresContainer" style="display:none;">
                                <h4><i class="bi bi-palette"></i> Selecciona Color</h4>
                                <div class="colores-grid" id="coloresGrid"></div>
                            </div>

                            <div class="variante-info" id="varianteInfo">
                                <div class="variante-detalles">
                                    <p><i class="bi bi-rulers"></i> <strong>Talla:</strong> <span id="infoTalla">-</span></p>
                                    <p><i class="bi bi-palette"></i> <strong>Color:</strong> <span id="infoColor">-</span></p>
                                    <p class="variante-precio"><?php echo MONEDA; ?> <span id="infoPrecio">-</span></p>
                                    <p><span class="variante-stock" id="infoStock">-</span></p>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold" for="cantidadVariante">
                                    <i class="bi bi-sort-numeric-up-alt"></i> Cantidad:
                                </label>
                                <input class="form-control quantity-input" 
                                       id="cantidadVariante" 
                                       name="cantidadVariante" 
                                       type="number" 
                                       min="1" 
                                       max="12" 
                                       value="1">
                            </div>

                            <button class="btn-gold w-100" id="btnAddVariante">
                                <i class="bi bi-plus-circle"></i> Agregar esta variante
                            </button>
                        </div>
                    </div>

                    <!-- CARRITO TEMPORAL -->
                    <div class="variantes-carrito" id="variantesCarrito" style="display:none;">
                        <h5><i class="bi bi-bag-check"></i> Variantes seleccionadas</h5>
                        <div id="listaVariantes"></div>
                        <div class="total-variantes">
                            <span>Total:</span> <?php echo MONEDA; ?> <span id="totalVariantes">0.00</span>
                        </div>
                    </div>

                    <button class="btn-gold w-100" id="btnAgregarTodo" style="display:none;">
                        <i class="bi bi-cart-plus"></i> Agregar todas las variantes al carrito
                    </button>

                    <!-- FORMULARIO SIMPLE -->
                    <div id="formSimple" style="display:none;">
                        <div class="form-simple">
                            <div class="mb-4">
                                <label class="form-label fw-bold" for="cantidadSimple">
                                    <i class="bi bi-sort-numeric-up-alt"></i> Cantidad:
                                </label>
                                <input class="form-control quantity-input" 
                                       id="cantidadSimple" 
                                       name="cantidadSimple" 
                                       type="number" 
                                       min="1" 
                                       max="100" 
                                       value="1">
                            </div>
                            <button class="btn-gold w-100" id="btnAgregarSimple">
                                <i class="bi bi-cart-plus"></i> Agregar al carrito
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- MODAL DE ZOOM -->
<div class="image-modal" id="imageModal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal()">
            <i class="bi bi-x-lg"></i>
        </button>
        <button class="modal-nav prev" onclick="navegarImagen(-1)">
            <i class="bi bi-chevron-left"></i>
        </button>
        <img src="" alt="Imagen ampliada" class="modal-image" id="modalImage">
        <button class="modal-nav next" onclick="navegarImagen(1)">
            <i class="bi bi-chevron-right"></i>
        </button>
        <div class="modal-counter" id="modalCounter">
            <span id="imagenActual">1</span> / <span id="totalImagenes"><?php echo count($imagenes); ?></span>
        </div>
    </div>
</div>

<?php include '../include/footer.php'; ?>

<script>
    // ======================================================================
    // VISOR DE IMÁGENES
    // ======================================================================
    
    const imagenes = <?php echo json_encode($imagenes); ?>;
    const rutaBase = '<?php echo $ruta_base; ?>';
    let imagenActual = 0;
    
    function cambiarImagen(index) {
        imagenActual = index;
        document.getElementById('mainImage').src = rutaBase + imagenes[index];
        document.querySelectorAll('.thumbnail-item').forEach((item, i) => {
            item.classList.toggle('active', i === index);
        });
    }
    
    function openModal(index) {
        imagenActual = index;
        document.getElementById('modalImage').src = rutaBase + imagenes[index];
        document.getElementById('imagenActual').textContent = index + 1;
        document.getElementById('totalImagenes').textContent = imagenes.length;
        document.getElementById('imageModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    function closeModal() {
        document.getElementById('imageModal').classList.remove('active');
        document.body.style.overflow = '';
    }
    
    function navegarImagen(direccion) {
        imagenActual = (imagenActual + direccion + imagenes.length) % imagenes.length;
        document.getElementById('modalImage').src = rutaBase + imagenes[imagenActual];
        document.getElementById('imagenActual').textContent = imagenActual + 1;
    }
    
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') closeModal();
        if (document.getElementById('imageModal').classList.contains('active')) {
            if (event.key === 'ArrowLeft') navegarImagen(-1);
            else if (event.key === 'ArrowRight') navegarImagen(1);
        }
    });
    
    document.getElementById('imageModal').addEventListener('click', function(event) {
        if (event.target === this) closeModal();
    });
    
    // ======================================================================
    // VARIANTES Y CARRITO
    // ======================================================================
    
    const idProducto = <?php echo $id; ?>;
    const tokenProducto = '<?php echo $token_tmp; ?>';
    let variantes = [];
    let tallas = [];
    let colores = [];
    let varianteActual = null;
    let variantesSeleccionadas = {};
    let tallaSeleccionada = null;
    let colorSeleccionado = null;

    document.addEventListener('DOMContentLoaded', function() {
        cargarVariantes();
    });

    function cargarVariantes() {
        fetch(`../include/api_variantes.php?action=getVariantes&id_producto=${idProducto}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    variantes = data.variantes;
                    tallas = data.tallas;
                    colores = data.colores;

                    if (variantes.length === 0) {
                        document.getElementById('variantesSection').style.display = 'none';
                        document.getElementById('formSimple').style.display = 'block';
                        document.getElementById('btnAgregarTodo').style.display = 'none';
                    } else {
                        document.getElementById('variantesSection').style.display = 'block';
                        document.getElementById('formSimple').style.display = 'none';
                        document.getElementById('loadingVariantes').classList.remove('activo');
                        document.getElementById('variantesContent').style.display = 'block';
                        document.getElementById('btnAgregarTodo').style.display = 'block';
                        renderTallas();
                        renderColores();
                    }
                } else {
                    document.getElementById('variantesSection').style.display = 'none';
                    document.getElementById('formSimple').style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('variantesSection').style.display = 'none';
                document.getElementById('formSimple').style.display = 'block';
            });
    }

    function renderTallas() {
        const container = document.getElementById('tallasGrid');
        container.innerHTML = '';
        if (tallas.length === 0) { document.getElementById('tallasContainer').style.display = 'none'; return; }
        document.getElementById('tallasContainer').style.display = 'block';
        tallas.forEach(talla => {
            const btn = document.createElement('button');
            btn.className = 'talla-btn';
            btn.textContent = talla.nombre;
            btn.type = 'button';
            btn.onclick = () => seleccionarTalla(talla.id, btn);
            container.appendChild(btn);
        });
    }

    function renderColores(coloresDisponibles = null) {
        const container = document.getElementById('coloresGrid');
        container.innerHTML = '';
        const listaColores = coloresDisponibles || colores;
        if (listaColores.length === 0) { document.getElementById('coloresContainer').style.display = 'none'; return; }
        document.getElementById('coloresContainer').style.display = 'block';
        listaColores.forEach(color => {
            const btn = document.createElement('div');
            btn.className = 'color-btn';
            btn.onclick = () => seleccionarColor(color.id, btn);
            const circle = document.createElement('div');
            circle.className = 'color-circle';
            circle.style.backgroundColor = color.codigo_hex;
            const nombre = document.createElement('div');
            nombre.className = 'color-name';
            nombre.textContent = color.nombre;
            btn.appendChild(circle);
            btn.appendChild(nombre);
            container.appendChild(btn);
        });
    }

    function seleccionarTalla(idTalla, elemento) {
        tallaSeleccionada = idTalla;
        document.querySelectorAll('.talla-btn').forEach(btn => btn.classList.remove('active'));
        elemento.classList.add('active');
        filtrarVariantes();
    }

    function seleccionarColor(idColor, elemento) {
        colorSeleccionado = idColor;
        document.querySelectorAll('.color-btn').forEach(btn => btn.classList.remove('active'));
        elemento.classList.add('active');
        filtrarVariantes();
    }

    function filtrarVariantes() {
        let url = `../include/api_variantes.php?action=filtrarVariantes&id_producto=${idProducto}`;
        if (tallaSeleccionada) url += `&id_talla=${tallaSeleccionada}`;
        if (colorSeleccionado) url += `&id_color=${colorSeleccionado}`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    variantes = data.variantes;
                    if (tallaSeleccionada && data.colores_disponibles) {
                        renderColores(data.colores_disponibles);
                        if (colorSeleccionado) buscarVariante(tallaSeleccionada, colorSeleccionado);
                    } else if (colorSeleccionado && data.tallas_disponibles) {
                        renderTallas();
                        if (tallaSeleccionada) buscarVariante(tallaSeleccionada, colorSeleccionado);
                    } else if (tallaSeleccionada || colorSeleccionado) {
                        buscarVariante(tallaSeleccionada, colorSeleccionado);
                    }
                }
            })
            .catch(error => console.error('Error:', error));
    }

    function buscarVariante(idTalla, idColor) {
        const variante = variantes.find(v => 
            (idTalla ? v.id_talla == idTalla : !v.id_talla) && 
            (idColor ? v.id_color == idColor : !v.id_color)
        );
        if (variante) { varianteActual = variante; mostrarVariante(variante); }
        else { document.getElementById('varianteInfo').classList.remove('activa'); }
    }

    function mostrarVariante(variante) {
        document.getElementById('infoTalla').textContent = variante.talla_nombre || 'Sin talla';
        document.getElementById('infoColor').textContent = variante.color_nombre || 'Sin color';
        document.getElementById('infoPrecio').textContent = parseFloat(variante.precio).toFixed(2);

        const stockInfo = document.getElementById('infoStock');
        if (variante.stock > 0) {
            stockInfo.className = 'variante-stock disponible';
            stockInfo.innerHTML = '<i class="bi bi-check-circle"></i> ' + variante.stock + ' en stock';
        } else {
            stockInfo.className = 'variante-stock agotado';
            stockInfo.innerHTML = '<i class="bi bi-exclamation-circle"></i> Sin stock';
        }

        document.getElementById('cantidadVariante').value = 1;
        document.getElementById('varianteInfo').classList.add('activa');
        document.getElementById('btnAddVariante').disabled = variante.stock === 0;
    }

    document.getElementById('btnAddVariante').addEventListener('click', function() {
        if (!varianteActual) { showNotification('Por favor selecciona una talla y color', 'warning'); return; }
        const cantidad = parseInt(document.getElementById('cantidadVariante').value);
        if (cantidad < 1 || cantidad > varianteActual.stock) { showNotification('Cantidad inválida o superior al stock disponible', 'error'); return; }

        const clave = `variante_${varianteActual.variante_id}`;
        if (variantesSeleccionadas[clave]) {
            variantesSeleccionadas[clave].cantidad += cantidad;
        } else {
            variantesSeleccionadas[clave] = {
                variante_id: varianteActual.variante_id,
                id_producto: idProducto,
                cantidad: cantidad,
                precio: parseFloat(varianteActual.precio),
                talla: varianteActual.talla_nombre || 'Sin talla',
                color: varianteActual.color_nombre || 'Sin color'
            };
        }

        mostrarVariantesSeleccionadas();
        showNotification('✓ ' + cantidad + ' variante(s) agregada(s)', 'success');
        
        document.querySelectorAll('.talla-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.color-btn').forEach(btn => btn.classList.remove('active'));
        tallaSeleccionada = null;
        colorSeleccionado = null;
        varianteActual = null;
        document.getElementById('varianteInfo').classList.remove('activa');
    });

    function mostrarVariantesSeleccionadas() {
        const lista = document.getElementById('listaVariantes');
        lista.innerHTML = '';
        let total = 0;

        Object.entries(variantesSeleccionadas).forEach(([clave, item]) => {
            const div = document.createElement('div');
            div.className = 'variante-item';
            div.innerHTML = `
                <div class="variante-item-info">
                    <div class="variante-item-titulo">${item.talla} - ${item.color}</div>
                    <div class="variante-item-precio"><?php echo MONEDA; ?>${item.precio.toFixed(2)} c/u</div>
                </div>
                <div class="variante-item-cantidad">
                    <input type="number" value="${item.cantidad}" min="1" 
                           onchange="actualizarCantidadVariante('${clave}', this.value)">
                    <button class="btn-remove-variante" onclick="eliminarVariante('${clave}')">
                        <i class="bi bi-trash"></i> Quitar
                    </button>
                </div>
            `;
            lista.appendChild(div);
            total += item.precio * item.cantidad;
        });

        if (Object.keys(variantesSeleccionadas).length > 0) {
            document.getElementById('variantesCarrito').style.display = 'block';
            document.getElementById('totalVariantes').textContent = total.toFixed(2);
        } else {
            document.getElementById('variantesCarrito').style.display = 'none';
        }
    }

    function actualizarCantidadVariante(clave, cantidad) {
        cantidad = parseInt(cantidad);
        if (cantidad < 1) { eliminarVariante(clave); }
        else { variantesSeleccionadas[clave].cantidad = cantidad; mostrarVariantesSeleccionadas(); }
    }

    function eliminarVariante(clave) {
        delete variantesSeleccionadas[clave];
        mostrarVariantesSeleccionadas();
        showNotification('Variante eliminada', 'success');
    }

    // ✅ CORRECCIÓN: btnAgregarTodo — usa window.actualizarBadgeCarrito
    document.getElementById('btnAgregarTodo').addEventListener('click', function() {
        const cantidad = Object.keys(variantesSeleccionadas).length;
        if (cantidad === 0) { showNotification('Selecciona al menos una variante', 'warning'); return; }

        let promesas = Object.values(variantesSeleccionadas).map(item => {
            const formData = new FormData();
            formData.append('action', 'agregarVariante');
            formData.append('variante_id', item.variante_id);
            formData.append('id_producto', item.id_producto);
            formData.append('cantidad', item.cantidad);
            return fetch('../include/carrito_variantes.php', { method: 'POST', body: formData }).then(r => r.json());
        });

        Promise.all(promesas).then(resultados => {
            const allSuccess = resultados.every(r => r.ok);
            if (allSuccess) {
                const ultimoResultado = resultados[resultados.length - 1];

                // ✅ Actualiza badge desktop + mobile del header
                if (window.actualizarBadgeCarrito) {
                    window.actualizarBadgeCarrito(ultimoResultado.numero);
                }

                showNotification('✓ ' + cantidad + ' variante(s) agregada(s) al carrito', 'success');
                variantesSeleccionadas = {};
                mostrarVariantesSeleccionadas();
                tallaSeleccionada = null;
                colorSeleccionado = null;
                document.querySelectorAll('.talla-btn').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.color-btn').forEach(btn => btn.classList.remove('active'));
            } else {
                showNotification('Error al agregar algunas variantes', 'error');
            }
        }).catch(error => {
            console.error('Error:', error);
            showNotification('Error al agregar al carrito', 'error');
        });
    });

    // ✅ CORRECCIÓN: btnAgregarSimple — usa window.actualizarBadgeCarrito
    document.getElementById('btnAgregarSimple').addEventListener('click', function() {
        const cantidad = parseInt(document.getElementById('cantidadSimple').value);
        if (cantidad < 1 || cantidad > 100) { showNotification('Cantidad inválida', 'error'); return; }

        const formData = new FormData();
        formData.append('id', idProducto);
        formData.append('token', tokenProducto);
        formData.append('cantidad', cantidad);

        fetch('../include/carrito.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                // ✅ Actualiza badge desktop + mobile del header
                if (window.actualizarBadgeCarrito) {
                    window.actualizarBadgeCarrito(data.numero);
                }
                showNotification('✓ Producto agregado (' + cantidad + ' unidad/es)', 'success');
                document.getElementById('cantidadSimple').value = 1;
            } else {
                showNotification('Error al agregar el producto', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error al agregar al carrito', 'error');
        });
    });

    function showNotification(message, type = 'success') {
        document.querySelectorAll('.notification').forEach(n => n.remove());
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        let icon = 'check-circle-fill';
        if (type === 'warning') icon = 'exclamation-triangle-fill';
        if (type === 'error') icon = 'exclamation-octagon-fill';
        notification.innerHTML = `<i class="bi bi-${icon}"></i> ${message}`;
        document.body.appendChild(notification);
        setTimeout(() => {
            notification.classList.add('fade-out');
            setTimeout(() => { if (notification.parentNode) notification.remove(); }, 300);
        }, 3500);
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>