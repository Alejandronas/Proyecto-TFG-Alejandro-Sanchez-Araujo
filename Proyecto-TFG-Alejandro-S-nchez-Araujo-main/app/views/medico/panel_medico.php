<?php

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/CitaModel.php';

// Carga citas de hoy, próximas 7dias y total de pacientes del medico en sesin
$modelo         = new CitaModel($pdo);
$citasHoy       = $modelo->obtenerCitasMedico($_SESSION['id_empleado']);
$proximasCitas  = $modelo->obtenerProximasCitas($_SESSION['id_empleado']);
$totalPacientes = $modelo->contarPacientesMedico($_SESSION['id_empleado']);

$programadas = 0;
$completadas = 0;
$canceladas  = 0;
foreach ($citasHoy as $c) {
    if ($c['estado'] === 'programada') $programadas++;
    if ($c['estado'] === 'completada') $completadas++;
    if ($c['estado'] === 'cancelada')  $canceladas++;
}

$colores = ['#0a6e5c','#12907a','#2ec4a5','#c8903a','#d95f5f','#5a9e8f'];
?>

<link rel="stylesheet" href="/assets/css/panel_medico.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<?php
$pagina_activa = 'dashboard';
require_once __DIR__ . '/../../includes/sidebar_medico.php';
?>

<main class="panel-principal">

    <!-- Cabecera -->
    <div class="cabecera">
        <h1>Dashboard</h1>
        <div class="cabecera__fecha">
            <i class="bi bi-calendar3"></i>
            <span id="fecha-hoy"></span>
        </div>
    </div>

    <!-- Banner bienvenida -->
    <div class="banner-bienvenida">
        <p class="banner-bienvenida__subtitulo">¡Bienvenido de vuelta!</p>
        <h2>Dr. <?= htmlspecialchars($_SESSION['nombre']) ?> <i class="bi bi-emoji-smile"></i></h2>
        <p>Tienes <?= count($citasHoy) ?> citas programadas hoy. Revisa tu agenda y mantente al día con tus pacientes.</p>
        <a href="#calendario" class="boton-blanco">
            <i class="bi bi-calendar2-week"></i> Ver mis citas
        </a>
    </div>

    <!-- Tarjetas de estadísticas -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="tarjeta-stat">
                <div>
                    <div class="tarjeta-stat__numero"><?= count($citasHoy) ?></div>
                    <div class="tarjeta-stat__etiqueta">Citas hoy</div>
                </div>
                <div class="tarjeta-stat__icono"><i class="bi bi-calendar2-check"></i></div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="tarjeta-stat">
                <div>
                    <div class="tarjeta-stat__numero"><?= $totalPacientes ?></div>
                    <div class="tarjeta-stat__etiqueta">Mis pacientes</div>
                </div>
                <div class="tarjeta-stat__icono tarjeta-stat__icono--verde"><i class="bi bi-person-wheelchair"></i></div>
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
                    <div class="tarjeta-stat__numero"><?= $canceladas ?></div>
                    <div class="tarjeta-stat__etiqueta">Canceladas hoy</div>
                </div>
                <div class="tarjeta-stat__icono tarjeta-stat__icono--rojo"><i class="bi bi-x-circle"></i></div>
            </div>
        </div>
    </div>

    <!-- Calendario + Próximas citas -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-7">
            <div class="titulo-seccion">Calendario de Citas</div>
            <div class="tarjeta-calendario">
                <div id="calendario"></div>
            </div>
        </div>
        <div class="col-12 col-xl-5">
            <div class="titulo-seccion">Próximas Citas</div>
            <div class="tarjeta-citas">
                <?php if (empty($proximasCitas)): ?>
                    <p class="tarjeta-citas__vacio">No hay citas próximas.</p>
                <?php else: ?>
                    <?php foreach ($proximasCitas as $i => $cita):
                        $iniciales   = strtoupper(substr($cita['nombre'],0,1) . substr($cita['apellido'],0,1));
                        $color       = $colores[$i % count($colores)];
                        $estadoClase = $cita['estado'] === 'completada' ? 'confirmada' : ($cita['estado'] === 'cancelada' ? 'cancelada' : 'pendiente');
                    ?>
                    <div class="fila-cita">
                        <div class="fila-cita__avatar" style="background:<?= $color ?>"><?= $iniciales ?></div>
                        <div>
                            <div class="fila-cita__nombre"><?= htmlspecialchars($cita['nombre'] . ' ' . $cita['apellido']) ?></div>
                            <div class="fila-cita__tipo"><?= date('d/m/Y', strtotime($cita['fecha_cita'])) ?></div>
                        </div>
                        <span class="etiqueta-estado etiqueta-estado--<?= $estadoClase ?>"><?= ucfirst($cita['estado']) ?></span>
                        <div class="fila-cita__hora"><?= substr($cita['hora_cita'], 0, 5) ?></div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Resumen rápido -->
    <div class="row g-3">
        <div class="col-6 col-md-3">
            <div class="tarjeta-resumen">
                <div class="tarjeta-resumen__numero tarjeta-resumen__numero--principal"><?= count($proximasCitas) ?></div>
                <div class="tarjeta-resumen__etiqueta">Citas esta semana</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="tarjeta-resumen">
                <div class="tarjeta-resumen__numero tarjeta-resumen__numero--acento"><?= $completadas ?></div>
                <div class="tarjeta-resumen__etiqueta">Completadas hoy</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="tarjeta-resumen">
                <div class="tarjeta-resumen__numero tarjeta-resumen__numero--naranja"><?= $programadas ?></div>
                <div class="tarjeta-resumen__etiqueta">Pendientes hoy</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="tarjeta-resumen">
                <div class="tarjeta-resumen__numero tarjeta-resumen__numero--rojo"><?= $canceladas ?></div>
                <div class="tarjeta-resumen__etiqueta">Canceladas hoy</div>
            </div>
        </div>
    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
    document.getElementById('fecha-hoy').textContent = new Date().toLocaleDateString('es-ES', {
        weekday: 'short', year: 'numeric', month: 'short', day: 'numeric'
    });

    document.addEventListener('DOMContentLoaded', function () {
        const cal = new FullCalendar.Calendar(document.getElementById('calendario'), {
            initialView: 'dayGridMonth',
            locale: 'es',
            height: 420,
            headerToolbar: {
                left:   'prev next today',
                center: 'title',
                right:  'dayGridMonth timeGridWeek timeGridDay'
            },
            views: {
                dayGridMonth: { buttonText: 'Mes' },
                timeGridWeek: { buttonText: 'Sem' },
                timeGridDay:  { buttonText: 'Día' }
            },
            buttonText: { today: 'Hoy', month: 'Mes', week: 'Sem', day: 'Día' },
            eventDisplay: 'block',
            // Endpoint JSON que devuelve las citas del médico para renderizarlas en el calendario
    events: '/controllers/CalendarioController.php',
            eventContent: function(arg) {
                const hora  = arg.event.start
                    ? arg.event.start.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })
                    : '';
                const nombre = arg.event.title || '';
                return {
                    html: '<span style="font-size:.75rem;font-weight:600;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;padding:1px 4px">'
                          + hora + ' ' + nombre
                          + '</span>'
                };
            },
            eventClick: function(info) {
                alert('Paciente: ' + info.event.title + '\nEstado: ' + info.event.extendedProps.estado);
            }
        });
        cal.render();
    });
</script>
