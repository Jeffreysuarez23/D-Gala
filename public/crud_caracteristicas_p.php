<?php
// Incluimos el archivo de conexión a la base de datos
require_once '../include/db.php';

// Obtener los productos de la base de datos
$db = new Database();
$pdo = $db->getConexion();

$sql = "SELECT id, titulo FROM productos WHERE activo = 1"; // Solo productos activos
$stmt = $pdo->prepare($sql);
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Agregar Características del Producto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<div class="container mt-5">
    <h2>Agregar Características del Producto</h2>

    <form action="../include/añadir_caracteristicas.php" method="POST" id="formCaracteristicas">

        <!-- Selección de Producto -->
        <div class="mb-3">
            <label for="id_producto" class="form-label">Producto:</label>
            <select class="form-select" name="id_producto" id="id_producto" required>
                <!-- Cargar productos dinámicamente desde la base de datos -->
                <option value="" disabled selected>Selecciona un producto</option>
                <?php foreach ($productos as $producto): ?>
                    <option value="<?= $producto['id'] ?>"><?= htmlspecialchars($producto['titulo']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Sección de Características Dinámicas -->
        <div id="caracteristicas-container">
            <!-- Aquí se agregarán dinámicamente las características -->
        </div>

        <!-- Botón para añadir más características -->
        <button type="button" id="agregarCaracteristica" class="btn btn-secondary mb-3">Añadir Más Características</button>

        <button type="submit" class="btn btn-primary">Guardar Características</button>

    </form>
</div>

<script>
    $(document).ready(function() {
        let contadorCaracteristicas = 0; // Contador de características

        // Función para agregar una nueva característica
        function agregarCaracteristica() {
            contadorCaracteristicas++;  // Aumentamos el contador por cada característica agregada

            const caracteristicaHTML = `
                <div class="mb-4" id="caracteristica${contadorCaracteristicas}">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4>Nueva Característica <span class="caracteristica-titulo"></span></h4>
                        <!-- Botón de eliminación de característica -->
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="eliminarCaracteristica(${contadorCaracteristicas})">X</button>
                    </div>
                    
                    <div class="mb-3">
                        <label for="tipo_caracteristica${contadorCaracteristicas}" class="form-label">Seleccionar Característica:</label>
                        <select class="form-select" name="caracteristicas[${contadorCaracteristicas}][id_caracteristica]" id="tipo_caracteristica${contadorCaracteristicas}" required>
                            <option value="1">Color</option>
                            <option value="2">Material</option>
                            <option value="3">Talla</option>
                        </select>
                    </div>

                    <div id="valores-caracteristica${contadorCaracteristicas}" class="mb-3">
                        <h5>Valores y Stock</h5>
                        <div class="valor-stock mb-3" id="valor-stock-0">
                            <input type="text" class="form-control mb-2" name="caracteristicas[${contadorCaracteristicas}][valores][0][valor]" placeholder="Valor (ej. M)" required>
                            <input type="number" class="form-control" name="caracteristicas[${contadorCaracteristicas}][valores][0][stock]" placeholder="Stock" required>
                            <!-- Botón de eliminación de valor -->
                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="eliminarValor(${contadorCaracteristicas}, 0)" style="font-size: 12px; padding: 5px 10px; margin-top: 5px;">X</button>
                        </div>
                    </div>

                    <!-- Botón para añadir más valores dentro de esta característica -->
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="agregarValor(${contadorCaracteristicas})" style="font-size: 12px; padding: 5px 10px;">Añadir Otro Valor</button>
                </div>
            `;

            // Agregar el HTML al contenedor
            $('#caracteristicas-container').append(caracteristicaHTML);

            // Actualizar título según tipo de característica
            $(`#tipo_caracteristica${contadorCaracteristicas}`).change(function() {
                const selected = $(this).find('option:selected').text();
                $(`#caracteristica${contadorCaracteristicas} .caracteristica-titulo`).text(selected);
            });
        }

        // Llamar a la función al hacer click en "Añadir Más Características"
        $('#agregarCaracteristica').click(function() {
            agregarCaracteristica();
        });

        // Función para añadir más valores dentro de una característica
        window.agregarValor = function(caracteristicaId) {
            const nuevoValorHTML = `
                <div class="valor-stock mb-3" id="valor-stock-${$('#valores-caracteristica' + caracteristicaId).children().length}">
                    <input type="text" class="form-control mb-2" name="caracteristicas[${caracteristicaId}][valores][${$('#valores-caracteristica' + caracteristicaId).children().length}][valor]" placeholder="Valor (ej. M)" required>
                    <input type="number" class="form-control" name="caracteristicas[${caracteristicaId}][valores][${$('#valores-caracteristica' + caracteristicaId).children().length}][stock]" placeholder="Stock" required>
                    <!-- Botón de eliminación de valor -->
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="eliminarValor(${caracteristicaId}, ${$('#valores-caracteristica' + caracteristicaId).children().length})" style="font-size: 12px; padding: 5px 10px; margin-top: 5px;">X</button>
                </div>
            `;
            // Agregar un nuevo valor y stock
            $(`#valores-caracteristica${caracteristicaId}`).append(nuevoValorHTML);
        }

        // Función para eliminar una característica
        window.eliminarCaracteristica = function(caracteristicaId) {
            if (confirm('¿Estás seguro de que quieres eliminar esta característica?')) {
                $(`#caracteristica${caracteristicaId}`).remove();
            }
        }

        // Función para eliminar un valor dentro de una característica
        window.eliminarValor = function(caracteristicaId, valorId) {
            if (confirm('¿Estás seguro de que quieres eliminar este valor?')) {
                $(`#valor-stock-${valorId}`).remove();
            }
        }
    });
</script>

</body>
</html>
