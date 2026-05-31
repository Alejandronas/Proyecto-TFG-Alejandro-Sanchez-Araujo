<?php
function nav_activo_admin($pagina, $activa) {
    return $pagina === $activa ? 'barra-lateral__enlace--activo' : '';
}
?>

<aside class="barra-lateral">
    <div class="barra-lateral__logo">Clínica</div>

    <div class="barra-lateral__perfil">
        <div class="barra-lateral__avatar"><i class="bi bi-shield-fill"></i></div>
        <div class="barra-lateral__info">
            <h6><?= htmlspecialchars($_SESSION['nombre']) ?></h6>
            <span>Administrador</span>
        </div>
    </div>

    <div class="barra-lateral__etiqueta">Menú</div>

    <a href="/panel.php" class="barra-lateral__enlace <?= nav_activo_admin('dashboard', $pagina_activa_admin) ?>">
        <i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span>
    </a>
    <a href="/admin/empleados_admin.php" class="barra-lateral__enlace <?= nav_activo_admin('empleados', $pagina_activa_admin) ?>">
        <i class="bi bi-person-badge-fill"></i><span>Empleados</span>
    </a>
    <a href="/admin/pacientes_admin.php" class="barra-lateral__enlace <?= nav_activo_admin('pacientes', $pagina_activa_admin) ?>">
        <i class="bi bi-people-fill"></i><span>Pacientes</span>
    </a>
    <a href="/admin/citas_admin.php" class="barra-lateral__enlace <?= nav_activo_admin('citas', $pagina_activa_admin) ?>">
        <i class="bi bi-calendar2-check"></i><span>Citas por médico</span>
    </a>
    <a href="/admin/logs_admin.php" class="barra-lateral__enlace <?= nav_activo_admin('logs', $pagina_activa_admin) ?>">
        <i class="bi bi-journal-text"></i><span>Log de accesos</span>
    </a>

    <a href="/controllers/AuthController.php?accion=cerrar" class="barra-lateral__cerrar-sesion">
        <i class="bi bi-box-arrow-left"></i><span>Cerrar sesión</span>
    </a>
</aside>
