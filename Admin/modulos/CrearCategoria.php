<?php
require_once("include/db.php");
require_once("include/configuracionesAD.php");
require_once(dirname(dirname(__DIR__)) . '/include/validaciones.php');

$db = new Database();
$conexion = $db->getConexion();

$mensaje = '';
$tipo_mensaje = '';

// Obtener todas las categorías activas
$sql = "SELECT id, nombre FROM caracteristicas WHERE activo = 1";
$resultado = $conexion->query($sql);
$categorias = $resultado->fetchAll(PDO::FETCH_ASSOC);

// CREAR CATEGORÍA
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nombre']) && !isset($_POST['accion'])) {
    $caracteristica = ValidadorFormularios::limpiar($_POST['nombre']);
    
    // Validar que no esté vacío
    if (empty($caracteristica)) {
        $mensaje = '❌ El nombre de la categoría es requerido';
        $tipo_mensaje = 'error';
    } else {
        // Validar formato
        $val = ValidadorFormularios::validar($caracteristica, 'titulo');
        if ($val !== true) {
            $mensaje = $val['mensaje'];
            $tipo_mensaje = 'error';
        } else {
            // Verificar que no exista duplicada
            $sqlVerificar = "SELECT id FROM caracteristicas WHERE LOWER(nombre) = LOWER(?) AND activo = 1";
            $stmtVerificar = $conexion->prepare($sqlVerificar);
            $stmtVerificar->execute([$caracteristica]);
            
            if ($stmtVerificar->rowCount() > 0) {
                $mensaje = '❌ Esta categoría ya existe';
                $tipo_mensaje = 'error';
            } else {
                try {
                    $sql = $conexion->prepare("INSERT INTO caracteristicas (nombre, activo) VALUES (?, 1)");
                    if ($sql->execute([$caracteristica])) {
                        $mensaje = '✅ Categoría creada correctamente';
                        $tipo_mensaje = 'success';
                        // Recargar categorías
                        $resultado = $conexion->query("SELECT id, nombre FROM caracteristicas WHERE activo = 1");
                        $categorias = $resultado->fetchAll(PDO::FETCH_ASSOC);
                    }
                } catch (Exception $e) {
                    $mensaje = '❌ Error al crear: ' . $e->getMessage();
                    $tipo_mensaje = 'error';
                }
            }
        }
    }
}

// EDITAR CATEGORÍA
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'editar') {
    $id = intval($_POST['id'] ?? 0);
    $nombre = ValidadorFormularios::limpiar($_POST['nombre'] ?? '');
    
    if ($id <= 0 || empty($nombre)) {
        $mensaje = '❌ Datos inválidos';
        $tipo_mensaje = 'error';
    } else {
        // Validar formato
        $val = ValidadorFormularios::validar($nombre, 'titulo');
        if ($val !== true) {
            $mensaje = $val['mensaje'];
            $tipo_mensaje = 'error';
        } else {
            // Verificar que no exista otra con el mismo nombre
            $sqlVerificar = "SELECT id FROM caracteristicas WHERE LOWER(nombre) = LOWER(?) AND id != ? AND activo = 1";
            $stmtVerificar = $conexion->prepare($sqlVerificar);
            $stmtVerificar->execute([$nombre, $id]);
            
            if ($stmtVerificar->rowCount() > 0) {
                $mensaje = '❌ Ya existe una categoría con ese nombre';
                $tipo_mensaje = 'error';
            } else {
                try {
                    $sqlActualizar = "UPDATE caracteristicas SET nombre = ? WHERE id = ? AND activo = 1";
                    $stmt = $conexion->prepare($sqlActualizar);
                    if ($stmt->execute([$nombre, $id])) {
                        $mensaje = '✅ Categoría actualizada correctamente';
                        $tipo_mensaje = 'success';
                        // Recargar categorías
                        $resultado = $conexion->query("SELECT id, nombre FROM caracteristicas WHERE activo = 1");
                        $categorias = $resultado->fetchAll(PDO::FETCH_ASSOC);
                    } else {
                        $mensaje = '❌ Error al actualizar la categoría';
                        $tipo_mensaje = 'error';
                    }
                } catch (Exception $e) {
                    $mensaje = '❌ Error: ' . $e->getMessage();
                    $tipo_mensaje = 'error';
                }
            }
        }
    }
}

// ELIMINAR CATEGORÍA
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'eliminar') {
    $idEliminar = intval($_POST['id_eliminar'] ?? 0);
    
    if ($idEliminar <= 0) {
        $mensaje = '❌ ID inválido';
        $tipo_mensaje = 'error';
    } else {
        try {
            // Primero verificar que la categoría existe y está activa
            $sqlVerificar = "SELECT id FROM caracteristicas WHERE id = ? AND activo = 1";
            $stmtVerificar = $conexion->prepare($sqlVerificar);
            $stmtVerificar->execute([$idEliminar]);
            
            if ($stmtVerificar->rowCount() == 0) {
                $mensaje = '❌ Categoría no encontrada';
                $tipo_mensaje = 'error';
            } else {
                // Marcar como inactiva
                $sqlEliminar = "UPDATE caracteristicas SET activo = 0 WHERE id = ?";
                $stmtEliminar = $conexion->prepare($sqlEliminar);
                if ($stmtEliminar->execute([$idEliminar])) {
                    $mensaje = '✅ Categoría eliminada correctamente';
                    $tipo_mensaje = 'success';
                    // Recargar categorías
                    $resultado = $conexion->query("SELECT id, nombre FROM caracteristicas WHERE activo = 1");
                    $categorias = $resultado->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $mensaje = '❌ Error al eliminar la categoría';
                    $tipo_mensaje = 'error';
                }
            }
        } catch (Exception $e) {
            $mensaje = '❌ Error: ' . $e->getMessage();
            $tipo_mensaje = 'error';
        }
    }
}
?> 

    <style>
        /* ===== ESTILOS GESTIONAR CATEGORÍAS - BLANCO, NEGRO Y DORADO ===== */
        
        :root {
            --primary: #ffd700;
            --primary-dark: #e6c300;
            --secondary: #000000;
            --dark: #1a1a1a;
            --light: #ffffff;
            --gray: #f5f5f5;
            --border: #e0e0e0;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
        }

        * {
            transition: all 0.3s ease;
        }

        body {
            background: var(--light);
            min-height: 100vh;
        }

        /* Header Section */
        .page-header {
            background: var(--secondary);
            color: var(--primary);
            padding: 2.5rem 2rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border);
            position: relative;
            overflow: hidden;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--primary-dark), var(--primary));
        }

        .page-header h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 1rem;
            color: var(--primary);
        }

        .page-header h1 i {
            color: var(--primary);
            font-size: 2.5rem;
        }

        .page-header p {
            margin: 0.5rem 0 0 0;
            color: #e0e0e0;
            font-size: 1rem;
        }

        .header-content {
            flex: 1;
        }

        /* Buttons */
        .btn {
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--secondary);
            color: var(--light);
            border: 2px solid var(--primary);
        }

        .btn-primary:hover {
            background: var(--primary);
            color: var(--secondary);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 215, 0, 0.3);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-secondary {
            background: var(--light);
            color: var(--secondary);
            border: 2px solid var(--border);
        }

        .btn-secondary:hover {
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-2px);
        }

        .btn-danger {
            background: var(--light);
            color: var(--danger);
            border: 2px solid var(--danger);
        }

        .btn-danger:hover {
            background: var(--danger);
            color: var(--light);
            transform: translateY(-2px);
        }

        .btn-crear {
            background: var(--primary);
            color: var(--secondary);
            border: none;
            padding: 12px 25px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-crear:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 215, 0, 0.3);
        }

        /* Table Container */
        .table-container {
            background: var(--light);
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            border: 1px solid var(--border);
            transition: all 0.3s ease;
        }

        .table-container:hover {
            border-color: var(--primary);
            box-shadow: 0 8px 25px rgba(255, 215, 0, 0.1);
        }

        .table {
            margin-bottom: 0;
        }

        .table thead {
            background: var(--secondary);
            color: var(--primary);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            border-bottom: 2px solid var(--primary);
        }

        .table thead th {
            padding: 1rem;
            border-bottom: none;
        }

        .table tbody tr {
            border-bottom: 1px solid var(--border);
            transition: all 0.3s ease;
        }

        .table tbody tr:hover {
            background-color: var(--gray);
            transform: translateX(3px);
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
        }

        /* Badge */
        .badge-category {
            background: var(--secondary) !important;
            color: var(--primary) !important;
            font-size: 0.9rem;
            padding: 6px 12px;
            border-radius: 20px;
            border: 1px solid var(--primary);
            font-weight: 600;
        }

        /* Action Buttons */
        .btn-accion {
            padding: 6px 12px;
            font-size: 0.85rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            margin: 0 4px;
            font-weight: 500;
        }

        .btn-editar {
            background: var(--secondary);
            color: var(--primary);
            border: 1px solid var(--primary);
        }

        .btn-editar:hover {
            background: var(--primary);
            color: var(--secondary);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 215, 0, 0.3);
        }

        .btn-eliminar {
            background: var(--light);
            color: var(--danger);
            border: 1px solid var(--danger);
        }

        .btn-eliminar:hover {
            background: var(--danger);
            color: var(--light);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(239, 68, 68, 0.3);
        }

        /* No Data */
        .no-datos {
            text-align: center;
            padding: 4rem;
            color: #999;
        }

        .no-datos i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: var(--border);
        }

        .no-datos h5 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--secondary);
            margin-bottom: 0.5rem;
        }

        /* Modal Styles */
        .modal-content {
            border-radius: 20px;
            border: 1px solid var(--border);
            overflow: hidden;
        }

        .modal-header {
            background: var(--secondary);
            color: var(--primary);
            border: none;
            padding: 1.5rem;
        }

        .modal-header .modal-title {
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            border-top: 1px solid var(--border);
            padding: 1rem 1.5rem;
        }

        /* Form Controls */
        .form-label {
            font-weight: 600;
            color: var(--secondary);
            margin-bottom: 0.5rem;
        }

        .form-control {
            border: 2px solid var(--border);
            border-radius: 12px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.2) !important;
        }

        .form-control:hover {
            border-color: var(--primary);
        }

        .form-text {
            font-size: 0.75rem;
            color: #999;
            margin-top: 0.5rem;
            display: block;
        }

        /* Alert Messages */
        .alert-success {
            background: #f0fdf4;
            border: 1px solid var(--success);
            color: #166534;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid var(--danger);
            color: #991b1b;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
                padding: 1.5rem;
            }

            .page-header h1 {
                font-size: 1.8rem;
            }

            .btn-crear {
                width: 100%;
                justify-content: center;
            }

            .table-container {
                overflow-x: auto;
            }

            .btn-accion {
                margin: 4px;
                width: 100%;
            }

            .modal-dialog {
                margin: 1rem;
            }
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .table-container {
            animation: fadeIn 0.4s ease;
        }

        .alert-success, .alert-error {
            animation: fadeIn 0.4s ease;
        }
    </style>
    <main>
        <div class="container-fluid px-4 py-5">
            
            <!-- MENSAJES -->
            <?php if (!empty($mensaje)): ?>
                <div class="alert-<?= $tipo_mensaje ?>">
                    <i class="fas <?= $tipo_mensaje === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                    <strong><?= $tipo_mensaje === 'success' ? 'Éxito' : 'Error' ?></strong><br>
                    <?= htmlspecialchars($mensaje) ?>
                </div>
            <?php endif; ?>
            
            <!-- HEADER -->
            <div class="page-header">
                <div class="header-content">
                    <h1>
                        <i class="fas fa-tag"></i>
                        Gestionar Categorías
                    </h1>
                    <p>Administra todas las categorías de tu tienda</p>
                </div>
                <button type="button" class="btn btn-crear" data-bs-toggle="modal" data-bs-target="#crearModal">
                    <i class="fas fa-plus-circle"></i> Crear Categoría
                </button>
            </div>

            <!-- TABLA CATEGORÍAS -->
            <div class="table-container">
                <?php if (count($categorias) > 0): ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th width="15%"><i class="fas fa-hashtag"></i> ID</th>
                                    <th width="60%"><i class="fas fa-tag"></i> Nombre</th>
                                    <th width="25%" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categorias as $categoria): ?>
                                    <tr>
                                        <td>
                                            <span class="badge-category">
                                                #<?= $categoria['id'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($categoria['nombre']) ?></strong>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-accion btn-editar" data-bs-toggle="modal" data-bs-target="#editarModal<?= $categoria['id'] ?>">
                                                <i class="fas fa-pencil-alt"></i> Editar
                                            </button>
                                            <button class="btn btn-accion btn-eliminar" data-bs-toggle="modal" data-bs-target="#eliminarModal<?= $categoria['id'] ?>">
                                                <i class="fas fa-trash-alt"></i> Eliminar
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Modal Editar -->
                                    <div class="modal fade" id="editarModal<?= $categoria['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">
                                                        <i class="fas fa-pencil-alt"></i> Editar Categoría
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="index.php?mod=CrearCategoria">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id" value="<?= $categoria['id'] ?>">
                                                        <input type="hidden" name="accion" value="editar">
                                                        <div class="mb-3">
                                                            <label class="form-label">Nombre de la Categoría:</label>
                                                            <input type="text" class="form-control" name="nombre" value="<?= htmlspecialchars($categoria['nombre']) ?>" placeholder="Nombre de categoría" data-validation-type="titulo" required>
                                                            <small class="form-text">🔒 3-100 caracteres</small>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="fas fa-check-circle"></i> Guardar Cambios
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modal Eliminar -->
                                    <div class="modal fade" id="eliminarModal<?= $categoria['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title text-danger">
                                                        <i class="fas fa-exclamation-triangle"></i> Eliminar Categoría
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="index.php?mod=CrearCategoria">
                                                    <div class="modal-body">
                                                        <p class="mb-2">¿Está seguro de que desea eliminar esta categoría?</p>
                                                        <p class="fw-bold text-danger">
                                                            <i class="fas fa-exclamation-circle"></i>
                                                            <?= htmlspecialchars($categoria['nombre']) ?>
                                                        </p>
                                                        <small class="form-text">⚠️ Esta acción no se puede deshacer</small>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <input type="hidden" name="id_eliminar" value="<?= $categoria['id'] ?>">
                                                        <input type="hidden" name="accion" value="eliminar">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                        <button type="submit" class="btn btn-danger">
                                                            <i class="fas fa-trash-alt"></i> Eliminar
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="no-datos">
                        <i class="fas fa-inbox"></i>
                        <h5>No hay categorías registradas</h5>
                        <p class="text-muted">Comienza creando una nueva categoría</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Modal Crear -->
            <div class="modal fade" id="crearModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-plus-circle"></i> Crear Categoría
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="index.php?mod=CrearCategoria" method="POST">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="nombre" class="form-label">Nombre de la Categoría:</label>
                                    <input type="text" class="form-control" name="nombre" id="nombre" placeholder="Ej: Ropa, Electrónica, etc." required autofocus>
                                    <small class="form-text">🔒 3-100 caracteres</small>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-check-circle"></i> Crear Categoría
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- Scripts -->
    <script src="<?= dirname(dirname(__DIR__)) ?>/assets/js/validaciones.js"></script>
    
    <script>
        // Validar formulario de crear categoría
        document.getElementById('crearModal').addEventListener('show.bs.modal', function() {
            const form = this.querySelector('form');
            const input = form.querySelector('input[name="nombre"]');
            input.value = ''; // Limpiar campo
            input.focus();
        });

        // Validar todos los formularios al enviar
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const nombreInput = this.querySelector('input[name="nombre"]');
                
                if (!nombreInput) return; // Si no hay campo nombre, es otro formulario
                
                // Validar el campo nombre
                const validar = ValidationSystem.validar('titulo', nombreInput.value);
                
                if (!validar.valido) {
                    e.preventDefault();
                    AlertSystem.mostrar('validation', '🔒 Validación', validar.mensaje);
                    nombreInput.focus();
                    nombreInput.classList.add('is-invalid');
                    return;
                }
                
                // Si es válido, remover clase de error si existe
                nombreInput.classList.remove('is-invalid');
            });
        });

        // Limpiar estado de validación al abrir modales
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('show.bs.modal', function() {
                this.querySelectorAll('input').forEach(input => {
                    input.classList.remove('is-invalid');
                });
            });
        });
    </script>