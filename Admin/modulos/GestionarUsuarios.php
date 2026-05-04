<?php
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
    /* ===== ESTILOS GESTIONAR USUARIOS - SOLO PARA ESTA PÁGINA ===== */
    
    .gestionar-usuarios-container {
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
        --info: #3b82f6;
        padding-top: 2rem;
    }

    /* Page Header */
    .gestionar-usuarios-container .page-header {
        background: var(--secondary);
        color: var(--primary);
        padding: 2.5rem 2rem;
        border-radius: 20px;
        margin-bottom: 2rem;
        margin-top: -8px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        border: 1px solid var(--border);
        position: relative;
        overflow: hidden;
    }

    .gestionar-usuarios-container .page-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary), var(--primary-dark), var(--primary));
    }

    .gestionar-usuarios-container .page-header h1 {
        margin: 0;
        font-size: 2.5rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 1rem;
        color: var(--primary);
    }

    .gestionar-usuarios-container .page-header h1 i {
        color: var(--primary);
        font-size: 2.5rem;
    }

    .gestionar-usuarios-container .page-header p {
        margin-top: 0.5rem;
        margin-bottom: 0;
        color: #e0e0e0;
    }

    /* Tabs Navigation */
    .gestionar-usuarios-container .tabs-navs {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
        border-bottom: 2px solid var(--border);
        padding-bottom: 1rem;
    }

    .gestionar-usuarios-container .tab-button {
        padding: 0.75rem 1.5rem;
        border: none;
        background: transparent;
        color: var(--dark);
        cursor: pointer;
        font-weight: 600;
        position: relative;
        font-size: 1rem;
        border-radius: 12px;
        transition: all 0.3s ease;
    }

    .gestionar-usuarios-container .tab-button i {
        margin-right: 8px;
        color: var(--primary);
    }

    .gestionar-usuarios-container .tab-button.active {
        color: var(--primary);
        background: rgba(255, 215, 0, 0.1);
    }

    .gestionar-usuarios-container .tab-button.active::after {
        content: '';
        position: absolute;
        bottom: -1rem;
        left: 0;
        right: 0;
        height: 2px;
        background: var(--primary);
    }

    .gestionar-usuarios-container .tab-button:hover:not(.active) {
        background: rgba(0, 0, 0, 0.05);
        color: var(--primary);
    }

    /* Tab Content */
    .gestionar-usuarios-container .tab-content {
        display: none;
    }

    .gestionar-usuarios-container .tab-content.active {
        display: block;
        animation: fadeInTab 0.4s ease;
    }

    @keyframes fadeInTab {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Stats Cards */
    .gestionar-usuarios-container .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .gestionar-usuarios-container .stat-card {
        background: var(--light);
        padding: 1.5rem;
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--border);
        transition: all 0.3s ease;
    }

    .gestionar-usuarios-container .stat-card:hover {
        border-color: var(--primary);
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(255, 215, 0, 0.1);
    }

    .gestionar-usuarios-container .stat-card h6 {
        color: #666666;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        margin: 0 0 1rem 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .gestionar-usuarios-container .stat-card h6 i {
        color: var(--primary);
    }

    .gestionar-usuarios-container .stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--secondary);
    }

    /* Filter Section */
    .gestionar-usuarios-container .filter-section {
        padding: 1.5rem;
        background: var(--light);
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
        border: 1px solid var(--border);
        transition: all 0.3s ease;
    }

    .gestionar-usuarios-container .filter-section:hover {
        border-color: var(--primary);
    }

    .gestionar-usuarios-container .filter-section h5 {
        color: var(--secondary);
        font-weight: 600;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .gestionar-usuarios-container .filter-section h5 i {
        color: var(--primary);
    }

    .gestionar-usuarios-container .search-row {
        display: grid;
        grid-template-columns: 1fr auto auto;
        gap: 1rem;
        align-items: flex-end;
    }

    .gestionar-usuarios-container .search-input {
        position: relative;
    }

    .gestionar-usuarios-container .search-input input {
        border: 2px solid var(--border);
        border-radius: 12px;
        padding: 0.75rem 1rem 0.75rem 2.75rem;
        width: 100%;
        background: var(--light);
        font-weight: 500;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .gestionar-usuarios-container .search-input input::placeholder {
        color: #999999;
    }

    .gestionar-usuarios-container .search-input input:focus {
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.2) !important;
        outline: none;
    }

    .gestionar-usuarios-container .search-input input:hover {
        border-color: var(--primary);
    }

    .gestionar-usuarios-container .search-input i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--primary);
    }

    .gestionar-usuarios-container .btn-search {
        background: var(--secondary);
        color: var(--light);
        border: 2px solid var(--primary);
        border-radius: 12px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
    }

    .gestionar-usuarios-container .btn-search:hover {
        background: var(--primary);
        color: var(--secondary);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 215, 0, 0.3);
    }

    .gestionar-usuarios-container .btn-clear {
        background: var(--light);
        color: var(--secondary);
        border: 2px solid var(--border);
        border-radius: 12px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .gestionar-usuarios-container .btn-clear:hover {
        border-color: var(--primary);
        color: var(--primary);
        transform: translateY(-2px);
        text-decoration: none;
    }

    /* Table Container */
    .gestionar-usuarios-container .table-container {
        overflow-x: auto;
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--border);
        background: var(--light);
    }

    .gestionar-usuarios-container .table {
        margin-bottom: 0;
        background: var(--light);
    }

    .gestionar-usuarios-container .table thead {
        background: #f8f9fa;
        border-bottom: 2px solid var(--primary);
    }

    .gestionar-usuarios-container .table thead th {
        color: var(--secondary);
        font-weight: 700;
        padding: 1.25rem;
        border: none;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }

    .gestionar-usuarios-container .table thead th i {
        color: var(--primary);
        margin-right: 5px;
    }

    .gestionar-usuarios-container .table tbody tr {
        border-bottom: 1px solid #f0f0f0;
        transition: all 0.3s ease;
    }

    .gestionar-usuarios-container .table tbody tr:hover {
        background: rgba(255, 215, 0, 0.05);
    }

    .gestionar-usuarios-container .table tbody td {
        padding: 1rem 1.25rem;
        vertical-align: middle;
        color: var(--dark);
    }

    .gestionar-usuarios-container .table strong {
        color: var(--secondary);
        font-weight: 700;
    }

    /* Badges */
    .gestionar-usuarios-container .badge-estatus {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .gestionar-usuarios-container .badge-activo {
        background: rgba(255, 215, 0, 0.15);
        color: var(--primary-dark);
        border: 1px solid rgba(255, 215, 0, 0.3);
    }

    .gestionar-usuarios-container .badge-pendiente {
        background: rgba(245, 158, 11, 0.15);
        color: var(--warning);
        border: 1px solid rgba(245, 158, 11, 0.3);
    }

    .gestionar-usuarios-container .badge-deshabilitado {
        background: rgba(239, 68, 68, 0.15);
        color: var(--danger);
        border: 1px solid rgba(239, 68, 68, 0.3);
    }

    /* Action Buttons */
    .gestionar-usuarios-container .btn-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 10px;
        border: none;
        cursor: pointer;
        font-size: 1rem;
        transition: all 0.3s ease;
        margin: 0 0.25rem;
        background: transparent;
    }

    .gestionar-usuarios-container .btn-edit {
        background: var(--secondary);
        color: var(--primary);
        border: 1px solid var(--primary);
    }

    .gestionar-usuarios-container .btn-edit i {
        color: var(--primary);
    }

    .gestionar-usuarios-container .btn-edit:hover {
        background: var(--primary);
        transform: translateY(-2px);
    }

    .gestionar-usuarios-container .btn-edit:hover i {
        color: var(--secondary);
    }

    .gestionar-usuarios-container .btn-delete {
        background: var(--light);
        color: var(--danger);
        border: 1px solid var(--danger);
    }

    .gestionar-usuarios-container .btn-delete i {
        color: var(--danger);
    }

    .gestionar-usuarios-container .btn-delete:hover {
        background: var(--danger);
        transform: translateY(-2px);
    }

    .gestionar-usuarios-container .btn-delete:hover i {
        color: var(--light);
    }

    .gestionar-usuarios-container .btn-enable {
        background: var(--light);
        color: var(--success);
        border: 1px solid var(--success);
    }

    .gestionar-usuarios-container .btn-enable i {
        color: var(--success);
    }

    .gestionar-usuarios-container .btn-enable:hover {
        background: var(--success);
        transform: translateY(-2px);
    }

    .gestionar-usuarios-container .btn-enable:hover i {
        color: var(--light);
    }

    /* Empty State */
    .gestionar-usuarios-container .empty-state {
        text-align: center;
        padding: 3rem 2rem;
        color: #999999;
    }

    .gestionar-usuarios-container .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        color: var(--primary);
        opacity: 0.5;
    }

    .gestionar-usuarios-container .empty-state p {
        font-size: 1rem;
        margin: 0;
    }

    /* Modal Styles */
    .gestionar-usuarios-container .modal-content {
        border: none;
        border-radius: 20px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
        overflow: hidden;
    }

    .gestionar-usuarios-container .modal-header {
        background: var(--secondary);
        color: var(--primary);
        border: none;
        border-radius: 20px 20px 0 0;
        padding: 1.25rem 1.5rem;
    }

    .gestionar-usuarios-container .modal-header .modal-title {
        font-weight: 700;
        font-size: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .gestionar-usuarios-container .modal-header .modal-title i {
        color: var(--primary);
    }

    .gestionar-usuarios-container .modal-header .btn-close {
        filter: brightness(0) invert(1);
        background-color: var(--primary);
        opacity: 0.8;
        border-radius: 50%;
        padding: 0.5rem;
    }

    .gestionar-usuarios-container .modal-body {
        padding: 1.5rem;
    }

    .gestionar-usuarios-container .modal-footer {
        border-top: 1px solid var(--border);
        padding: 1rem 1.5rem;
    }

    /* Form Controls */
    .gestionar-usuarios-container .form-control {
        border: 2px solid var(--border);
        border-radius: 12px;
        padding: 0.75rem 1rem;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        background: var(--light);
    }

    .gestionar-usuarios-container .form-control:focus {
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.2) !important;
        outline: none;
    }

    .gestionar-usuarios-container .form-control:hover {
        border-color: var(--primary);
    }

    .gestionar-usuarios-container .form-label {
        font-weight: 600;
        color: var(--secondary);
        margin-bottom: 0.5rem;
    }

    /* Buttons */
    .gestionar-usuarios-container .btn-primary {
        background: var(--secondary);
        color: var(--light);
        border: 2px solid var(--primary);
        border-radius: 12px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .gestionar-usuarios-container .btn-primary:hover {
        background: var(--primary);
        color: var(--secondary);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 215, 0, 0.3);
    }

    .gestionar-usuarios-container .btn-secondary {
        background: var(--light);
        color: var(--secondary);
        border: 2px solid var(--border);
        border-radius: 12px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .gestionar-usuarios-container .btn-secondary:hover {
        border-color: var(--primary);
        color: var(--primary);
        transform: translateY(-2px);
    }

    .gestionar-usuarios-container .btn-danger {
        background: var(--light);
        color: var(--danger);
        border: 2px solid var(--danger);
        border-radius: 12px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .gestionar-usuarios-container .btn-danger:hover {
        background: var(--danger);
        color: var(--light);
        transform: translateY(-2px);
    }

    .gestionar-usuarios-container .btn-success {
        background: var(--light);
        color: var(--success);
        border: 2px solid var(--success);
        border-radius: 12px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .gestionar-usuarios-container .btn-success:hover {
        background: var(--success);
        color: var(--light);
        transform: translateY(-2px);
    }

    /* Alerts */
    .gestionar-usuarios-container .alert-warning {
        background: rgba(255, 215, 0, 0.1);
        border-left: 4px solid var(--primary);
        color: var(--secondary);
        border-radius: 12px;
        padding: 1rem;
    }

    .gestionar-usuarios-container .alert-success {
        background: rgba(16, 185, 129, 0.1);
        border-left: 4px solid var(--success);
        color: var(--secondary);
        border-radius: 12px;
        padding: 1rem;
    }

    .gestionar-usuarios-container .text-muted {
        color: #999999 !important;
        font-size: 0.85rem;
        margin-top: 0.5rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .gestionar-usuarios-container .page-header h1 {
            font-size: 1.75rem;
        }

        .gestionar-usuarios-container .page-header i {
            font-size: 1.75rem;
        }

        .gestionar-usuarios-container .search-row {
            grid-template-columns: 1fr;
        }

        .gestionar-usuarios-container .stats-row {
            grid-template-columns: 1fr;
        }

        .gestionar-usuarios-container .table {
            font-size: 0.85rem;
        }

        .gestionar-usuarios-container .table th, 
        .gestionar-usuarios-container .table td {
            padding: 0.75rem 0.5rem;
        }

        .gestionar-usuarios-container .btn-icon {
            width: 32px;
            height: 32px;
            font-size: 0.9rem;
        }

        .gestionar-usuarios-container .tabs-navs {
            flex-direction: column;
            gap: 0.5rem;
        }

        .gestionar-usuarios-container .tab-button.active::after {
            display: none;
        }

        .gestionar-usuarios-container .tab-button.active {
            background: rgba(255, 215, 0, 0.2);
        }
    }
</style>


<div class="gestionar-usuarios-container">
    <div class="container-fluid" style="max-width: 1400px; margin: 0 auto; padding: 0 1rem;">
        <div class="page-header">
            <h1>
                <i class="bi bi-people-fill"></i>
                Gestionar Accesos
            </h1>
            <p>Administra y controla usuarios clientes y administradores</p>
        </div>

        <!-- Navegación de pestañas -->
        <div class="tabs-navs">
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
                <div class="stat-card">
                    <h6><i class="bi bi-clock-history"></i> Pendientes</h6>
                    <div class="stat-value"><?= array_reduce($usuarios, fn($c, $r) => $c + ($r["activacion"] == 0 ? 1 : 0), 0) ?></div>
                </div>
                <div class="stat-card">
                    <h6><i class="bi bi-person-x"></i> Deshabilitados</h6>
                    <div class="stat-value"><?= array_reduce($usuarios, fn($c, $r) => $c + ($r["activacion"] == 2 ? 1 : 0), 0) ?></div>
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
                <div class="stat-card">
                    <h6><i class="bi bi-shield-x"></i> Inactivos</h6>
                    <div class="stat-value"><?= array_reduce($administradores, fn($c, $r) => $c + ($r["activo"] == 0 ? 1 : 0), 0) ?></div>
                </div>
                <div class="stat-card">
                    <h6><i class="bi bi-people-fill"></i> Total</h6>
                    <div class="stat-value"><?= count($administradores) ?></div>
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

    <!-- Modales (igual que antes) -->
    <!-- Modal Cambiar Contraseña - Usuarios -->
    <div class="modal fade" id="cambiarPasswordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-key"></i> Cambiar Contraseña Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
                        <button type="button" class="btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn-primary">
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
                        
                        <div class="alert-warning">
                            <i class="bi bi-info-circle"></i>
                            ¿Está seguro que desea <strong>deshabilitar</strong> al usuario <strong id="deshabilitarUsuario"></strong>?
                        </div>
                        <p class="text-muted">El usuario no podrá acceder a su cuenta hasta que sea habilitado nuevamente.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn-danger">
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
                        
                        <div class="alert-success">
                            <i class="bi bi-info-circle"></i>
                            ¿Está seguro que desea <strong>habilitar</strong> al usuario <strong id="habilitarUsuario"></strong>?
                        </div>
                        <p class="text-muted">El usuario podrá acceder nuevamente a su cuenta.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn-success">
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
                        <button type="button" class="btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn-primary">
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
                        
                        <div class="alert-warning">
                            <i class="bi bi-info-circle"></i>
                            ¿Está seguro que desea <strong>deshabilitar</strong> al administrador <strong id="deshabilitarAdminUsuario"></strong>?
                        </div>
                        <p class="text-muted">El administrador no podrá acceder al panel administrativo hasta que sea habilitado nuevamente.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn-danger">
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
                        
                        <div class="alert-success">
                            <i class="bi bi-info-circle"></i>
                            ¿Está seguro que desea <strong>habilitar</strong> al administrador <strong id="habilitarAdminUsuario"></strong>?
                        </div>
                        <p class="text-muted">El administrador podrá acceder nuevamente al panel administrativo.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn-success">
                            <i class="bi bi-check-circle"></i> Sí, Habilitar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Función para cambiar tabs
    function cambiarTab(tabId, button) {
        const tabContents = document.querySelectorAll('.gestionar-usuarios-container .tab-content');
        tabContents.forEach(tab => tab.classList.remove('active'));

        const buttons = document.querySelectorAll('.gestionar-usuarios-container .tab-button');
        buttons.forEach(btn => btn.classList.remove('active'));

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
    const busquedaUsuarios = document.querySelector('.gestionar-usuarios-container input[name="busqueda_usuarios"]');
    if (busquedaUsuarios) {
        busquedaUsuarios.addEventListener('keyup', function(e) {
            if(e.key === 'Enter') {
                document.getElementById('searchFormUsuarios').submit();
            }
        });
    }

    // Búsqueda al presionar Enter - Administradores
    const busquedaAdmins = document.querySelector('.gestionar-usuarios-container input[name="busqueda_admins"]');
    if (busquedaAdmins) {
        busquedaAdmins.addEventListener('keyup', function(e) {
            if(e.key === 'Enter') {
                document.getElementById('searchFormAdmin').submit();
            }
        });
    }
</script>