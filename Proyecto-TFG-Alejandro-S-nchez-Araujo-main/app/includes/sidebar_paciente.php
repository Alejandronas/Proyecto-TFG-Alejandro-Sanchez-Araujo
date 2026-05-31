<?php
function nav_activo_pac($pagina, $activa) {
    return $pagina === $activa ? 'barra-lateral__enlace--activo' : '';
}
?>

<aside class="barra-lateral">
    <div class="barra-lateral__logo">Clínica</div>

    <div class="barra-lateral__perfil">
        <div class="barra-lateral__avatar"><i class="bi bi-person-fill"></i></div>
        <div class="barra-lateral__info">
            <h6><?= htmlspecialchars($_SESSION['nombre']) ?></h6>
            <span>Paciente</span>
        </div>
    </div>

    <div class="barra-lateral__etiqueta">Menú</div>

    <a href="/panel.php" class="barra-lateral__enlace <?= nav_activo_pac('dashboard', $pagina_activa_pac) ?>">
        <i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span>
    </a>
    <a href="/paciente/citas_paciente.php" class="barra-lateral__enlace <?= nav_activo_pac('citas', $pagina_activa_pac) ?>">
        <i class="bi bi-calendar2-check"></i><span>Mis Citas</span>
    </a>
    <a href="/paciente/historial_paciente.php" class="barra-lateral__enlace <?= nav_activo_pac('historial', $pagina_activa_pac) ?>">
        <i class="bi bi-clipboard2-pulse"></i><span>Mi Historial</span>
    </a>
    <a href="/paciente/pedir_cita.php" class="barra-lateral__enlace <?= nav_activo_pac('pedir_cita', $pagina_activa_pac) ?>">
        <i class="bi bi-calendar-plus"></i><span>Pedir Cita</span>
    </a>
    <a href="/paciente/perfil_paciente.php" class="barra-lateral__enlace <?= nav_activo_pac('perfil', $pagina_activa_pac) ?>">
        <i class="bi bi-person-gear"></i><span>Mi Perfil</span>
    </a>

    <a href="/controllers/AuthController.php?accion=cerrar" class="barra-lateral__cerrar-sesion">
        <i class="bi bi-box-arrow-left"></i><span>Cerrar sesión</span>
    </a>
</aside>
