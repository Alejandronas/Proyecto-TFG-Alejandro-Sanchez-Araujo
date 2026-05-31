<?php




require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/CitaModel.php';

$modelo   = new CitaModel($pdo);
$citasHoy = $modelo->obtenerCitasHoyTodas();

// Agrupa las citas por nombre del médico y ordena cada columna por hora ascendente
$porMedico = [];
foreach ($citasHoy as $c) {
    $key = $c['medico_nombre'] ? 'Dr. '.$c['medico_nombre'].' '.$c['medico_apellido'] : 'Sin médico asignado';
    $porMedico[$key][] = $c;
}
foreach ($porMedico as &$grupo) {
    usort($grupo, fn($a, $b) => strcmp($a['hora_cita'], $b['hora_cita']));
}
unset($grupo);

$colores = ['#0a6e5c','#12907a','#2ec4a5','#c8903a','#d95f5f','#5a9e8f'];
?>

<link rel="stylesheet" href="/assets/css/panel_medico.css">
<link rel="stylesheet" href="/assets/css/citas_medico.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<?php
$pagina_activa_rec = 'agenda';
require_once __DIR__ . '/../../includes/sidebar_recepcionista.php';
?>

<main class="panel-principal">

    <div class="cabecera">
        <h1>Agenda de Hoy</h1>
        <div class="cabecera__fecha">
            <i class="bi bi-calendar3"></i>
            <?php
            $dias = ['Sunday'=>'Domingo','Monday'=>'Lunes','Tuesday'=>'Martes',
                     'Wednesday'=>'Miércoles','Thursday'=>'Jueves','Friday'=>'Viernes','Saturday'=>'Sábado'];
            ?>
            <span><?= $dias[date('l')] ?> <?= date('d/m/Y') ?></span>
        </div>
    </div>

    <?php if (empty($citasHoy)): ?>
        <div class="tabla-card">
            <div class="sin-resultados">
                <i class="bi bi-calendar-x"></i>
                No hay citas programadas para hoy.
            </div>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php $ci = 0; foreach ($porMedico as $nombreMedico => $citas): ?>
            <div class="col-12 col-xl-6">
                <div class="titulo-seccion"><?= htmlspecialchars($nombreMedico) ?></div>
                <div class="tarjeta-citas">
                    <?php foreach ($citas as $c):
                        $iniciales   = strtoupper(substr($c['paciente_nombre'],0,1).substr($c['paciente_apellido'],0,1));
                        $color       = $colores[$ci % count($colores)];
                        $estadoClase = $c['estado'] === 'completada' ? 'confirmada' : ($c['estado'] === 'cancelada' ? 'cancelada' : 'pendiente');
                        $ci++;
                    ?>
                    <div class="fila-cita">
                        <div class="fila-cita__avatar" style="background:<?= $color ?>"><?= $iniciales ?></div>
                        <div>
                            <div class="fila-cita__nombre"><?= htmlspecialchars($c['paciente_nombre'].' '.$c['paciente_apellido']) ?></div>
                            <?php if (!empty($c['motivo'])): ?>
                            <div class="fila-cita__tipo"><?= htmlspecialchars($c['motivo']) ?></div>
                            <?php endif; ?>
                        </div>
                        <span class="etiqueta-estado etiqueta-estado--<?= $estadoClase ?>"><?= ucfirst($c['estado']) ?></span>
                        <div class="fila-cita__hora"><?= substr($c['hora_cita'],0,5) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</main>
