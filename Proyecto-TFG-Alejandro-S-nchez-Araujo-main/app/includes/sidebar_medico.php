<?php



function nav_activo($pagina, $activa) {
    return $pagina === $activa ? 'barra-lateral__enlace--activo' : '';
}
?>

<aside class="barra-lateral">
    <div class="barra-lateral__logo">Clínica</div>

    <div class="barra-lateral__perfil">
        <div class="barra-lateral__avatar"><i class="bi bi-person-fill"></i></div>
        <div class="barra-lateral__info">
            <h6>Dr. <?= htmlspecialchars($_SESSION['nombre']) ?></h6>
            <span>doctor@clinicageneral.local</span>
        </div>
    </div>

    <div class="barra-lateral__etiqueta">Menú</div>

    <a href="/panel.php" class="barra-lateral__enlace <?= nav_activo('dashboard', $pagina_activa) ?>">
        <i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span>
    </a>
    <a href="/medico/citas.php" class="barra-lateral__enlace <?= nav_activo('citas', $pagina_activa) ?>">
        <i class="bi bi-calendar2-check"></i><span>Mis Citas</span>
    </a>

    <a href="/medico/pacientes.php" class="barra-lateral__enlace <?= nav_activo('pacientes', $pagina_activa) ?>">
        <i class="bi bi-people-fill"></i><span>Mis Pacientes</span>
    </a>
    <a href="/configuracion.php" class="barra-lateral__enlace <?= nav_activo('configuracion', $pagina_activa) ?>">
        <i class="bi bi-gear-fill"></i><span>Configuración</span>
    </a>

    <a href="/controllers/AuthController.php?accion=cerrar" class="barra-lateral__cerrar-sesion">
        <i class="bi bi-box-arrow-left"></i><span>Cerrar sesión</span>
    </a>
</aside>
