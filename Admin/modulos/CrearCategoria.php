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

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Categorías</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
        }
        
        .header-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header-section h1 {
            margin-bottom: 0;
            font-weight: 700;
            font-size: 2rem;
        }
        
        .header-section p {
            margin: 0;
            font-size: 0.95rem;
        }
        
        .header-content {
            flex: 1;
        }
        
        .btn-crear {
            background: white;
            color: #667eea;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-crear:hover {
            background: #f0f0f0;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            color: #667eea;
        }
        
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
        }
        
        .table tbody tr {
            border-bottom: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .table tbody tr:hover {
            background-color: #f8f9fa;
            transform: translateX(3px);
        }
        
        .btn-accion {
            padding: 6px 12px;
            font-size: 0.85rem;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .btn-editar {
            background: #3498db;
            color: white;
            border: none;
        }
        
        .btn-editar:hover {
            background: #2980b9;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }
        
        .btn-eliminar {
            background: #e74c3c;
            color: white;
            border: none;
        }
        
        .btn-eliminar:hover {
            background: #c0392b;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.4);
        }
        
        .no-datos {
            text-align: center;
            padding: 50px;
            color: #7f8c8d;
        }
        
        .no-datos i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #bdc3c7;
        }
        
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }
        
        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }

        .alert-success {
            background: #d4edda;
            border: 2px solid #28a745;
            color: #155724;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .alert-error {
            background: #f8d7da;
            border: 2px solid #dc3545;
            color: #721c24;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <main>
        <div class="container-fluid px-4 py-5">
            
            <!-- MENSAJES -->
            <?php if (!empty($mensaje)): ?>
                <div class="alert-<?= $tipo_mensaje ?>">
                    <strong><?= $tipo_mensaje === 'success' ? '✅ Éxito' : '❌ Error' ?></strong><br>
                    <?= htmlspecialchars($mensaje) ?>
                </div>
            <?php endif; ?>
            
            <!-- HEADER -->
            <div class="header-section">
                <div class="header-content">
                    <h1><i class="bi bi-tag"></i> Gestionar Categorías</h1>
                    <p class="mt-2">Administra todas las categorías de tu tienda</p>
                </div>
                <button type="button" class="btn btn-crear" data-bs-toggle="modal" data-bs-target="#crearModal">
                    <i class="bi bi-plus-circle"></i> Crear Categoría
                </button>
            </div>

            <!-- TABLA CATEGORÍAS -->
            <div class="table-container">
                <?php if (count($categorias) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th width="15%"><i class="bi bi-hash"></i> ID</th>
                                    <th width="60%"><i class="bi bi-tag"></i> Nombre</th>
                                    <th width="25%" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categorias as $categoria): ?>
                                    <tr>
                                        <td>
                                            <span class="badge" style="background: #667eea; font-size: 0.9rem;">
                                                #<?= $categoria['id'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($categoria['nombre']) ?></strong>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-accion btn-editar" data-bs-toggle="modal" data-bs-target="#editarModal<?= $categoria['id'] ?>">
                                                <i class="bi bi-pencil-square"></i> Editar
                                            </button>
                                            <button class="btn btn-accion btn-eliminar" data-bs-toggle="modal" data-bs-target="#eliminarModal<?= $categoria['id'] ?>">
                                                <i class="bi bi-trash"></i> Eliminar
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Modal Editar -->
                                    <div class="modal fade" id="editarModal<?= $categoria['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Editar Categoría</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="index.php?mod=CrearCategoria">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id" value="<?= $categoria['id'] ?>">
                                                        <input type="hidden" name="accion" value="editar">
                                                        <div class="mb-3">
                                                            <label class="form-label">Nombre de la Categoría:</label>
                                                            <input type="text" class="form-control" name="nombre" value="<?= htmlspecialchars($categoria['nombre']) ?>" placeholder="Nombre de categoría" data-validation-type="titulo" required>
                                                            <small class="text-muted d-block mt-1">🔒 3-100 caracteres</small>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="bi bi-check-circle"></i> Guardar Cambios
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
                                                <div class="modal-header border-danger">
                                                    <h5 class="modal-title text-danger"><i class="bi bi-exclamation-triangle"></i> Eliminar Categoría</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="index.php?mod=CrearCategoria">
                                                    <div class="modal-body">
                                                        <p class="mb-2">¿Está seguro de que desea eliminar esta categoría?</p>
                                                        <p class="fw-bold text-danger">
                                                            <i class="bi bi-exclamation-circle"></i>
                                                            <?= htmlspecialchars($categoria['nombre']) ?>
                                                        </p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <input type="hidden" name="id_eliminar" value="<?= $categoria['id'] ?>">
                                                        <input type="hidden" name="accion" value="eliminar">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                        <button type="submit" class="btn btn-danger">
                                                            <i class="bi bi-trash"></i> Eliminar
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
                        <i class="bi bi-inbox"></i>
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
                            <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Crear Categoría</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="index.php?mod=CrearCategoria" method="POST">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="nombre" class="form-label">Nombre de la Categoría:</label>
                                    <input type="text" class="form-control" name="nombre" id="nombre" placeholder="Ej: Ropa, Electrónica, etc." required autofocus>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Crear Categoría
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
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
</body>
</html>

