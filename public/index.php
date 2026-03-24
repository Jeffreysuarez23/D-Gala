<?php
#ya volvistes
require_once '../include/db.php';
require_once '../include/configuraciones.php';
 
// Obtener los productos activos para mostrarlos
$db = new Database();
$conexion = $db->getConexion();
 
$idCategoria = isset($_GET['cat']) ? (int)$_GET['cat'] : null;
$orden = isset($_GET['orden']) ? $_GET['orden'] : null;
 
$orders = [
    'asc' => 'titulo ASC',
    'desc' => 'titulo DESC',
    'precio_bajo' => 'precio ASC',
    'precio_alto' => 'precio DESC'
];
$order = $orders[$orden] ?? '';
 
if(!empty($orden)){
    $orden = "ORDER BY $order";
}
 
if (!empty($idCategoria)) {
    $sql = $conexion->prepare("SELECT id, titulo, precio, imagen, id_categoria FROM productos WHERE activo = 1 AND id_categoria = ? $orden ");
    $sql->execute([$idCategoria]);
} else {
    $sql = $conexion->prepare("SELECT id, titulo, precio, imagen, id_categoria FROM productos WHERE activo = 1 $orden ");
    $sql->execute();
}
 
$resultado = $sql->fetchAll(PDO::FETCH_ASSOC); 
 
$sqlCategorias = $conexion->prepare("SELECT id, nombre FROM caracteristicas WHERE activo = 1");
$sqlCategorias->execute();
$categorias = $sqlCategorias->fetchAll(PDO::FETCH_ASSOC);
 
// Si es una petición AJAX, solo devolvemos los productos
if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    
    $productosHTML = '';
    
    if (empty($resultado)) {
        $productosHTML = '<div class="col-12">
                            <div class="empty-products">
                                <i class="bi bi-inbox"></i>
                                <p>No hay productos disponibles en esta categoría</p>
                            </div>
                        </div>';
    } else {
        $rutaBase = '../assets/img/';
        foreach ($resultado as $row) {
            $imagen = $row['imagen'] ? $rutaBase . $row['imagen'] : $rutaBase . 'noadd.jpg';
            $token_tmp = hash_hmac('sha1', $row['id'], KEY_TOKEN);
            
            // Obtener nombre de categoría
            $categoriaNombre = '';
            foreach ($categorias as $cat) {
                if ($cat['id'] == $row['id_categoria']) {
                    $categoriaNombre = $cat['nombre'];
                    break;
                }
            }
            
            $productosHTML .= '<div class="col-6 col-md-4 col-lg-3">
                                <div class="card product-card h-100 border-0 shadow-sm">
                                    ' . ($categoriaNombre ? '<div class="product-badge">' . htmlspecialchars($categoriaNombre) . '</div>' : '') . '
                                    <div class="product-image">
                                        <img src="' . $imagen . '" class="card-img-top" alt="' . htmlspecialchars($row['titulo']) . '" loading="lazy">
                                    </div>
                                    <div class="card-body d-flex flex-column p-2 p-sm-3">
                                        <h3 class="product-title h6 mb-2">' . htmlspecialchars($row['titulo']) . '</h3>
                                        <div class="product-price mt-auto mb-2 mb-sm-3">
                                            <span class="price-current h5 fw-bold">$' . number_format($row['precio'], 2, '.', ',') . '</span>
                                        </div>
                                        <div class="product-actions d-flex gap-2">
                                            <a href="detalles.php?id=' . $row['id'] . '&token=' . $token_tmp . '" 
                                               class="btn-details flex-grow-1" 
                                               title="Ver detalles">
                                                <i class="bi bi-eye"></i>
                                                <span class="d-none d-sm-inline"> Detalles</span>
                                            </a>
                                            <button class="btn-cart flex-grow-1" 
                                                    onclick="addProducto(' . $row['id'] . ', \'' . $token_tmp . '\')" 
                                                    title="Agregar al carrito">
                                                <i class="bi bi-cart-plus"></i>
                                                <span class="d-none d-sm-inline"> Agregar</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>';
        }
    }
    
    echo json_encode([
        'success' => true,
        'html' => $productosHTML,
        'total' => count($resultado)
    ]);
    exit;
}
?>
 
<!DOCTYPE html>
<html lang="es" data-bs-theme="auto">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
    <title>Tienda Online</title>
    <?php include '../include/links.php'; ?>
    <meta name="theme-color" content="#000000">
    <style>
        /* Reset y variables globales */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
 
        :root {
            --primary-black: #000000;
            --primary-gold: #FFD700;
            --text-light: #ffffff;
            --text-dark: #333333;
            --gray-light: #f8f9fa;
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
        }
 
        html {
            font-size: 16px;
            scroll-behavior: smooth;
            overflow-x: hidden;
            width: 100%;
        }
 
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-dark);
            background: #f5f5f5;
            overflow-x: hidden;
            width: 100%;
            position: relative;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
 
        /* Contenedor principal responsive */
        .container-fluid {
            width: 100%;
            padding-right: 15px;
            padding-left: 15px;
            margin-right: auto;
            margin-left: auto;
        }
 
        @media (min-width: 576px) {
            .container-fluid {
                padding-right: 20px;
                padding-left: 20px;
            }
        }
 
        @media (min-width: 768px) {
            .container-fluid {
                padding-right: 25px;
                padding-left: 25px;
            }
        }
 
        @media (min-width: 992px) {
            .container-fluid {
                padding-right: 30px;
                padding-left: 30px;
            }
        }
 
        @media (min-width: 1200px) {
            .container-fluid {
                padding-right: 35px;
                padding-left: 35px;
            }
        }
 
        /* Text color utilities */
        .text-gold {
            color: var(--primary-gold) !important;
        }
        .bg-gold {
            background-color: var(--primary-gold) !important;
        }
        .border-gold {
            border-color: var(--primary-gold) !important;
        }
 
        /* Hero Section - 100% responsive */
        .about-hero {
            background: linear-gradient(135deg, rgba(0,0,0,0.95) 0%, rgba(0,0,0,0.8) 100%), url('../assets/img/uniformes-bg.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: scroll;
            padding: 3rem 1rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
            width: 100%;
        }
 
        @media (min-width: 768px) {
            .about-hero {
                padding: 5rem 2rem;
                margin-bottom: 2.5rem;
                background-attachment: fixed;
            }
        }
 
        @media (min-width: 992px) {
            .about-hero {
                padding: 6rem 2rem;
                margin-bottom: 3rem;
            }
        }
 
        .about-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 215, 0, 0.1), transparent);
            animation: shine 8s infinite;
        }
 
        .about-hero h1 {
            color: var(--text-light);
            font-size: clamp(1.5rem, 6vw, 2.5rem);
            font-weight: 700;
            margin-bottom: 0.75rem;
            position: relative;
            word-break: break-word;
            line-height: 1.2;
        }
 
        @media (min-width: 768px) {
            .about-hero h1 {
                margin-bottom: 1rem;
            }
        }
 
        .about-hero h1 span {
            color: var(--primary-gold);
        }
 
        .about-hero p {
            color: rgba(255,255,255,0.9);
            font-size: clamp(0.9rem, 4vw, 1.2rem);
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            padding: 0 0.5rem;
            word-break: break-word;
            line-height: 1.5;
        }
 
        .gold-line {
            width: 60px;
            height: 3px;
            background: var(--primary-gold);
            margin: 1.25rem auto;
            position: relative;
        }
 
        @media (min-width: 768px) {
            .gold-line {
                width: 80px;
                height: 4px;
                margin: 1.5rem auto;
            }
        }
 
        @media (min-width: 992px) {
            .gold-line {
                width: 100px;
                margin: 2rem auto;
            }
        }
 
        /* ========== FILTROS 100% RESPONSIVE ========== */
        .filters-section {
            background: white;
            border-radius: 16px;
            box-shadow: var(--shadow-md);
            overflow: hidden;
            position: sticky;
            top: 80px;
            width: 100%;
            max-height: calc(100vh - 100px);
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }
 
        @media (max-width: 991px) {
            .filters-section {
                position: static;
                margin-bottom: 1.5rem;
                max-height: none;
                overflow-y: visible;
                border-radius: 12px;
            }
        }
 
        .filters-header {
            background: var(--primary-black);
            color: white;
            padding: 1rem 1.25rem;
            border-bottom: 2px solid var(--primary-gold);
        }
 
        @media (min-width: 768px) {
            .filters-header {
                padding: 1.25rem 1.5rem;
                border-bottom-width: 3px;
            }
        }
 
        .filters-header h5 {
            margin: 0;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: clamp(1rem, 4vw, 1.2rem);
            color: var(--primary-gold);
            word-break: break-word;
        }
 
        .filters-body {
            padding: 1rem 1.25rem;
        }
 
        @media (min-width: 768px) {
            .filters-body {
                padding: 1.25rem 1.5rem;
            }
        }
 
        /* Buscador responsive */
        .search-box {
            margin-bottom: 1.25rem;
        }
 
        .search-box .input-group {
            border-radius: 10px;
            overflow: hidden;
            border: 2px solid #e0e0e0;
            transition: var(--transition);
            width: 100%;
        }
 
        .search-box .input-group:focus-within {
            border-color: var(--primary-gold);
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.1);
        }
 
        .search-box .input-group-text {
            background: white;
            border: none;
            color: #999;
            padding: 0.5rem 0.75rem;
        }
 
        .search-box .form-control {
            border: none;
            padding: 0.5rem 0.5rem 0.5rem 0;
            font-size: 0.9rem;
            height: auto;
        }
 
        @media (min-width: 768px) {
            .search-box .form-control {
                font-size: 0.95rem;
                padding: 0.6rem 0.5rem 0.6rem 0;
            }
        }
 
        .search-box .form-control:focus {
            box-shadow: none;
        }
 
        .search-box .form-control::placeholder {
            font-size: 0.85rem;
        }
 
        /* Sección de filtros responsive */
        .filter-section {
            margin-bottom: 1.5rem;
        }
 
        .filter-section:last-child {
            margin-bottom: 0;
        }
 
        .filter-title {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--primary-black);
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            word-break: break-word;
        }
 
        @media (min-width: 768px) {
            .filter-title {
                font-size: 0.85rem;
                margin-bottom: 0.9rem;
                gap: 0.5rem;
            }
        }
 
        .filter-title i {
            color: var(--primary-gold);
            font-size: 0.85rem;
            flex-shrink: 0;
        }
 
        /* Categorías responsive */
        .categories-list {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
        }
 
        .category-item {
            display: flex;
            align-items: center;
            padding: 0.6rem 0.75rem;
            border-radius: 8px;
            color: var(--text-dark);
            text-decoration: none;
            transition: var(--transition);
            cursor: pointer;
            border: 2px solid transparent;
            background: white;
            width: 100%;
            word-break: break-word;
        }
 
        @media (min-width: 768px) {
            .category-item {
                padding: 0.7rem 1rem;
                border-radius: 10px;
            }
        }
 
        .category-item i {
            margin-right: 0.6rem;
            color: var(--primary-gold);
            font-size: 0.9rem;
            transition: var(--transition);
            flex-shrink: 0;
        }
 
        .category-item .category-name {
            flex: 1;
            font-size: 0.85rem;
            font-weight: 500;
            line-height: 1.3;
        }
 
        @media (min-width: 768px) {
            .category-item .category-name {
                font-size: 0.9rem;
            }
        }
 
        .category-item .category-count {
            background: var(--gray-light);
            padding: 0.15rem 0.5rem;
            border-radius: 20px;
            font-size: 0.65rem;
            font-weight: 600;
            color: #666;
            margin-left: 0.5rem;
            flex-shrink: 0;
        }
 
        @media (min-width: 768px) {
            .category-item .category-count {
                font-size: 0.7rem;
                padding: 0.2rem 0.6rem;
            }
        }
 
        .category-item:hover {
            background: rgba(255, 215, 0, 0.05);
            border-color: rgba(255, 215, 0, 0.3);
            transform: translateX(2px);
        }
 
        .category-item.active {
            background: linear-gradient(135deg, var(--primary-black) 0%, #1a1a1a 100%);
            color: var(--primary-gold);
        }
 
        /* Rango de precios responsive */
        .price-range {
            padding: 0.25rem 0;
            width: 100%;
        }
 
        .price-inputs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
            flex-wrap: wrap;
        }
 
        .price-input {
            flex: 1 1 calc(50% - 0.25rem);
            min-width: 80px;
            position: relative;
        }
 
        .price-input span {
            position: absolute;
            left: 6px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 0.75rem;
            font-weight: 500;
        }
 
        @media (min-width: 768px) {
            .price-input span {
                left: 8px;
                font-size: 0.8rem;
            }
        }
 
        .price-input input {
            width: 100%;
            padding: 0.5rem 0.5rem 0.5rem 1.3rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.8rem;
            transition: var(--transition);
            height: 38px;
        }
 
        @media (min-width: 768px) {
            .price-input input {
                padding: 0.55rem 0.5rem 0.55rem 1.5rem;
                font-size: 0.85rem;
                height: 40px;
            }
        }
 
        .price-input input:focus {
            outline: none;
            border-color: var(--primary-gold);
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.1);
        }
 
        .price-input input::placeholder {
            font-size: 0.75rem;
        }
 
        .btn-apply {
            width: 100%;
            padding: 0.6rem;
            background: var(--primary-black);
            color: white;
            border: 2px solid var(--primary-black);
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            transition: var(--transition);
            cursor: pointer;
        }
 
        @media (min-width: 768px) {
            .btn-apply {
                padding: 0.65rem;
                font-size: 0.9rem;
            }
        }
 
        .btn-apply:hover {
            background: var(--primary-gold);
            color: var(--primary-black);
            border-color: var(--primary-gold);
        }
 
        /* Barra de ordenamiento responsive */
        .sort-section {
            background: white;
            padding: 1rem 1.25rem;
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
            border: 2px solid transparent;
            transition: var(--transition);
        }
 
        @media (min-width: 768px) {
            .sort-section {
                padding: 1.25rem 1.5rem;
                margin-bottom: 2rem;
                border-radius: 18px;
            }
        }
 
        .sort-section:hover {
            border-color: rgba(255, 215, 0, 0.3);
        }
 
        .results-info {
            color: #666;
            font-size: 0.85rem;
        }
 
        @media (min-width: 768px) {
            .results-info {
                font-size: 0.9rem;
            }
        }
 
        .results-info strong {
            color: var(--primary-black);
            font-size: clamp(0.9rem, 4vw, 1.1rem);
        }
 
        .sort-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
            width: 100%;
        }
 
        @media (min-width: 576px) {
            .sort-controls {
                width: auto;
                gap: 0.75rem;
            }
        }
 
        .sort-label {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            color: #666;
            font-size: 0.85rem;
        }
 
        .sort-label i {
            color: var(--primary-gold);
            font-size: 0.9rem;
        }
 
        .sort-select {
            padding: 0.5rem 2rem 0.5rem 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 0.85rem;
            color: var(--text-dark);
            background: white;
            cursor: pointer;
            transition: var(--transition);
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%23333' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.6rem center;
            background-size: 14px;
            width: 100%;
        }
 
        @media (min-width: 576px) {
            .sort-select {
                width: auto;
                min-width: 160px;
                font-size: 0.9rem;
                padding: 0.55rem 2.2rem 0.55rem 1rem;
            }
        }
 
        .sort-select:focus {
            outline: none;
            border-color: var(--primary-gold);
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.1);
        }
 
        /* Tarjetas de producto responsive */
        .product-card {
            transition: var(--transition);
            overflow: hidden;
            border-radius: 16px !important;
            border: 2px solid transparent !important;
            height: 100%;
        }
 
        @media (min-width: 768px) {
            .product-card {
                border-radius: 18px !important;
            }
        }
 
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg) !important;
            border-color: var(--primary-gold) !important;
        }
 
        .product-badge {
            position: absolute;
            top: 0.75rem;
            left: 0.75rem;
            background: var(--primary-gold);
            color: var(--primary-black);
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.65rem;
            font-weight: 600;
            z-index: 2;
            box-shadow: var(--shadow-sm);
            border: 2px solid white;
        }
 
        @media (min-width: 768px) {
            .product-badge {
                top: 1rem;
                left: 1rem;
                padding: 0.25rem 0.8rem;
                font-size: 0.7rem;
            }
        }
 
        .product-image {
            position: relative;
            padding-top: 100%;
            overflow: hidden;
            background: #f5f5f5;
        }
 
        .product-image img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }
 
        .product-card:hover .product-image img {
            transform: scale(1.08);
        }
 
        .product-title {
            font-size: clamp(0.75rem, 3vw, 0.95rem);
            font-weight: 600;
            color: var(--primary-black);
            margin-bottom: 0.4rem;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 2.2rem;
        }
 
        @media (min-width: 768px) {
            .product-title {
                font-size: clamp(0.85rem, 2.5vw, 1rem);
                min-height: 2.5rem;
                margin-bottom: 0.5rem;
            }
        }
 
        .product-price {
            margin-bottom: 0.75rem;
        }
 
        .price-current {
            font-size: clamp(0.95rem, 4vw, 1.2rem);
            font-weight: 700;
            color: var(--primary-gold);
            text-shadow: 1px 1px 0 rgba(0,0,0,0.05);
        }
 
        @media (min-width: 768px) {
            .price-current {
                font-size: clamp(1.1rem, 3vw, 1.3rem);
            }
        }
 
        /* Botones responsive */
        .product-actions {
            display: flex;
            gap: 0.4rem;
            margin-top: auto;
        }
 
        .btn-details, .btn-cart {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.2rem;
            padding: 0.4rem 0.3rem;
            border-radius: 8px;
            font-size: 0.7rem;
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
            cursor: pointer;
            border: 2px solid;
            min-height: 32px;
        }
 
        @media (min-width: 768px) {
            .btn-details, .btn-cart {
                padding: 0.5rem 0.4rem;
                font-size: 0.8rem;
                gap: 0.3rem;
                min-height: 36px;
            }
        }
 
        .btn-details {
            background: var(--gray-light);
            color: var(--text-dark);
            border-color: #dee2e6;
        }
 
        .btn-details:hover {
            background: #e9ecef;
            border-color: #adb5bd;
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
            color: var(--text-dark);
        }
 
        .btn-cart {
            background: linear-gradient(135deg, var(--primary-black) 0%, #1a1a1a 100%);
            color: var(--primary-gold);
            border: none;
        }
 
        .btn-cart:hover {
            background: var(--primary-gold);
            color: var(--primary-black);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 215, 0, 0.3);
        }
 
        .btn-details i, .btn-cart i {
            font-size: 0.8rem;
        }
 
        @media (min-width: 768px) {
            .btn-details i, .btn-cart i {
                font-size: 0.9rem;
            }
        }
 
        /* Loader */
        .products-loader {
            text-align: center;
            padding: 2rem 1rem;
            display: none;
        }
 
        .products-loader.active {
            display: block;
        }
 
        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #f3f3f3;
            border-top-color: var(--primary-gold);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 0.75rem;
        }
 
        @media (min-width: 768px) {
            .spinner {
                width: 45px;
                height: 45px;
                border-width: 4px;
            }
        }
 
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
 
        /* Mensaje sin productos */
        .empty-products {
            background: white;
            border-radius: 24px;
            padding: 2.5rem 1rem;
            text-align: center;
            box-shadow: var(--shadow-md);
            border: 2px dashed var(--primary-gold);
        }
 
        @media (min-width: 768px) {
            .empty-products {
                padding: 3rem 2rem;
                border-radius: 28px;
            }
        }
 
        .empty-products i {
            font-size: clamp(2.5rem, 10vw, 3.5rem);
            color: var(--primary-gold);
            margin-bottom: 0.75rem;
        }
 
        .empty-products p {
            font-size: clamp(0.9rem, 4vw, 1.1rem);
            color: #666;
        }
 
        /* Notificaciones responsive */
        .custom-notification {
            position: fixed;
            top: 70px;
            right: 15px;
            left: 15px;
            background: white;
            color: var(--text-dark);
            padding: 0.75rem 1rem;
            border-radius: 40px;
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            z-index: 9999;
            animation: slideInRight 0.3s ease forwards;
            border-left: 4px solid var(--primary-gold);
            border: 2px solid var(--primary-gold);
            max-width: none;
            font-weight: 500;
            font-size: 0.9rem;
        }
 
        @media (min-width: 576px) {
            .custom-notification {
                top: 80px;
                right: 20px;
                left: auto;
                width: auto;
                max-width: 350px;
                padding: 0.9rem 1.3rem;
            }
        }
 
        .custom-notification i {
            font-size: 1.2rem;
            color: var(--primary-gold);
        }
 
        /* Botón colapsable de filtros */
        .btn-filter-toggle {
            background: var(--primary-black);
            color: white;
            border: 2px solid var(--primary-gold);
            padding: 0.7rem;
            border-radius: 12px;
            font-weight: 600;
            transition: var(--transition);
            font-size: 0.9rem;
        }
 
        .btn-filter-toggle:hover {
            background: var(--primary-gold);
            color: var(--primary-black);
            border-color: var(--primary-black);
        }
 
        .btn-filter-toggle i {
            color: var(--primary-gold);
            transition: var(--transition);
            font-size: 1rem;
        }
 
        .btn-filter-toggle:hover i {
            color: var(--primary-black);
        }
 
        /* Ajustes de grid */
        .g-3, .gy-3 {
            --bs-gutter-y: 0.75rem;
        }
 
        @media (min-width: 768px) {
            .g-3, .gy-3 {
                --bs-gutter-y: 1rem;
            }
        }
        
        .row {
            margin-right: -0.35rem;
            margin-left: -0.35rem;
        }
        
        .row > * {
            padding-right: 0.35rem;
            padding-left: 0.35rem;
        }
 
        @media (min-width: 768px) {
            .row {
                margin-right: -0.5rem;
                margin-left: -0.5rem;
            }
            
            .row > * {
                padding-right: 0.5rem;
                padding-left: 0.5rem;
            }
        }
 
        /* Espaciado general */
        .section-padding {
            padding: 1.5rem 0;
        }
 
        @media (min-width: 768px) {
            .section-padding {
                padding: 2rem 0;
            }
        }
 
        @media (min-width: 992px) {
            .section-padding {
                padding: 2.5rem 0;
            }
        }
 
        /* Animaciones */
        @keyframes shine {
            0% { left: -100%; }
            20% { left: 100%; }
            100% { left: 100%; }
        }
 
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
 
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
 
        .fadeInUp {
            animation: fadeInUp 0.5s ease forwards;
        }
 
        /* Correcciones para móviles muy pequeños */
        @media (max-width: 360px) {
            .product-actions {
                flex-direction: column;
            }
            
            .btn-details, .btn-cart {
                width: 100%;
            }
            
            .sort-select {
                font-size: 0.8rem;
            }
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
        <div class="container-fluid">
            <div class="row g-4">
                <!-- Sidebar de filtros -->
                <div class="col-12 col-lg-3">
                    <!-- Botón para móvil -->
                    <div class="d-lg-none mb-3">
                        <button class="btn-filter-toggle w-100 d-flex align-items-center justify-content-center gap-2" 
                                type="button" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#filtersCollapse">
                            <i class="bi bi-sliders2"></i>
                            Mostrar filtros
                        </button>
                    </div>
                    
                    <!-- Contenido de filtros -->
                    <div class="collapse d-lg-block" id="filtersCollapse">
                        <div class="filters-section">
                            <div class="filters-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-sliders2"></i>
                                    Filtros
                                </h5>
                            </div>
                            <div class="filters-body">
                                <!-- Buscador -->
                                <div class="search-box">
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-search"></i>
                                        </span>
                                        <input type="text" 
                                               class="form-control" 
                                               id="searchInput"
                                               placeholder="Buscar productos...">
                                    </div>
                                </div>
 
                                <!-- Categorías -->
                                <div class="filter-section">
                                    <div class="filter-title">
                                        <i class="bi bi-grid"></i>
                                        Categorías
                                    </div>
                                    <div class="categories-list">
                                        <div class="category-item filter-category <?php echo ($idCategoria === null) ? 'active' : ''; ?>" 
                                             data-categoria=""
                                             onclick="filterByCategory('')">
                                            <i class="bi bi-grid-3x2-gap"></i>
                                            <span class="category-name">Todos los productos</span>
                                            <span class="category-count"><?php echo count($resultado); ?></span>
                                        </div>
                                        <?php foreach ($categorias as $categoria): 
                                            $count = 0;
                                            foreach ($resultado as $prod) {
                                                if ($prod['id_categoria'] == $categoria['id']) $count++;
                                            }
                                        ?>
                                            <div class="category-item filter-category <?php echo ($idCategoria == $categoria['id']) ? 'active' : ''; ?>"
                                                 data-categoria="<?php echo $categoria['id']; ?>"
                                                 onclick="filterByCategory(<?php echo $categoria['id']; ?>)">
                                                <i class="bi bi-tag"></i>
                                                <span class="category-name"><?php echo htmlspecialchars($categoria['nombre']); ?></span>
                                                <span class="category-count"><?php echo $count; ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
 
                                <!-- Rango de precios -->
                                <div class="filter-section">
                                    <div class="filter-title">
                                        <i class="bi bi-currency-dollar"></i>
                                        Rango de precio
                                    </div>
                                    <div class="price-range">
                                        <div class="price-inputs">
                                            <div class="price-input">
                                                <span>$</span>
                                                <input type="number" id="minPrice" class="form-control" placeholder="Mín" min="0">
                                            </div>
                                            <div class="price-input">
                                                <span>$</span>
                                                <input type="number" id="maxPrice" class="form-control" placeholder="Máx" min="0">
                                            </div>
                                        </div>
                                        <button class="btn-apply" onclick="applyPriceFilter()">
                                            Aplicar filtro
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
 
                <!-- Grid de productos -->
                <div class="col-12 col-lg-9">
                    <!-- Barra de ordenamiento -->
                    <div class="sort-section">
                        <div class="results-info">
                            <strong id="productsCount"><?php echo count($resultado); ?></strong> productos encontrados
                        </div>
                        <div class="sort-controls">
                            <span class="sort-label">
                                <i class="bi bi-arrow-down-up"></i>
                                Ordenar:
                            </span>
                            <select id="orden" class="sort-select" onchange="filterByOrder(this.value)">
                                <option value="">Seleccionar</option>
                                <option value="asc" <?php echo ($orden === 'asc') ? 'selected' : ''; ?>>Nombre A - Z</option>
                                <option value="desc" <?php echo ($orden === 'desc') ? 'selected' : ''; ?>>Nombre Z - A</option>
                                <option value="precio_bajo" <?php echo ($orden === 'precio_bajo') ? 'selected' : ''; ?>>Precio: Menor a Mayor</option>
                                <option value="precio_alto" <?php echo ($orden === 'precio_alto') ? 'selected' : ''; ?>>Precio: Mayor a Menor</option>
                            </select>
                        </div>
                    </div>
 
                    <!-- Loader -->
                    <div class="products-loader" id="productsLoader">
                        <div class="spinner"></div>
                        <p class="text-muted">Cargando productos...</p>
                    </div>
 
                    <!-- Contenedor de productos -->
                    <div id="productsContainer">
                        <?php if (empty($resultado)): ?>
                            <div class="row">
                                <div class="col-12">
                                    <div class="empty-products">
                                        <i class="bi bi-inbox"></i>
                                        <p class="mb-0">No hay productos disponibles en esta categoría</p>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="row g-3">
                                <?php foreach ($resultado as $row): 
                                    $imagen = $row['imagen'] ? '../assets/img/' . $row['imagen'] : '../assets/img/noadd.jpg';
                                    $token_tmp = hash_hmac('sha1', $row['id'], KEY_TOKEN);
                                ?>
                                    <div class="col-6 col-md-4 col-lg-3">
                                        <div class="card product-card h-100 border-0 shadow-sm">
                                            <div class="product-image">
                                                <img src="<?php echo $imagen; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['titulo']); ?>" loading="lazy">
                                            </div>
                                            <div class="card-body d-flex flex-column p-2 p-sm-3">
                                                <h3 class="product-title h6 mb-2"><?php echo htmlspecialchars($row['titulo']); ?></h3>
                                                <div class="product-price mt-auto mb-2 mb-sm-3">
                                                    <span class="price-current h5 fw-bold">$<?php echo number_format($row['precio'], 2); ?></span>
                                                </div>
                                                <div class="product-actions d-flex gap-2">
                                                    <a href="detalles.php?id=<?php echo $row['id']; ?>&token=<?php echo $token_tmp; ?>" 
                                                       class="btn-details flex-grow-1" 
                                                       title="Ver detalles"
                                                       style="text-decoration: none;">
                                                        <i class="bi bi-eye"></i>
                                                        <span class="d-none d-sm-inline"> Detalles</span>
                                                    </a>
                                                    <button class="btn-cart flex-grow-1" 
                                                            onclick="addProducto(<?php echo $row['id']; ?>, '<?php echo $token_tmp; ?>')" 
                                                            title="Agregar al carrito">
                                                        <i class="bi bi-cart-plus"></i>
                                                        <span class="d-none d-sm-inline"> Agregar</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
 
    <?php include '../include/footer.php'; ?>
 
    <script>
        // Variables globales
        let currentCategoria = <?php echo json_encode($idCategoria); ?>;
        let currentOrden = <?php echo json_encode($orden); ?>;
        let searchTimeout;
        
        // Función para cargar productos vía AJAX
        function loadProductos(categoria = null, orden = null, search = '') {
            const loader = document.getElementById('productsLoader');
            const container = document.getElementById('productsContainer');
            
            loader.classList.add('active');
            container.style.opacity = '0.5';
            
            let url = window.location.pathname + '?';
            if (categoria !== null && categoria !== '') {
                url += 'cat=' + categoria + '&';
            }
            if (orden) {
                url += 'orden=' + orden + '&';
            }
            if (search) {
                url += 'search=' + encodeURIComponent(search) + '&';
            }
            
            url = url.replace(/[?&]$/, '');
            
            if (url === window.location.pathname + '?') {
                url = window.location.pathname;
            }
            
            fetch(url, {
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    container.innerHTML = '<div class="row g-3">' + data.html + '</div>';
                    
                    const params = new URLSearchParams();
                    if (categoria) params.append('cat', categoria);
                    if (orden) params.append('orden', orden);
                    
                    const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
                    window.history.pushState({}, '', newUrl);
                    
                    const countElement = document.getElementById('productsCount');
                    if (countElement) {
                        countElement.textContent = data.total;
                    }
                    
                    document.querySelectorAll('.category-item').forEach(item => {
                        const catId = item.dataset.categoria;
                        if (catId == categoria || (categoria === null && catId === '')) {
                            item.classList.add('active');
                        } else {
                            item.classList.remove('active');
                        }
                    });
                    
                    if (window.innerWidth < 992) {
                        const collapse = document.getElementById('filtersCollapse');
                        if (collapse && collapse.classList.contains('show')) {
                            collapse.classList.remove('show');
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error al cargar los productos', 'error');
            })
            .finally(() => {
                loader.classList.remove('active');
                container.style.opacity = '1';
            });
        }
        
        function filterByCategory(categoria) {
            currentCategoria = categoria || null;
            const searchValue = document.getElementById('searchInput') ? document.getElementById('searchInput').value : '';
            loadProductos(currentCategoria, currentOrden, searchValue);
        }
        
        function filterByOrder(orden) {
            currentOrden = orden || null;
            const searchValue = document.getElementById('searchInput') ? document.getElementById('searchInput').value : '';
            loadProductos(currentCategoria, currentOrden, searchValue);
        }
        
        function handleSearch() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const search = document.getElementById('searchInput').value;
                loadProductos(currentCategoria, currentOrden, search);
            }, 500);
        }
        
        function applyPriceFilter() {
            const min = document.getElementById('minPrice').value;
            const max = document.getElementById('maxPrice').value;
            
            if (!min && !max) {
                showNotification('Ingresa un rango de precio válido', 'info');
                return;
            }
            
            showNotification('Filtro de precio en desarrollo', 'info');
        }
        
        // ✅ CORRECCIÓN: usa window.actualizarBadgeCarrito del header
        //    en lugar de buscar el ID incorrecto "num_cart" que no existe
        function addProducto(id, token) {
            let url = '../include/carrito.php';
            let formData = new FormData();
            formData.append('id', id);
            formData.append('token', token);
 
            fetch(url, {
                method: 'POST',
                body: formData,
                mode: 'cors'
            })
            .then(response => response.json())
            .then(data => { 
                if (data.ok) {
                    if (window.actualizarBadgeCarrito) {
                        window.actualizarBadgeCarrito(data.numero);
                    }
                    showNotification('Producto agregado al carrito');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error al agregar el producto', 'error');
            });
        }
 
        function showNotification(message, type = 'success') {
            document.querySelectorAll('.custom-notification').forEach(n => n.remove());
 
            const notification = document.createElement('div');
            notification.className = 'custom-notification';
            
            let iconColor = 'var(--primary-gold)';
            if (type === 'error') iconColor = '#dc3545';
            else if (type === 'info') iconColor = '#17a2b8';
            
            notification.innerHTML = `
                <i class="bi bi-${type === 'success' ? 'check-circle-fill' : type === 'error' ? 'exclamation-circle-fill' : 'info-circle-fill'}" 
                   style="color: ${iconColor};"></i>
                <span>${message}</span>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideInRight 0.3s ease reverse forwards';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
 
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.addEventListener('input', handleSearch);
            }
            
            document.addEventListener('click', function(e) {
                if (window.innerWidth < 992) {
                    const filters = document.getElementById('filtersCollapse');
                    const filterButton = e.target.closest('[data-bs-toggle="collapse"]');
                    
                    if (filters && !filters.contains(e.target) && !filterButton && filters.classList.contains('show')) {
                        filters.classList.remove('show');
                    }
                }
            });
        });
 
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 992) {
                const collapse = document.getElementById('filtersCollapse');
                if (collapse && !collapse.classList.contains('show')) {
                    collapse.classList.add('show');
                }
            }
        });
 
        document.addEventListener('click', function(e) {
            if (e.target.closest('.filter-category')) {
                e.preventDefault();
            }
        });
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>