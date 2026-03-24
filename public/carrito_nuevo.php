<?php
require_once '../include/configuraciones.php';
require_once '../include/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$db = new Database();
$pdo = $db->getConexion();

// Obtener carrito
$carrito_productos = $_SESSION['carrito']['productos'] ?? [];
$carrito_variantes = $_SESSION['carrito']['variantes'] ?? [];

// Obtener detalles de productos antiguos
$productos_detalles = [];
if (!empty($carrito_productos)) {
    $ids = implode(',', array_keys($carrito_productos));
    $stmt = $pdo->query("SELECT id, titulo, imagen, precio FROM productos WHERE id IN ($ids)");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $productos_detalles[$row['id']] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Carrito - Tienda Online</title>
    <?php include '../include/links.php'; ?>
    <style>
        .carrito-section {
            background-color: var(--secondary-light);
            padding: 2rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }

        .carrito-item {
            display: flex;
            gap: 1rem;
            padding: 1rem;
            background: white;
            border-radius: 6px;
            margin-bottom: 1rem;
            border: 1px solid #ddd;
        }

        .carrito-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 4px;
        }

        .carrito-item-info {
            flex-grow: 1;
        }

        .carrito-item-titulo {
            font-weight: 600;
            color: var(--primary-dark);
            margin-bottom: 0.5rem;
        }

        .carrito-item-detalles {
            font-size: 0.9rem;
            color: var(--accent-gray);
            margin-bottom: 0.5rem;
        }

        .carrito-item-precio {
            font-weight: 600;
            color: var(--primary-dark);
        }

        .carrito-item-acciones {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }

        .cantidad-input {
            width: 80px;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
        }

        .btn-remove {
            background: #dc3545;
            color: white;
            border: none;
            padding: 0.5rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .btn-remove:hover {
            background: #c82333;
        }

        .carrito-vacio {
            text-align: center;
            padding: 3rem;
            color: var(--accent-gray);
        }

        .resumen-carrito {
            background-color: var(--primary-dark);
            color: white;
            padding: 2rem;
            border-radius: 8px;
            margin-top: 2rem;
        }

        .resumen-fila {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            padding-bottom: 1rem;
        }

        .resumen-fila.total {
            border: none;
            font-size: 1.3rem;
            font-weight: 700;
        }

        .btn-checkout {
            background-color: var(--secondary-light);
            color: var(--primary-dark);
            border: none;
            padding: 1rem 2rem;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            margin-top: 1.5rem;
            font-size: 1.1rem;
        }

        .btn-checkout:hover {
            background-color: #f0f0f0;
        }

        .seccion-titulo {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 3px solid var(--primary-dark);
        }
    </style>
</head>
<body>

<?php include '../include/header.php'; ?>

<main class="section-padding">
    <div class="container">
        <a href="index.php" class="btn btn-outline-dark mb-4">
            <i class="bi bi-arrow-left"></i> Continuar comprando
        </a>

        <h1 class="mb-4"><i class="bi bi-bag"></i> Mi Carrito</h1>

        <?php 
        $hay_elementos = !empty($carrito_productos) || !empty($carrito_variantes);
        if (!$hay_elementos): 
        ?>
            <div class="carrito-vacio">
                <i class="bi bi-cart-x" style="font-size: 3rem; color: var(--accent-gray); display: block; margin-bottom: 1rem;"></i>
                <h3>Tu carrito está vacío</h3>
                <p>Agrega algunos productos antes de continuar</p>
                <a href="index.php" class="btn btn-primary mt-3">Ir al catálogo</a>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-lg-8">
                    <!-- Productos antiguos (compatibilidad) -->
                    <?php if (!empty($carrito_productos)): ?>
                        <div class="carrito-section">
                            <div class="seccion-titulo"><i class="bi bi-box"></i> Productos</div>
                            <?php foreach ($carrito_productos as $id_producto => $cantidad): ?>
                                <?php 
                                $producto = $productos_detalles[$id_producto] ?? null;
                                if (!$producto) continue;
                                $subtotal = $producto['precio'] * $cantidad;
                                ?>
                                <div class="carrito-item">
                                    <img src="../assets/img/<?php echo htmlspecialchars($producto['imagen']); ?>" alt="<?php echo htmlspecialchars($producto['titulo']); ?>">
                                    <div class="carrito-item-info">
                                        <div class="carrito-item-titulo"><?php echo htmlspecialchars($producto['titulo']); ?></div>
                                        <div class="carrito-item-precio"><?php echo MONEDA . number_format($producto['precio'], 2); ?> c/u</div>
                                    </div>
                                    <div class="carrito-item-acciones">
                                        <input type="number" class="cantidad-input" value="<?php echo $cantidad; ?>" min="1">
                                        <button class="btn-remove" onclick="eliminarProducto(<?php echo $id_producto; ?>)">Eliminar</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Variantes de productos -->
                    <?php if (!empty($carrito_variantes)): ?>
                        <div class="carrito-section">
                            <div class="seccion-titulo"><i class="bi bi-palette-fill"></i> Variantes seleccionadas</div>
                            <?php foreach ($carrito_variantes as $clave => $variante): ?>
                                <div class="carrito-item" data-clave="<?php echo htmlspecialchars($clave); ?>">
                                    <div class="carrito-item-info">
                                        <div class="carrito-item-titulo">
                                            <?php echo htmlspecialchars($variante['talla']); ?> - <?php echo htmlspecialchars($variante['color']); ?>
                                        </div>
                                        <div class="carrito-item-detalles">
                                            Producto ID: #<?php echo $variante['id_producto']; ?>
                                        </div>
                                        <div class="carrito-item-precio"><?php echo MONEDA . number_format($variante['precio'], 2); ?> c/u</div>
                                    </div>
                                    <div class="carrito-item-acciones">
                                        <input type="number" class="cantidad-input" value="<?php echo $variante['cantidad']; ?>" min="1" 
                                               onchange="actualizarVariante('<?php echo htmlspecialchars($clave); ?>', this.value)">
                                        <button class="btn-remove" onclick="eliminarVariante('<?php echo htmlspecialchars($clave); ?>')">Eliminar</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-lg-4">
                    <div class="resumen-carrito">
                        <h3 style="margin-bottom: 1.5rem; color: white;">Resumen del pedido</h3>
                        
                        <?php 
                        $subtotal = 0;
                        
                        // Subtotal productos antiguos
                        foreach ($carrito_productos as $id_producto => $cantidad) {
                            $producto = $productos_detalles[$id_producto] ?? null;
                            if ($producto) {
                                $subtotal += $producto['precio'] * $cantidad;
                            }
                        }
                        
                        // Subtotal variantes
                        foreach ($carrito_variantes as $variante) {
                            $subtotal += $variante['precio'] * $variante['cantidad'];
                        }

                        $iva = $subtotal * (IVA / 100);
                        $total = $subtotal + $iva;
                        ?>

                        <div class="resumen-fila">
                            <span>Subtotal:</span>
                            <span><?php echo MONEDA . number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="resumen-fila">
                            <span>IVA (<?php echo IVA; ?>%):</span>
                            <span><?php echo MONEDA . number_format($iva, 2); ?></span>
                        </div>
                        <div class="resumen-fila total">
                            <span>TOTAL:</span>
                            <span><?php echo MONEDA . number_format($total, 2); ?></span>
                        </div>

                        <button class="btn-checkout" onclick="procederCheckout()">
                            <i class="bi bi-credit-card"></i> Proceder al pago
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include '../include/footer.php'; ?>

<script>
    function eliminarProducto(idProducto) {
        if (confirm('¿Eliminar este producto del carrito?')) {
            // Implementar con AJAX
            console.log('Eliminar producto:', idProducto);
        }
    }

    function actualizarVariante(clave, cantidad) {
        const formData = new FormData();
        formData.append('action', 'actualizarCantidad');
        formData.append('clave_variante', clave);
        formData.append('cantidad', cantidad);

        fetch('../include/carrito_variantes.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                location.reload(); // Recargar para actualizar totales
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function eliminarVariante(clave) {
        if (confirm('¿Eliminar esta variante del carrito?')) {
            const formData = new FormData();
            formData.append('action', 'eliminarVariante');
            formData.append('clave_variante', clave);

            fetch('../include/carrito_variantes.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.ok) {
                    location.reload();
                }
            })
            .catch(error => console.error('Error:', error));
        }
    }

    function procederCheckout() {
        // Redirigir a la página de checkout
        window.location.href = 'checkout.php';
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
