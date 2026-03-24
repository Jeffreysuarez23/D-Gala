<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Insertar Producto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Spartan Multi Image Picker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/spartan-multi-image-picker/dist/css/spartan-multi-image-picker.min.css">
    <script src="https://cdn.jsdelivr.net/npm/spartan-multi-image-picker/dist/js/spartan-multi-image-picker.min.js"></script>

    <style>
        /* Contenedor horizontal con scroll para las imágenes */
        #multi_image_picker {
            display: flex !important; /* fuerza el flex */
            overflow-x: auto;
            gap: 10px;
            padding: 10px 0;
            max-width: 100%;
        }
        /* Para que cada imagen tenga un tamaño fijo y se vea bien */
        #multi_image_picker .spartan_item {
            flex: 0 0 auto; /* no se achica ni crece */
            width: 120px;   /* ancho fijo igual al rowHeight */
            height: 120px;  /* alto fijo igual al rowHeight */
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h2>Agregar Producto</h2>

    <form action="../include/añadir.php" method="POST" enctype="multipart/form-data" id="formProducto">

        <div class="mb-3">
            <label for="titulo" class="form-label">Título:</label>
            <input type="text" class="form-control" name="titulo" id="titulo" required />
        </div>

        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción:</label>
            <textarea class="form-control" name="descripcion" id="descripcion" required></textarea>
        </div>

        <div class="mb-3">
            <label for="precio" class="form-label">Precio:</label>
            <input type="number" class="form-control" name="precio" id="precio" step="0.01" required />
        </div>

        <div class="mb-3">
            <label for="talla" class="form-label">Talla:</label>
            <select class="form-select" name="talla" id="talla" required>
                <option value="" disabled selected>Selecciona una talla</option>
                <optgroup label="Tallas Niño / Adolescente">
                    <option value="2">Talla 2</option>
                    <option value="4">Talla 4</option>
                    <option value="6">Talla 6</option>
                    <option value="8">Talla 8</option>
                    <option value="10">Talla 10</option>
                    <option value="12">Talla 12</option>
                    <option value="14">Talla 14</option>
                    <option value="16">Talla 16</option>
                </optgroup>
                <optgroup label="Tallas Adulto">
                    <option value="XS">XS</option>
                    <option value="S">S</option>
                    <option value="M">M</option>
                    <option value="L">L</option>
                    <option value="XL">XL</option>
                    <option value="XXL">XXL</option>
                </optgroup>
            </select>
        </div>

        <div class="mb-3">
            <label for="imagen" class="form-label">Imagen Principal:</label>
            <input type="file" class="form-control" name="imagen" id="imagen" accept="image/*" required />
        </div>

        <div class="mb-3">
            <label class="form-label">Imágenes Adicionales:</label>
            <div id="multi_image_picker"></div>
        </div>

        <div class="mb-3">
            <label for="id_categoria" class="form-label">Categoría:</label>
            <select class="form-select" name="id_categoria" id="id_categoria" required>
                <option value="1">Ropa</option>
                <option value="2">Camisas</option>
                <option value="3">Vestidos</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="activo" class="form-label">Activo:</label>
            <select class="form-select" name="activo" id="activo" required>
                <option value="1">Disponible</option>
                <option value="0">Agotado</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Agregar Producto</button>

    </form>
</div>

<script>
    $(document).ready(function() {
        $("#multi_image_picker").spartanMultiImagePicker({
            fieldName: 'imagenes[]',
            maxCount: 10,
            rowHeight: '120px',
            groupClassName: 'spartan_item', // una clase que le ponemos para controlar el estilo con flexbox
            allowedExt: 'png|jpg|jpeg',
            dropFileLabel: "Arrastra o haz clic aquí para agregar imágenes",
        });
    });
</script>

</body>
</html>

