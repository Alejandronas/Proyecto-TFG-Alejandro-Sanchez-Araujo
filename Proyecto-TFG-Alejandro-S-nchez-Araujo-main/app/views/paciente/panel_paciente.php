<?php



require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/CitaModel.php';

$modelo         = new CitaModel($pdo);
$proximasCitas  = $modelo->obtenerProximasCitasPaciente($_SESSION['id_paciente']); // Próximas 5 (LEFT JOIN: incluye sin médico)
$todasCitas     = $modelo->obtenerCitasPaciente($_SESSION['id_paciente']);          // Todas las citas para las estadísticas
$medicoAsignado = $modelo->obtenerMedicoAsignado($_SESSION['id_paciente']);          // Médico fijo asignado (MEDICO_PACIENTE)

$programadas = 0; $completadas = 0; $canceladas = 0;
foreach ($todasCitas as $c) {
    if ($c['estado'] === 'programada') $programadas++;
    if ($c['estado'] === 'completada') $completadas++;
    if ($c['estado'] === 'cancelada')  $canceladas++;
}

$colores = ['#0a6e5c','#12907a','#2ec4a5','#c8903a','#d95f5f','#5a9e8f'];
?>

<link rel="stylesheet" href="/assets/css/panel_medico.css">
<link rel="stylesheet" href="/assets/css/panel_paciente.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<?php
$pagina_activa_pac = 'dashboard';
require_once __DIR__ . '/../../includes/sidebar_paciente.php';
?>

<main class="panel-principal">

    <div class="cabecera">
        <h1>Dashboard</h1>
        <div class="cabecera__fecha">
            <i class="bi bi-calendar3"></i>
            <span id="fecha-hoy"></span>
        </div>
    </div>

    <!-- Banner -->
    <div class="banner-paciente">
        <p class="banner-paciente__subtitulo">¡Bienvenido/a de vuelta!</p>
        <h2><?= htmlspecialchars($_SESSION['nombre']) ?> <i class="bi bi-emoji-smile"></i></h2>
        <p>Tienes <?= $programadas ?> cita<?= $programadas !== 1 ? 's' : '' ?> programada<?= $programadas !== 1 ? 's' : '' ?>. Revisa tu agenda y mantente al día con tu salud.</p>
        <a href="/paciente/citas_paciente.php" class="boton-blanco">
            <i class="bi bi-calendar2-week"></i> Ver mis citas
        </a>
    </div>

    <!-- Estadísticas -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="tarjeta-stat">
                <div>
                    <div class="tarjeta-stat__numero"><?= count($todasCitas) ?></div>
                    <div class="tarjeta-stat__etiqueta">Total citas</div>
                </div>
                <div class="tarjeta-stat__icono"><i class="bi bi-calendar2-check"></i></div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="tarjeta-stat">
                <div>
                    <div class="tarjeta-stat__numero"><?= $programadas ?></div>
                    <div class="tarjeta-stat__etiqueta">Programadas</div>
                </div>
                <div class="tarjeta-stat__icono tarjeta-stat__icono--naranja"><i class="bi bi-bookmark-plus"></i></div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="tarjeta-stat">
                <div>
                    <div class="tarjeta-stat__numero"><?= $completadas ?></div>
                    <div class="tarjeta-stat__etiqueta">Completadas</div>
                </div>
                <div class="tarjeta-stat__icono tarjeta-stat__icono--verde"><i class="bi bi-check-circle"></i></div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="tarjeta-stat">
                <div>
                    <div class="tarjeta-stat__numero"><?= $canceladas ?></div>
                    <div class="tarjeta-stat__etiqueta">Canceladas</div>
                </div>
                <div class="tarjeta-stat__icono tarjeta-stat__icono--rojo"><i class="bi bi-x-circle"></i></div>
            </div>
        </div>
    </div>

    <!-- Próximas citas + Médico asignado -->
    <div class="row g-3">
        <div class="col-12 col-xl-7">
            <div class="titulo-seccion">Próximas Citas</div>
            <div class="tarjeta-citas">
                <?php if (empty($proximasCitas)): ?>
                    <p class="tarjeta-citas__vacio">No tienes citas próximas.</p>
                <?php else: ?>
                    <?php foreach ($proximasCitas as $i => $c):
                        $estadoClase = $c['estado'] === 'completada' ? 'confirmada' : ($c['estado'] === 'cancelada' ? 'cancelada' : 'pendiente');
                    ?>
                    <div class="fila-cita">
                        <div class="fila-cita__avatar" style="background:<?= $colores[$i % count($colores)] ?>">
                            <?= $c['medico_nombre'] ? strtoupper(substr($c['medico_nombre'],0,1) . substr($c['medico_apellido'],0,1)) : '?' ?>
                        </div>
                        <div>
                            <div class="fila-cita__nombre">
                                <?= $c['medico_nombre'] ? 'Dr. ' . htmlspecialchars($c['medico_nombre'] . ' ' . $c['medico_apellido']) : '<em style="color:var(--gris-suave);font-size:.85rem">Pendiente de asignación</em>' ?>
                            </div>
                            <div class="fila-cita__tipo"><?= htmlspecialchars($c['especialidad'] ?? '—') ?> · <?= date('d/m/Y', strtotime($c['fecha_cita'])) ?></div>
                        </div>
                        <span class="etiqueta-estado etiqueta-estado--<?= $estadoClase ?>"><?= ucfirst($c['estado']) ?></span>
                        <div class="fila-cita__hora"><?= substr($c['hora_cita'],0,5) ?></div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-12 col-xl-5">
            <div class="titulo-seccion">Mi Médico Asignado</div>
            <?php if ($medicoAsignado): ?>
            <div class="tarjeta-medico">
                <div class="tarjeta-medico__avatar"><i class="bi bi-person-fill"></i></div>
                <div>
                    <div class="tarjeta-medico__nombre">Dr. <?= htmlspecialchars($medicoAsignado['nombre'] . ' ' . $medicoAsignado['apellido']) ?></div>
                    <div class="tarjeta-medico__especialidad"><?= htmlspecialchars($medicoAsignado['especialidad'] ?? 'Sin especialidad') ?></div>
                </div>
            </div>
            <?php else: ?>
            <div class="tarjeta-medico">
                <div class="tarjeta-medico__avatar" style="background:var(--gris-suave)"><i class="bi bi-person-fill"></i></div>
                <div>
                    <div class="tarjeta-medico__nombre">Sin médico asignado</div>
                    <div class="tarjeta-medico__especialidad">Contacta con la clínica para que te asignen uno.</div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

</main>

<script>
    document.getElementById('fecha-hoy').textContent = new Date().toLocaleDateString('es-ES', {
        weekday: 'short', year: 'numeric', month: 'short', day: 'numeric'
    });
</script>
