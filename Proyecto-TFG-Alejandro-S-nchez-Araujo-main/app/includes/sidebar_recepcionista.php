<?php
function nav_activo_rec($pagina, $activa) {
    return $pagina === $activa ? 'barra-lateral__enlace--activo' : '';
}
?>

<aside class="barra-lateral">
    <div class="barra-lateral__logo">Clínica</div>

    <div class="barra-lateral__perfil">
        <div class="barra-lateral__avatar"><i class="bi bi-person-fill"></i></div>
        <div class="barra-lateral__info">
            <h6><?= htmlspecialchars($_SESSION['nombre']) ?></h6>
            <span>Recepcionista</span>
        </div>
    </div>

    <div class="barra-lateral__etiqueta">Menú</div>

    <a href="/panel.php" class="barra-lateral__enlace <?= nav_activo_rec('dashboard', $pagina_activa_rec) ?>">
        <i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span>
    </a>
    <a href="/recepcionista/citas_recepcionista.php" class="barra-lateral__enlace <?= nav_activo_rec('citas', $pagina_activa_rec) ?>">
        <i class="bi bi-calendar2-check"></i><span>Citas</span>
    </a>
    <a href="/recepcionista/agenda_recepcionista.php" class="barra-lateral__enlace <?= nav_activo_rec('agenda', $pagina_activa_rec) ?>">
        <i class="bi bi-clock-history"></i><span>Agenda de Hoy</span>
    </a>
    <a href="/recepcionista/pacientes_recepcionista.php" class="barra-lateral__enlace <?= nav_activo_rec('pacientes', $pagina_activa_rec) ?>">
        <i class="bi bi-people-fill"></i><span>Pacientes</span>
    </a>

    <a href="/controllers/AuthController.php?accion=cerrar" class="barra-lateral__cerrar-sesion">
        <i class="bi bi-box-arrow-left"></i><span>Cerrar sesión</span>
    </a>
</aside>
