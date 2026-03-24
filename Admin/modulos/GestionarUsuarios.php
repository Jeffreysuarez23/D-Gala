<?php
/**
 * ESTA TAREA LA HIZO: Jhon Perna
 * SU REQUERIMIENTO FUE: RF-005 - Editar Administrador / RF-006 - Eliminar Admin / 
 *                       RF-007 - Log de Accesos Admin / RF-008 - Búsqueda de Acciones Admin
 * PERTENECE A ESTE ARCHIVO: Admin/modulos/GestionarUsuarios.php
 */

require_once("include/db.php");
require_once("include/configuracionesAD.php");
require_once("include/admin.php");

$db = new Database();
$conexion = $db->getConexion();
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'usuarios';

// Procesar cambio de contraseña y acciones para usuarios clientes
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion'])) {
    if ($_POST['accion'] == 'cambiar_password') {
        $user_id = intval($_POST['user_id']);
        $password = trim($_POST['password']);
        $repassword = trim($_POST['repassword']);
        $tipo_user = $_POST['tipo_user'] ?? 'usuario';
        
        if (esNulo([$password, $repassword])) {
            echo "<script>alert('Debe llenar todos los campos'); window.location.href='index.php?mod=GestionarUsuarios&tipo=$tipo_user';</script>";
        } elseif (!validarPassword($password, $repassword)) {
            echo "<script>alert('Las contraseñas no coinciden'); window.location.href='index.php?mod=GestionarUsuarios&tipo=$tipo_user';</script>";
        } else {
            $pass_hash = password_hash($password, PASSWORD_DEFAULT);
            if ($tipo_user === 'admin') {
                $sql = $conexion->prepare("UPDATE admin SET password = ? WHERE id = ?");
                $resultado = $sql->execute([$pass_hash, $user_id]);
            } else {
                $resultado = ActualizarPassword($user_id, $pass_hash, $conexion);
            }
            if ($resultado) {
                echo "<script>alert('Contraseña actualizada correctamente'); window.location.href='index.php?mod=GestionarUsuarios&tipo=$tipo_user';</script>";
                exit;
            } else {
                echo "<script>alert('Error al actualizar la contraseña'); window.location.href='index.php?mod=GestionarUsuarios&tipo=$tipo_user';</script>";
            }
        }
    } elseif ($_POST['accion'] == 'deshabilitar' && $_POST['tipo_user'] === 'usuario') {
        $id = intval($_POST['id']);
        $sql = $conexion->prepare("UPDATE usuarios SET activacion = 2 WHERE id = ?");
        if ($sql->execute([$id])) {
            echo "<script>alert('Usuario deshabilitado correctamente'); window.location.href='index.php?mod=GestionarUsuarios&tipo=usuario';</script>";
            exit;
        }
    } elseif ($_POST['accion'] == 'habilitar' && $_POST['tipo_user'] === 'usuario') {
        $id = intval($_POST['id']);
        $sql = $conexion->prepare("UPDATE usuarios SET activacion = 1 WHERE id = ?");
        if ($sql->execute([$id])) {
            echo "<script>alert('Usuario habilitado correctamente'); window.location.href='index.php?mod=GestionarUsuarios&tipo=usuario';</script>";
            exit;
        }
    } elseif ($_POST['accion'] == 'deshabilitar_admin') {
        $id = intval($_POST['id']);
        $sql = $conexion->prepare("UPDATE admin SET activo = 0 WHERE id = ?");
        if ($sql->execute([$id])) {
            echo "<script>alert('Administrador deshabilitado correctamente'); window.location.href='index.php?mod=GestionarUsuarios&tipo=admin';</script>";
            exit;
        }
    } elseif ($_POST['accion'] == 'habilitar_admin') {
        $id = intval($_POST['id']);
        $sql = $conexion->prepare("UPDATE admin SET activo = 1 WHERE id = ?");
        if ($sql->execute([$id])) {
            echo "<script>alert('Administrador habilitado correctamente'); window.location.href='index.php?mod=GestionarUsuarios&tipo=admin';</script>";
            exit;
        }
    }
}

// Búsqueda para usuarios clientes
$busqueda_usuarios = isset($_GET['busqueda_usuarios']) ? trim($_GET['busqueda_usuarios']) : '';
$sql = "SELECT usuarios.id, 
               CONCAT(clientes.nombre, ' ', clientes.apellido) AS cliente, 
               usuarios.nombre_usuario, 
               usuarios.activacion,
               clientes.email,
        CASE 
            WHEN usuarios.activacion = 1 THEN 'Activo'
            WHEN usuarios.activacion = 0 THEN 'No Activado'
            ELSE 'Deshabilitado'
        END AS estatus
        FROM usuarios
        INNER JOIN clientes ON usuarios.id_cliente = clientes.id";

if ($busqueda_usuarios) {
    $sql .= " WHERE usuarios.nombre_usuario LIKE :busqueda 
              OR clientes.nombre LIKE :busqueda 
              OR clientes.apellido LIKE :busqueda 
              OR clientes.email LIKE :busqueda";
}

$stmt = $conexion->prepare($sql);
if ($busqueda_usuarios) {
    $stmt->execute([':busqueda' => '%' . $busqueda_usuarios . '%']);
} else {
    $stmt->execute();
}
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Búsqueda para administradores
$busqueda_admins = isset($_GET['busqueda_admins']) ? trim($_GET['busqueda_admins']) : '';
$sql_admin = "SELECT id, usuario, nombre, email, activo,
              CASE 
                  WHEN activo = 1 THEN 'Activo'
                  ELSE 'Inactivo'
              END AS estatus
              FROM admin WHERE 1=1";

if ($busqueda_admins) {
    $sql_admin .= " AND (usuario LIKE :busqueda OR nombre LIKE :busqueda OR email LIKE :busqueda)";
}

$stmt_admin = $conexion->prepare($sql_admin);
if ($busqueda_admins) {
    $stmt_admin->execute([':busqueda' => '%' . $busqueda_admins . '%']);
} else {
    $stmt_admin->execute();
}
$administradores = $stmt_admin->fetchAll(PDO::FETCH_ASSOC);
?>


<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        --primary: #6366f1;
        --secondary: #8b5cf6;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --info: #3b82f6;
        --dark: #1f2937;
        --light: #f9fafb;
    }

    * {
        transition: all 0.3s ease;
    }

    body {
        background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
        min-height: 100vh;
    }

    main {
        padding: 2rem 0;
    }

    .page-header {
        background: var(--primary-gradient);
        color: white;
        padding: 2.5rem 2rem;
        border-radius: 16px;
        margin-bottom: 2rem;
        box-shadow: 0 20px 50px rgba(99, 102, 241, 0.2);
        position: relative;
        overflow: hidden;
    }

    .page-header h1 {
        margin: 0;
        font-size: 2.5rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 1rem;
        color: white;
    }

    .page-header i {
        font-size: 2.5rem;
    }

    .filter-section {
        padding: 2rem;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        margin-bottom: 2rem;
    }

    .filter-section h5 {
        color: var(--dark);
        font-weight: 600;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .search-input {
        position: relative;
    }

    .search-input input {
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        padding: 0.75rem 1rem 0.75rem 2.75rem;
        width: 100%;
        background: #f9fafb;
    }

    .search-input input:focus {
        border-color: var(--primary);
        background: white;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        outline: none;
    }

    .search-input i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
    }

    .table-container {
        overflow-x: auto;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .table {
        margin-bottom: 0;
        background: white;
    }

    .table thead {
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        border-bottom: 2px solid #e5e7eb;
    }

    .table thead th {
        color: var(--dark);
        font-weight: 700;
        padding: 1.25rem;
        border: none;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }

    .table tbody tr {
        border-bottom: 1px solid #f3f4f6;
    }

    .table tbody tr:hover {
        background: linear-gradient(90deg, #f9fafb 0%, #f3f4f6 100%);
    }

    .table tbody td {
        padding: 1rem 1.25rem;
        vertical-align: middle;
        color: var(--dark);
    }

    .table strong {
        color: var(--primary);
        font-weight: 700;
    }

    .badge-estatus {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .badge-activo {
        background: rgba(16, 185, 129, 0.15);
        color: var(--success);
    }

    .badge-pendiente {
        background: rgba(245, 158, 11, 0.15);
        color: var(--warning);
    }

    .badge-deshabilitado {
        background: rgba(239, 68, 68, 0.15);
        color: var(--danger);
    }

    .btn-accion {
        padding: 0.5rem 1rem;
        border-radius: 6px;
        border: none;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        margin: 0 0.25rem;
    }

    .btn-password {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
    }

    .btn-password:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(59, 130, 246, 0.3);
        color: white;
    }

    .btn-deshabilitar {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }

    .btn-deshabilitar:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(245, 158, 11, 0.3);
        color: white;
    }

    .btn-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        border: none;
    }

    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(239, 68, 68, 0.3);
        color: white;
    }

    .no-usuarios {
        text-align: center;
        padding: 3rem 2rem;
        color: #9ca3af;
    }

    .no-usuarios i {
        font-size: 3rem;
        margin-bottom: 1rem;
        color: #d1d5db;
    }

    .modal-content {
        border: none;
        border-radius: 12px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
    }

    .modal-header {
        background: var(--primary-gradient);
        color: white;
        border: none;
        border-radius: 12px 12px 0 0;
    }

    .modal-header .btn-close {
        filter: brightness(0) invert(1);
    }

    .modal-title {
        font-weight: 700;
        font-size: 1.5rem;
    }

    .form-control {
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        background: #f9fafb;
    }

    .form-control:focus {
        border-color: var(--primary);
        background: white;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    .form-label {
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 0.5rem;
    }

    .btn-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        font-size: 1rem;
        transition: all 0.3s ease;
        margin: 0 0.25rem;
    }

    .btn-edit {
        background: #367dda;
        color: var(--info);
    }

    .btn-edit:hover {
        background: var(--info);
        color: white;
        transform: translateY(-2px);
    }

    .btn-delete {
        background: #eb1111;
        color: var(--danger);
    }

    .btn-delete:hover {
        background: var(--danger);
        color: white;
        transform: translateY(-2px);
    }

    .btn-enable {
        background: #0fe978;
        color: var(--success);
    }

    .btn-enable:hover {
        background: var(--success);
        color: white;
        transform: translateY(-2px);
    }

    .empty-state {
        text-align: center;
        padding: 3rem 2rem;
        color: #9ca3af;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .empty-state p {
        font-size: 1.1rem;
    }

    .search-row {
        display: grid;
        grid-template-columns: 1fr auto auto;
        gap: 1rem;
        align-items: flex-end;
    }

    .search-input {
        position: relative;
    }

    .search-input input {
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        padding: 0.75rem 1rem 0.75rem 2.75rem;
        width: 100%;
        background: #f9fafb;
        font-weight: 500;
    }

    .search-input input::placeholder {
        color: #9ca3af;
    }

    .search-input input:focus {
        border-color: var(--primary);
        background: white;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        outline: none;
    }

    .search-input i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
    }

    .btn-search {
        background: var(--primary-gradient);
        color: white;
        border: none;
        border-radius: 8px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-search:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);
        color: white;
    }

    .btn-clear {
        background: #e5e7eb;
        color: var(--dark);
        border: none;
        border-radius: 8px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-clear:hover {
        background: var(--dark);
        color: white;
    }

    .page-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }

    .page-header h1 {
        margin: 0;
        font-size: 2.5rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 1rem;
        position: relative;
        z-index: 1;
        margin-bottom: 0.5rem;
    }

    .tabs-navs {
        display: flex;
        gap: 2rem;
        margin-bottom: 2rem;
        border-bottom: 2px solid #e5e7eb;
        padding-bottom: 1rem;
    }

    .tab-button {
        padding: 0.75rem 1.5rem;
        border: none;
        background: transparent;
        color: var(--dark);
        cursor: pointer;
        font-weight: 700;
        position: relative;
        font-size: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
    }

    .tab-button.active {
        color: var(--primary);
    }

    .tab-button.active::after {
        content: '';
        position: absolute;
        bottom: -1rem;
        left: 0;
        right: 0;
        height: 3px;
        background: var(--primary-gradient);
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
        animation: fadeInTab 0.3s ease;
    }

    @keyframes fadeInTab {
        from {
            opacity: 0;
            transform: translateY(5px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        border-left: 4px solid var(--primary);
    }

    .stat-card h6 {
        color: #6b7280;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        margin: 0 0 1rem 0;
    }

    .stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--primary);
    }

    .btn-primary {
        background: var(--primary-gradient);
        border: none;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);
    }

    .btn-success {
        background: var(--success);
        border: none;
    }

    .btn-success:hover {
        background: #059669;
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3);
    }

    .btn-secondary {
        background: #e5e7eb;
        color: var(--dark);
        border: none;
    }

    .btn-secondary:hover {
        background: #d1d5db;
        color: var(--dark);
    }

    @media (max-width: 768px) {
        .page-header h1 {
            font-size: 1.75rem;
        }

        .page-header i {
            font-size: 1.75rem;
        }

        .search-row {
            grid-template-columns: 1fr;
        }

        .table {
            font-size: 0.9rem;
        }

        .table th, .table td {
            padding: 0.75rem 0.5rem;
        }

        .btn-icon {
            width: 32px;
            height: 32px;
            font-size: 0.9rem;
        }
    }
</style>

<div class="page-header">
    <h1>
        <i class="bi bi-people-fill"></i>
        Gestionar Accesos
    </h1>
    <p>Administra y controla usuarios clientes y administradores</p>
</div>

<div class="container-fluid" style="max-width: 1400px; margin: 0 auto;">
    <!-- Navegación de pestañas -->
    <div class="tabs-navs" style="background: white; padding: 1rem 2rem; border-radius: 12px; margin-bottom: 2rem; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);">
        <button class="tab-button <?= $tipo !== 'admin' ? 'active' : '' ?>" onclick="cambiarTab('usuarios', this)">
            <i class="bi bi-people"></i> Usuarios Cliente
        </button>
        <button class="tab-button <?= $tipo === 'admin' ? 'active' : '' ?>" onclick="cambiarTab('admin', this)">
            <i class="bi bi-shield-lock"></i> Administradores
        </button>
    </div>

    <!-- SECCIÓN 1: USUARIOS CLIENTES -->
    <div id="usuarios" class="tab-content <?= $tipo !== 'admin' ? 'active' : '' ?>">
        <!-- Estadísticas rápidas - Usuarios -->
        <div class="stats-row">
            <div class="stat-card">
                <h6><i class="bi bi-person-check"></i> Usuarios Activos</h6>
                <div class="stat-value"><?= array_reduce($usuarios, fn($c, $r) => $c + ($r["activacion"] == 1 ? 1 : 0), 0) ?></div>
            </div>
            <div class="stat-card" style="border-left-color: var(--warning);">
                <h6><i class="bi bi-clock-history"></i> Pendientes</h6>
                <div class="stat-value" style="color: var(--warning);"><?= array_reduce($usuarios, fn($c, $r) => $c + ($r["activacion"] == 0 ? 1 : 0), 0) ?></div>
            </div>
            <div class="stat-card" style="border-left-color: var(--danger);">
                <h6><i class="bi bi-person-x"></i> Deshabilitados</h6>
                <div class="stat-value" style="color: var(--danger);"><?= array_reduce($usuarios, fn($c, $r) => $c + ($r["activacion"] == 2 ? 1 : 0), 0) ?></div>
            </div>
        </div>

        <!-- Filtros y búsqueda - Usuarios -->
        <div class="filter-section">
            <h5><i class="bi bi-funnel"></i> Filtrar y Buscar</h5>
            <form method="GET" action="index.php" id="searchFormUsuarios">
                <input type="hidden" name="mod" value="GestionarUsuarios">
                <input type="hidden" name="tipo" value="usuarios">
                <div class="search-row">
                    <div class="search-input">
                        <i class="bi bi-search"></i>
                        <input type="text" name="busqueda_usuarios" placeholder="Buscar por nombre, usuario, email..." value="<?= htmlspecialchars($busqueda_usuarios) ?>" autofocus>
                    </div>
                    <button type="submit" class="btn-search">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                    <?php if ($busqueda_usuarios): ?>
                        <a href="index.php?mod=GestionarUsuarios&tipo=usuarios" class="btn-clear">
                            <i class="bi bi-x-circle"></i> Limpiar
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Tabla de usuarios clientes -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th><i class="bi bi-hash"></i> ID</th>
                        <th><i class="bi bi-person"></i> Cliente</th>
                        <th><i class="bi bi-user-circle"></i> Usuario</th>
                        <th><i class="bi bi-envelope"></i> Email</th>
                        <th><i class="bi bi-info-circle"></i> Estatus</th>
                        <th><i class="bi bi-gear"></i> Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($usuarios) > 0): ?>
                        <?php foreach ($usuarios as $row): ?>
                            <tr>
                                <td><strong>#<?= htmlspecialchars($row["id"]) ?></strong></td>
                                <td><?= htmlspecialchars($row["cliente"]) ?></td>
                                <td><strong><?= htmlspecialchars($row["nombre_usuario"]) ?></strong></td>
                                <td><?= htmlspecialchars($row["email"]) ?></td>
                                <td>
                                    <span class="badge-estatus badge-<?= $row["activacion"] == 1 ? 'activo' : ($row["activacion"] == 0 ? 'pendiente' : 'deshabilitado') ?>">
                                        <?= htmlspecialchars($row["estatus"]) ?>
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn-icon btn-edit" data-bs-toggle="modal" data-bs-target="#cambiarPasswordModal" data-user-id="<?=$row["id"] ?>" data-usuario="<?= htmlspecialchars($row["nombre_usuario"]) ?>" data-tipo="usuario" title="Cambiar contraseña">
                                        <i class="bi bi-key"></i>
                                    </button>
                                    <?php if ($row["activacion"] == 2): ?>
                                        <button type="button" class="btn-icon btn-enable" data-bs-toggle="modal" data-bs-target="#habilitarModal" data-user-id="<?= $row["id"] ?>" data-usuario="<?= htmlspecialchars($row["nombre_usuario"]) ?>" data-tipo="usuario" title="Habilitar usuario">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn-icon btn-delete" data-bs-toggle="modal" data-bs-target="#deshabilitarModal" data-user-id="<?= $row["id"] ?>" data-usuario="<?= htmlspecialchars($row["nombre_usuario"]) ?>" data-tipo="usuario" title="Deshabilitar usuario">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <p>No se encontraron usuarios clientes</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- SECCIÓN 2: ADMINISTRADORES -->
    <div id="admin" class="tab-content <?= $tipo === 'admin' ? 'active' : '' ?>">
        <!-- Estadísticas rápidas - Administradores -->
        <div class="stats-row">
            <div class="stat-card">
                <h6><i class="bi bi-shield-check"></i> Administradores Activos</h6>
                <div class="stat-value"><?= array_reduce($administradores, fn($c, $r) => $c + ($r["activo"] == 1 ? 1 : 0), 0) ?></div>
            </div>
            <div class="stat-card" style="border-left-color: var(--danger);">
                <h6><i class="bi bi-shield-x"></i> Inactivos</h6>
                <div class="stat-value" style="color: var(--danger);"><?= array_reduce($administradores, fn($c, $r) => $c + ($r["activo"] == 0 ? 1 : 0), 0) ?></div>
            </div>
            <div class="stat-card" style="border-left-color: var(--info);">
                <h6><i class="bi bi-people-fill"></i> Total</h6>
                <div class="stat-value" style="color: var(--info);"><?= count($administradores) ?></div>
            </div>
        </div>

        <!-- Filtros y búsqueda - Administradores -->
        <div class="filter-section">
            <h5><i class="bi bi-funnel"></i> Filtrar y Buscar</h5>
            <form method="GET" action="index.php" id="searchFormAdmin">
                <input type="hidden" name="mod" value="GestionarUsuarios">
                <input type="hidden" name="tipo" value="admin">
                <div class="search-row">
                    <div class="search-input">
                        <i class="bi bi-search"></i>
                        <input type="text" name="busqueda_admins" placeholder="Buscar por usuario, nombre, email..." value="<?= htmlspecialchars($busqueda_admins) ?>" autofocus>
                    </div>
                    <button type="submit" class="btn-search">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                    <?php if ($busqueda_admins): ?>
                        <a href="index.php?mod=GestionarUsuarios&tipo=admin" class="btn-clear">
                            <i class="bi bi-x-circle"></i> Limpiar
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Tabla de administradores -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th><i class="bi bi-hash"></i> ID</th>
                        <th><i class="bi bi-user-circle"></i> Usuario</th>
                        <th><i class="bi bi-person"></i> Nombre</th>
                        <th><i class="bi bi-envelope"></i> Email</th>
                        <th><i class="bi bi-info-circle"></i> Estatus</th>
                        <th><i class="bi bi-gear"></i> Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($administradores) > 0): ?>
                        <?php foreach ($administradores as $admin): ?>
                            <tr>
                                <td><strong>#<?= htmlspecialchars($admin["id"]) ?></strong></td>
                                <td><strong><?= htmlspecialchars($admin["usuario"]) ?></strong></td>
                                <td><?= htmlspecialchars($admin["nombre"]) ?></td>
                                <td><?= htmlspecialchars($admin["email"]) ?></td>
                                <td>
                                    <span class="badge-estatus badge-<?= $admin["activo"] == 1 ? 'activo' : 'deshabilitado' ?>">
                                        <?= htmlspecialchars($admin["estatus"]) ?>
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn-icon btn-edit" data-bs-toggle="modal" data-bs-target="#cambiarPasswordModalAdmin" data-user-id="<?=$admin["id"] ?>" data-usuario="<?= htmlspecialchars($admin["usuario"]) ?>" data-tipo="admin" title="Cambiar contraseña">
                                        <i class="bi bi-key"></i>
                                    </button>
                                    <?php if ($admin["activo"] == 0): ?>
                                        <button type="button" class="btn-icon btn-enable" data-bs-toggle="modal" data-bs-target="#habilitarModalAdmin" data-user-id="<?= $admin["id"] ?>" data-usuario="<?= htmlspecialchars($admin["usuario"]) ?>" title="Habilitar administrador">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn-icon btn-delete" data-bs-toggle="modal" data-bs-target="#deshabilitarModalAdmin" data-user-id="<?= $admin["id"] ?>" data-usuario="<?= htmlspecialchars($admin["usuario"]) ?>" title="Deshabilitar administrador">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <p>No se encontraron administradores</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Cambiar Contraseña - Usuarios -->
<div class="modal fade" id="cambiarPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-key"></i> Cambiar Contraseña Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" ></button>
            </div>
            <form method="POST" action="index.php?mod=GestionarUsuarios">
                <div class="modal-body">
                    <input type="hidden" name="accion" value="cambiar_password">
                    <input type="hidden" name="tipo_user" value="usuario">
                    <input type="hidden" name="user_id" id="passwordUserId">
                    
                    <div class="mb-3">
                        <label for="usuarioDisplay" class="form-label">Usuario</label>
                        <input type="text" class="form-control" id="usuarioDisplay" disabled>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Nueva Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="repassword" class="form-label">Confirmar Contraseña</label>
                        <input type="password" class="form-control" id="repassword" name="repassword" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Cambiar Contraseña
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Deshabilitar Usuario -->
<div class="modal fade" id="deshabilitarModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Deshabilitar Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="index.php?mod=GestionarUsuarios">
                <div class="modal-body">
                    <input type="hidden" name="accion" value="deshabilitar">
                    <input type="hidden" name="tipo_user" value="usuario">
                    <input type="hidden" name="id" id="deshabilitarUserId">
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle"></i>
                        ¿Está seguro que desea <strong>deshabilitar</strong> al usuario <strong id="deshabilitarUsuario"></strong>?
                    </div>
                    <p class="text-muted">El usuario no podrá acceder a su cuenta hasta que sea habilitado nuevamente.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle"></i> Sí, Deshabilitar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Habilitar Usuario -->
<div class="modal fade" id="habilitarModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-check-circle"></i> Habilitar Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="index.php?mod=GestionarUsuarios">
                <div class="modal-body">
                    <input type="hidden" name="accion" value="habilitar">
                    <input type="hidden" name="tipo_user" value="usuario">
                    <input type="hidden" name="id" id="habilitarUserId">
                    
                    <div class="alert alert-success">
                        <i class="bi bi-info-circle"></i>
                        ¿Está seguro que desea <strong>habilitar</strong> al usuario <strong id="habilitarUsuario"></strong>?
                    </div>
                    <p class="text-muted">El usuario podrá acceder nuevamente a su cuenta.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Sí, Habilitar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Cambiar Contraseña - Administrador -->
<div class="modal fade" id="cambiarPasswordModalAdmin" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-key"></i> Cambiar Contraseña Administrador</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" ></button>
            </div>
            <form method="POST" action="index.php?mod=GestionarUsuarios">
                <div class="modal-body">
                    <input type="hidden" name="accion" value="cambiar_password">
                    <input type="hidden" name="tipo_user" value="admin">
                    <input type="hidden" name="user_id" id="passwordAdminId">
                    
                    <div class="mb-3">
                        <label for="usuarioAdminDisplay" class="form-label">Usuario</label>
                        <input type="text" class="form-control" id="usuarioAdminDisplay" disabled>
                    </div>
                    
                    <div class="mb-3">
                        <label for="passwordAdmin" class="form-label">Nueva Contraseña</label>
                        <input type="password" class="form-control" id="passwordAdmin" name="password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="repasswordAdmin" class="form-label">Confirmar Contraseña</label>
                        <input type="password" class="form-control" id="repasswordAdmin" name="repassword" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Cambiar Contraseña
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Deshabilitar Administrador -->
<div class="modal fade" id="deshabilitarModalAdmin" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Deshabilitar Administrador</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="index.php?mod=GestionarUsuarios">
                <div class="modal-body">
                    <input type="hidden" name="accion" value="deshabilitar_admin">
                    <input type="hidden" name="id" id="deshabilitarAdminId">
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle"></i>
                        ¿Está seguro que desea <strong>deshabilitar</strong> al administrador <strong id="deshabilitarAdminUsuario"></strong>?
                    </div>
                    <p class="text-muted">El administrador no podrá acceder al panel administrativo hasta que sea habilitado nuevamente.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle"></i> Sí, Deshabilitar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Habilitar Administrador -->
<div class="modal fade" id="habilitarModalAdmin" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-check-circle"></i> Habilitar Administrador</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="index.php?mod=GestionarUsuarios">
                <div class="modal-body">
                    <input type="hidden" name="accion" value="habilitar_admin">
                    <input type="hidden" name="id" id="habilitarAdminId">
                    
                    <div class="alert alert-success">
                        <i class="bi bi-info-circle"></i>
                        ¿Está seguro que desea <strong>habilitar</strong> al administrador <strong id="habilitarAdminUsuario"></strong>?
                    </div>
                    <p class="text-muted">El administrador podrá acceder nuevamente al panel administrativo.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Sí, Habilitar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
            </form>
        </div>
    </div>
</div>

<script>
    // Función para cambiar tabs
    function cambiarTab(tabId, button) {
        // Ocultar todos los tabs
        const tabContents = document.querySelectorAll('.tab-content');
        tabContents.forEach(tab => tab.classList.remove('active'));

        // Remover clase active de todos los botones
        const buttons = document.querySelectorAll('.tab-button');
        buttons.forEach(btn => btn.classList.remove('active'));

        // Mostrar el tab seleccionado
        document.getElementById(tabId).classList.add('active');
        button.classList.add('active');
    }

    // Modal Cambiar Contraseña - Usuarios
    const cambiarPasswordModal = document.getElementById('cambiarPasswordModal');
    if (cambiarPasswordModal) {
        cambiarPasswordModal.addEventListener('show.bs.modal', function (e) {
            const button = e.relatedTarget;
            const userId = button.getAttribute('data-user-id');
            const usuario = button.getAttribute('data-usuario');
            
            document.getElementById('passwordUserId').value = userId;
            document.getElementById('usuarioDisplay').value = usuario;
            document.getElementById('password').value = '';
            document.getElementById('repassword').value = '';
            document.getElementById('password').focus();
        });
    }

    // Modal Deshabilitar Usuario
    const deshabilitarModal = document.getElementById('deshabilitarModal');
    if (deshabilitarModal) {
        deshabilitarModal.addEventListener('show.bs.modal', function (e) {
            const button = e.relatedTarget;
            const userId = button.getAttribute('data-user-id');
            const usuario = button.getAttribute('data-usuario');
            
            document.getElementById('deshabilitarUserId').value = userId;
            document.getElementById('deshabilitarUsuario').textContent = usuario;
        });
    }

    // Modal Habilitar Usuario
    const habilitarModal = document.getElementById('habilitarModal');
    if (habilitarModal) {
        habilitarModal.addEventListener('show.bs.modal', function (e) {
            const button = e.relatedTarget;
            const userId = button.getAttribute('data-user-id');
            const usuario = button.getAttribute('data-usuario');
            
            document.getElementById('habilitarUserId').value = userId;
            document.getElementById('habilitarUsuario').textContent = usuario;
        });
    }

    // Modal Cambiar Contraseña - Administrador
    const cambiarPasswordModalAdmin = document.getElementById('cambiarPasswordModalAdmin');
    if (cambiarPasswordModalAdmin) {
        cambiarPasswordModalAdmin.addEventListener('show.bs.modal', function (e) {
            const button = e.relatedTarget;
            const userId = button.getAttribute('data-user-id');
            const usuario = button.getAttribute('data-usuario');
            
            document.getElementById('passwordAdminId').value = userId;
            document.getElementById('usuarioAdminDisplay').value = usuario;
            document.getElementById('passwordAdmin').value = '';
            document.getElementById('repasswordAdmin').value = '';
            document.getElementById('passwordAdmin').focus();
        });
    }

    // Modal Deshabilitar Administrador
    const deshabilitarModalAdmin = document.getElementById('deshabilitarModalAdmin');
    if (deshabilitarModalAdmin) {
        deshabilitarModalAdmin.addEventListener('show.bs.modal', function (e) {
            const button = e.relatedTarget;
            const userId = button.getAttribute('data-user-id');
            const usuario = button.getAttribute('data-usuario');
            
            document.getElementById('deshabilitarAdminId').value = userId;
            document.getElementById('deshabilitarAdminUsuario').textContent = usuario;
        });
    }

    // Modal Habilitar Administrador
    const habilitarModalAdmin = document.getElementById('habilitarModalAdmin');
    if (habilitarModalAdmin) {
        habilitarModalAdmin.addEventListener('show.bs.modal', function (e) {
            const button = e.relatedTarget;
            const userId = button.getAttribute('data-user-id');
            const usuario = button.getAttribute('data-usuario');
            
            document.getElementById('habilitarAdminId').value = userId;
            document.getElementById('habilitarAdminUsuario').textContent = usuario;
        });
    }

    // Búsqueda al presionar Enter - Usuarios
    const busquedaUsuarios = document.querySelector('input[name="busqueda_usuarios"]');
    if (busquedaUsuarios) {
        busquedaUsuarios.addEventListener('keyup', function(e) {
            if(e.key === 'Enter') {
                document.getElementById('searchFormUsuarios').submit();
            }
        });
    }

    // Búsqueda al presionar Enter - Administradores
    const busquedaAdmins = document.querySelector('input[name="busqueda_admins"]');
    if (busquedaAdmins) {
        busquedaAdmins.addEventListener('keyup', function(e) {
            if(e.key === 'Enter') {
                document.getElementById('searchFormAdmin').submit();
            }
        });
    }
</script>
