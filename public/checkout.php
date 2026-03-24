<?php
require_once '../include/db.php';
require_once '../include/configuraciones.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$db = new Database();
$conexion = $db->getConexion();
$lista_carrito = array();

// 1. Agregar productos
$productos = isset($_SESSION['carrito']['productos']) ? $_SESSION['carrito']['productos'] : array();
if (!empty($productos)) {
    foreach ($productos as $id_prod => $cantidad) {
        $sql = $conexion->prepare("SELECT id, titulo, precio, imagen, descuento FROM productos WHERE id = ? AND activo = 1");
        $sql->execute([$id_prod]);
        $producto = $sql->fetch(PDO::FETCH_ASSOC);
        if ($producto) {
            $producto['cantidad'] = $cantidad;
            $producto['tipo'] = 'producto';
            $lista_carrito[] = $producto;
        }
    }
}

// 2. Agregar variantes
$variantes = isset($_SESSION['carrito']['variantes']) ? $_SESSION['carrito']['variantes'] : array();
if (!empty($variantes)) {
    foreach ($variantes as $key => $variante) {
        $variante['tipo'] = 'variante';
        
        if (isset($variante['id_producto'])) {
            $sql_prod = $conexion->prepare("SELECT titulo, imagen FROM productos WHERE id = ?");
            $sql_prod->execute([$variante['id_producto']]);
            $prod_original = $sql_prod->fetch(PDO::FETCH_ASSOC);
            
            if ($prod_original) {
                $variante['imagen'] = $prod_original['imagen'];
                $variante['titulo_producto'] = $prod_original['titulo'];
            }
        }
        
        $lista_carrito[] = $variante;
    }
}

$total = 0;
$num_items = count($lista_carrito);
?>

<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Carrito de Compras</title>
    <link rel="stylesheet" href="tt.css">
    <?php include '../include/links.php'; ?>
    <meta name="theme-color" content="#000000">
    
    <style>
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }

        .toast {
            background: var(--primary-black);
            color: var(--text-light);
            padding: 14px 24px;
            border-radius: 6px;
            margin-bottom: 10px;
            box-shadow: var(--shadow-lg);
            animation: slideIn 0.3s ease;
            font-weight: 500;
            border-left: 4px solid var(--primary-gold);
        }

        .toast.success {
            background: #10b981;
            border-left-color: #059669;
        }

        .toast.error {
            background: #ef4444;
            border-left-color: #dc2626;
        }

        @keyframes slideIn {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(400px); opacity: 0; }
        }

        .removing {
            opacity: 0.5;
            transition: opacity 0.3s ease;
        }

        @media (max-width: 768px) {
            .product-image { width: 70px; height: 70px; }
            .cart-table thead th { font-size: 0.75rem; padding: 15px 8px; }
            .cart-table tbody td { padding: 15px 8px; }
            .btn-delete { padding: 8px 10px; font-size: 0.75rem; }
            .qty-input { width: 55px; height: 35px; }
        }

        @media (max-width: 576px) {
            .product-image { width: 50px; height: 50px; }
            .product-name { font-size: 0.8rem; }
            .price-cell, .subtotal-cell { font-size: 0.8rem; }
        }
    </style>
</head>
<body>
    <?php include '../include/header.php'; ?>
    
    <section class="about-hero">
        <div class="container text-center">
            <h1 class="fadeInUp">Carrito d<span>e compras</span></h1>
            <div class="gold-line fadeInUp" style="animation-delay: 0.2s;"></div>
            <p class="fadeInUp" style="animation-delay: 0.4s;">
                Revisa los productos que has agregado a tu carrito. Puedes modificar las cantidades o eliminar productos antes de proceder al pago.
            </p>
        </div>
    </section>
    
    <main class="container section-padding">
        <?php if (empty($lista_carrito)): ?>
            <div class="empty-cart fadeInUp">
                <div class="empty-cart-icon">🛒</div>
                <h3>Tu carrito está vacío</h3>
                <p>No tienes productos en tu carrito. ¡Agrega algunos!</p>
                <a href="index.php" class="btn-action btn-primary-black">Continuar Comprando</a>
            </div>
        <?php else: ?>
            <div class="cart-table fadeInUp">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 10%;">Imagen</th>
                                <th style="width: 35%;">Producto</th>
                                <th style="width: 15%;">Precio</th>
                                <th style="width: 15%;">Cantidad</th>
                                <th style="width: 15%;">Subtotal</th>
                                <th style="width: 10%;">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            foreach ($lista_carrito as $item): 
                                $tipo = $item['tipo'];
                                $cantidad = $item['cantidad'];
                                
                                if ($tipo === 'producto') {
                                    $id = $item['id'];
                                    $titulo = $item['titulo'];
                                    $precio = $item['precio'];
                                    $descuento = isset($item['descuento']) ? $item['descuento'] : 0;
                                    $imagen = $item['imagen'];
                                    $precio_final = $precio - (($precio * $descuento) / 100);
                                    $badge = '';
                                } else {
                                    $id = $item['variante_id'];
                                    $titulo_prod = isset($item['titulo_producto']) ? $item['titulo_producto'] : 'Producto';
                                    $titulo = $titulo_prod . ' (' . $item['talla'] . ' - ' . $item['color'] . ')';
                                    $precio_final = $item['precio'];
                                    $imagen = isset($item['imagen']) ? $item['imagen'] : 'noadd.jpg';
                                    $badge = '<span class="product-badge">Variante</span>';
                                }
                                
                                $subtotal = $cantidad * $precio_final;
                                $total += $subtotal;
                                $id_element = $id . '_' . $tipo;
                            ?>
                            <tr id="row_<?php echo $id_element; ?>">
                                <td>
                                    <img src="<?php echo '../assets/img/' . htmlspecialchars($imagen); ?>" 
                                         alt="<?php echo htmlspecialchars($titulo); ?>" 
                                         class="product-image" 
                                         onerror="this.src='../assets/img/noadd.jpg'">
                                </td>
                                <td>
                                    <div class="product-info">
                                        <div class="product-name"><?php echo htmlspecialchars(strlen($titulo) > 45 ? substr($titulo, 0, 45) . '...' : $titulo); ?></div>
                                        <?php if ($badge): ?>
                                            <?php echo $badge; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="price-cell">
                                    <?php echo MONEDA . number_format($precio_final, 2, '.', ','); ?>
                                </td>
                                <td>
                                    <input type="number" 
                                           class="qty-input" 
                                           min="1" 
                                           max="99" 
                                           value="<?php echo $cantidad; ?>" 
                                           id="cant_<?php echo $id_element; ?>" 
                                           data-id="<?php echo $id; ?>" 
                                           data-tipo="<?php echo $tipo; ?>" 
                                           onchange="actualizarCantidad(this)">
                                </td>
                                <td class="subtotal-cell" id="sub_<?php echo $id_element; ?>">
                                    <?php echo MONEDA . number_format($subtotal, 2, '.', ','); ?>
                                </td>
                                <td>
                                    <button class="btn-delete" onclick="eliminarItem(<?php echo $id; ?>, '<?php echo $tipo; ?>')">
                                        ✕ Eliminar
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" style="text-align: right; padding-right: 30px;">TOTAL</td>
                                <td colspan="2" id="total-container">
                                    <?php echo MONEDA . number_format($total, 2, '.', ','); ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            
            <div class="checkout-actions">
                <a style="background: grey" href="index.php" class="btn-action btn-secondary-black">← Seguir Comprando</a>
                <?php if(isset($_SESSION['user_cliente'])) {?>
                    <a href="pago.php" class="btn-action btn-primary-black">Proceder al Pago →</a>
                <?php } else { ?>
                    <a href="login.php?pago" class="btn-action btn-primary-black">Inicia Sesión →</a>
                <?php } ?>
            </div>
        <?php endif; ?>
    </main>

    <?php include '../include/footer.php'; ?>

    <script>
        const MONEDA = '<?php echo MONEDA; ?>';
        
        function createToastContainer() {
            if (document.getElementById('toast-container')) return;
            const container = document.createElement('div');
            container.className = 'toast-container';
            container.id = 'toast-container';
            document.body.appendChild(container);
            return container;
        }
        
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container') || createToastContainer();
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.textContent = message;
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease forwards';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
        
        function actualizarTotal() {
            let total = 0;
            const subtotalCells = document.querySelectorAll('.subtotal-cell');
            
            subtotalCells.forEach(cell => {
                const text = cell.textContent.trim();
                let cleanText = text.replace(MONEDA, '').trim();
                
                if (cleanText.includes(',') && cleanText.includes('.')) {
                    const lastDot = cleanText.lastIndexOf('.');
                    const lastComma = cleanText.lastIndexOf(',');
                    if (lastDot > lastComma) {
                        cleanText = cleanText.replace(/,/g, '');
                    } else {
                        cleanText = cleanText.replace(/\./g, '').replace(',', '.');
                    }
                } else if (cleanText.includes(',')) {
                    cleanText = cleanText.replace(',', '.');
                }
                
                const num = parseFloat(cleanText);
                if (!isNaN(num)) total += num;
            });
            
            const totalElem = document.getElementById('total-container');
            if (totalElem) {
                totalElem.textContent = MONEDA + ' ' + total.toFixed(2).replace('.', ',');
            }
        }
        
        function actualizarCantidad(input) {
            const id = input.dataset.id;
            const tipo = input.dataset.tipo;
            const cantidad = parseInt(input.value);
            
            if (cantidad < 1 || cantidad > 99 || isNaN(cantidad)) {
                showToast('Cantidad inválida', 'error');
                input.value = input.dataset.original || 1;
                return;
            }
            
            input.disabled = true;
            const btnDelete = input.closest('tr').querySelector('.btn-delete');
            if (btnDelete) btnDelete.disabled = true;
            
            const formData = new FormData();
            const url = tipo === 'producto' 
                ? '../include/actualizar_carrito.php'
                : '../include/carrito_variantes.php';
            
            if (tipo === 'producto') {
                formData.append('action', 'agregar');
                formData.append('id', id);
                formData.append('cantidad', cantidad);
            } else {
                formData.append('action', 'actualizar');
                formData.append('variante_id', id);
                formData.append('cantidad', cantidad);
            }
            
            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.ok) {
                    input.dataset.original = cantidad;
                    
                    const row = input.closest('tr');
                    const precioText = row.querySelector('.price-cell').textContent.trim();
                    
                    let precioClean = precioText.replace(MONEDA, '').trim();
                    if (precioClean.includes(',') && precioClean.includes('.')) {
                        const lastDot = precioClean.lastIndexOf('.');
                        const lastComma = precioClean.lastIndexOf(',');
                        if (lastDot > lastComma) {
                            precioClean = precioClean.replace(/,/g, '');
                        } else {
                            precioClean = precioClean.replace(/\./g, '').replace(',', '.');
                        }
                    } else if (precioClean.includes(',')) {
                        precioClean = precioClean.replace(',', '.');
                    }
                    
                    const precio = parseFloat(precioClean);
                    const subtotal = precio * cantidad;
                    const subtotalFormatted = subtotal.toFixed(2).replace('.', ',');
                    row.querySelector('.subtotal-cell').textContent = MONEDA + ' ' + subtotalFormatted;
                    
                    actualizarTotal();
                    showToast('Cantidad actualizada ✓', 'success');
                } else {
                    showToast('Error al actualizar: ' + (data.error || 'Intenta de nuevo'), 'error');
                    input.value = input.dataset.original || 1;
                }
                
                input.disabled = false;
                if (btnDelete) btnDelete.disabled = false;
            })
            .catch(err => {
                console.error('Error:', err);
                showToast('Error de conexión. Intenta de nuevo', 'error');
                input.disabled = false;
                if (btnDelete) btnDelete.disabled = false;
            });
        }
        
        function eliminarItem(id, tipo) {
            if (!confirm('¿Eliminar este producto del carrito?')) {
                return;
            }
            
            const rowId = `row_${id}_${tipo}`;
            const row = document.getElementById(rowId);
            if (!row) return;
            
            const btnDelete = row.querySelector('.btn-delete');
            if (btnDelete) btnDelete.disabled = true;
            
            const formData = new FormData();
            const url = tipo === 'producto' 
                ? '../include/actualizar_carrito.php'
                : '../include/carrito_variantes.php';
            
            if (tipo === 'producto') {
                formData.append('action', 'eliminar');
                formData.append('id', id);
            } else {
                formData.append('action', 'eliminar');
                formData.append('variante_id', id);
            }
            
            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.ok) {
                    row.classList.add('removing');
                    setTimeout(() => {
                        row.style.animation = 'slideOut 0.3s ease forwards';
                        setTimeout(() => {
                            row.remove();
                            actualizarTotal();
                            showToast('Producto eliminado ✓', 'success');

                            // ✅ CORRECCIÓN: contar filas restantes en el DOM
                            // y actualizar el badge del header sin fetch extra
                            const filaRestantes = document.querySelectorAll('.cart-table tbody tr').length;
                            if (window.actualizarBadgeCarrito) {
                                window.actualizarBadgeCarrito(filaRestantes);
                            }

                            if (filaRestantes === 0) {
                                setTimeout(() => location.reload(), 500);
                            }
                        }, 300);
                    }, 100);
                } else {
                    showToast('Error al eliminar: ' + (data.error || 'Intenta de nuevo'), 'error');
                    if (btnDelete) btnDelete.disabled = false;
                }
            })
            .catch(err => {
                console.error('Error:', err);
                showToast('Error de conexión. Intenta de nuevo', 'error');
                if (btnDelete) btnDelete.disabled = false;
            });
        }
        
        document.addEventListener('DOMContentLoaded', () => {
            createToastContainer();
            actualizarTotal();
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>