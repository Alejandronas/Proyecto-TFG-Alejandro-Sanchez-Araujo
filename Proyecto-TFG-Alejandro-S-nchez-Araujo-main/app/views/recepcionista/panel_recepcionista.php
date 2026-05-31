<?php





require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/CitaModel.php';

$modelo    = new CitaModel($pdo);
$citasHoy  = $modelo->obtenerCitasHoyTodas();          // Todas las citas de hoy sgenda
$sinMedico = $modelo->obtenerCitasSinMedico();          // Citas pendientes de asignar médico
$medicos   = $modelo->obtenerMedicosConEspecialidad();  // Lista de médicos con su especialidad para el select

$programadas = 0; $canceladas = 0;
foreach ($citasHoy as $c) {
    if ($c['estado'] === 'programada') $programadas++;
    if ($c['estado'] === 'cancelada')  $canceladas++;
}

$colores = ['#0a6e5c','#12907a','#2ec4a5','#c8903a','#d95f5f','#5a9e8f'];
?>

<link rel="stylesheet" href="/assets/css/panel_medico.css">
<link rel="stylesheet" href="/assets/css/citas_medico.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<?php
$pagina_activa_rec = 'dashboard';
require_once __DIR__ . '/../../includes/sidebar_recepcionista.php';
?>

<main class="panel-principal">

    <div class="cabecera">
        <h1>Dashboard</h1>
        <div class="cabecera__fecha">
            <i class="bi bi-calendar3"></i>
            <span><?= date('d/m/Y') ?></span>
        </div>
    </div>

    <?php if (($_GET['ok'] ?? '') === 'asignado'): ?>
        <div style="background:#ddf6f1;color:#0a6e5c;border:1px solid #b2e8de;border-radius:12px;padding:12px 18px;font-size:.875rem;margin-bottom:20px;display:flex;align-items:center;gap:10px;">
            <i class="bi bi-check-circle-fill"></i> Médico asignado correctamente.
        </div>
    <?php endif; ?>

    <!-- Banner -->
    <div class="banner-bienvenida">
        <p class="banner-bienvenida__subtitulo">¡Bienvenido/a de vuelta!</p>
        <h2><?= htmlspecialchars($_SESSION['nombre']) ?> <i class="bi bi-emoji-smile"></i></h2>
        <p>Hay <strong><?= count($sinMedico) ?></strong> cita<?= count($sinMedico) !== 1 ? 's' : '' ?> pendiente<?= count($sinMedico) !== 1 ? 's' : '' ?> de asignación. Revisa la cola y gestiona la agenda del día.</p>
        <a href="/recepcionista/citas_recepcionista.php?sin_medico=1" class="boton-blanco">
            <i class="bi bi-person-check"></i> Ver citas sin médico
        </a>
    </div>

    <!-- Estadísticas -->
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
                    <div class="tarjeta-stat__numero"><?= count($sinMedico) ?></div>
                    <div class="tarjeta-stat__etiqueta">Sin médico</div>
                </div>
                <div class="tarjeta-stat__icono tarjeta-stat__icono--naranja"><i class="bi bi-exclamation-circle"></i></div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="tarjeta-stat">
                <div>
                    <div class="tarjeta-stat__numero"><?= $programadas ?></div>
                    <div class="tarjeta-stat__etiqueta">Programadas hoy</div>
                </div>
                <div class="tarjeta-stat__icono tarjeta-stat__icono--verde"><i class="bi bi-bookmark-check"></i></div>
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

    <!-- Cola sin médico + Agenda hoy -->
    <div class="row g-3">
        <div class="col-12 col-xl-6">
            <div class="titulo-seccion">Pendientes de asignación</div>
            <div class="tarjeta-citas">
                <?php if (empty($sinMedico)): ?>
                    <p class="tarjeta-citas__vacio"><i class="bi bi-check-all"></i> Todo asignado.</p>
                <?php else: ?>
                    <?php foreach (array_slice($sinMedico, 0, 5) as $i => $c): ?>
                    <div class="fila-cita">
                        <div class="fila-cita__avatar" style="background:<?= $colores[$i % count($colores)] ?>">
                            <?= strtoupper(substr($c['paciente_nombre'],0,1).substr($c['paciente_apellido'],0,1)) ?>
                        </div>
                        <div>
                            <div class="fila-cita__nombre"><?= htmlspecialchars($c['paciente_nombre'].' '.$c['paciente_apellido']) ?></div>
                            <div class="fila-cita__tipo"><?= date('d/m/Y', strtotime($c['fecha_cita'])) ?> · <?= substr($c['hora_cita'],0,5) ?></div>
                            <?php if (!empty($c['motivo'])): ?>
                            <div class="fila-cita__tipo" style="font-style:italic;color:var(--gris-suave)">
                                <i class="bi bi-chat-left-text" style="font-size:.7rem"></i> <?= htmlspecialchars($c['motivo']) ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <form method="POST" action="/controllers/RecepcionistaController.php?accion=asignar_medico" style="margin-left:auto;display:flex;flex-direction:column;gap:4px;align-items:stretch;min-width:200px">
                            <input type="hidden" name="id_cita" value="<?= $c['id_cita'] ?>">
                            <?php if (!empty($c['especialidad_nombre'])): ?>
                                <div style="font-size:.76rem;font-weight:600;color:var(--verde);background:rgba(10,110,92,.08);border:1px solid rgba(10,110,92,.2);border-radius:8px;padding:4px 10px;text-align:center">
                                    <i class="bi bi-hospital me-1"></i><?= htmlspecialchars($c['especialidad_nombre']) ?>
                                </div>
                            <?php else: ?>
                                <div style="font-size:.76rem;color:var(--gris-suave);text-align:center;padding:4px 0">
                                    Sin especialidad
                                </div>
                            <?php endif; ?>
                            <div style="display:flex;gap:6px;align-items:center">
                                <select name="id_empleado" id="med-<?= $c['id_cita'] ?>" class="form-select form-select-sm" style="font-size:.78rem;border-radius:8px;border:1px solid var(--borde)" required
                                        data-filter-esp="<?= $c['id_especialidad'] ?? '' ?>">
                                    <option value="">Médico…</option>
                                    <?php foreach ($medicos as $m): ?>
                                        <option value="<?= $m['id_empleado'] ?>" data-esp="<?= $m['id_especialidad'] ?>"
                                            <?php if (!empty($c['id_especialidad']) && (string)$m['id_especialidad'] !== (string)$c['id_especialidad']): ?>hidden<?php endif; ?>>
                                            Dr. <?= htmlspecialchars($m['apellido'].', '.$m['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn-guardar" style="padding:5px 12px;font-size:.78rem;white-space:nowrap">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                    <?php endforeach; ?>
                    <?php if (count($sinMedico) > 5): ?>
                        <p style="text-align:center;font-size:.78rem;color:var(--gris-suave);margin-top:10px">
                            Y <?= count($sinMedico)-5 ?> más — <a href="/recepcionista/citas_recepcionista.php?sin_medico=1">ver todas</a>
                        </p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="titulo-seccion">Agenda de hoy</div>
            <div class="tarjeta-citas">
                <?php if (empty($citasHoy)): ?>
                    <p class="tarjeta-citas__vacio">No hay citas para hoy.</p>
                <?php else: ?>
                    <?php foreach ($citasHoy as $i => $c):
                        $estadoClase = $c['estado'] === 'completada' ? 'confirmada' : ($c['estado'] === 'cancelada' ? 'cancelada' : 'pendiente');
                    ?>
                    <div class="fila-cita">
                        <div class="fila-cita__avatar" style="background:<?= $colores[$i % count($colores)] ?>">
                            <?= strtoupper(substr($c['paciente_nombre'],0,1).substr($c['paciente_apellido'],0,1)) ?>
                        </div>
                        <div>
                            <div class="fila-cita__nombre"><?= htmlspecialchars($c['paciente_nombre'].' '.$c['paciente_apellido']) ?></div>
                            <div class="fila-cita__tipo">
                                <?= $c['medico_nombre'] ? 'Dr. '.htmlspecialchars($c['medico_nombre'].' '.$c['medico_apellido']) : '<em>Sin médico</em>' ?>
                            </div>
                        </div>
                        <span class="etiqueta-estado etiqueta-estado--<?= $estadoClase ?>"><?= ucfirst($c['estado']) ?></span>
                        <div class="fila-cita__hora"><?= substr($c['hora_cita'],0,5) ?></div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
