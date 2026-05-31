<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/PacienteModel.php';

$modelo    = new PacienteModel($pdo);
$pacientes = $modelo->obtenerPacientesMedico($_SESSION['id_empleado']);
$todos     = $modelo->obtenerTodos();

$colores = ['#0a6e5c','#12907a','#2ec4a5','#c8903a','#d95f5f','#5a9e8f'];
?>

<link rel="stylesheet" href="/assets/css/panel_medico.css">
<link rel="stylesheet" href="/assets/css/pacientes_medico.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<?php
$pagina_activa = 'pacientes';
require_once __DIR__ . '/../../includes/sidebar_medico.php';
?>

<main class="panel-principal">

    <div class="cabecera">
        <h1>Mis Pacientes</h1>
        <div class="cabecera__fecha">
            <i class="bi bi-calendar3"></i>
            <span><?= date('d/m/Y') ?></span>
        </div>
    </div>

    <!-- Cabecera de sección con botón añadir -->
    <div class="tabla-card">
        <div class="pac-header">
            <h5>Total: <?= count($pacientes) ?> pacientes</h5>
            <button type="button" class="btn-nueva-cita" data-bs-toggle="modal" data-bs-target="#modalAnadir">
                <i class="bi bi-person-plus"></i> Añadir Paciente
            </button>
        </div>

        <?php if (empty($pacientes)): ?>
            <div class="sin-resultados">
                <i class="bi bi-people"></i>
                Aún no tienes pacientes asignados. Pulsa "Añadir Paciente" para empezar.
            </div>
        <?php else: ?>
            <table class="tabla-citas">
                <thead>
                    <tr>
                        <th>Paciente</th>
                        <th>Teléfono</th>
                        <th>Alta</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pacientes as $i => $p):
                        $iniciales = strtoupper(substr($p['nombre'],0,1) . substr($p['apellido'],0,1));
                        $color     = $colores[$i % count($colores)];
                    ?>
                    <tr>
                        <td>
                            <div class="paciente-cell">
                                <div class="mini-avatar" style="background:<?= $color ?>"><?= $iniciales ?></div>
                                <?= htmlspecialchars($p['nombre'] . ' ' . $p['apellido']) ?>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($p['telefono'] ?? '—') ?></td>
                        <td><?= $p['fecha_alta'] ? date('d/m/Y', strtotime($p['fecha_alta'])) : '—' ?></td>
                        <td>
                            <div class="acciones-cell">
                                <!-- Ver historial -->
                                <button class="btn-accion btn-historial"
                                    onclick="verHistorial(<?= $p['id_paciente'] ?>, '<?= htmlspecialchars($p['nombre'] . ' ' . $p['apellido']) ?>')">
                                    <i class="bi bi-clipboard2-pulse"></i>
                                </button>
                                <!-- Eliminar -->
                                <button class="btn-accion btn-eliminar"
                                    onclick="confirmarDesasignar(<?= $p['id_paciente'] ?>)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</main>


<!-- ═══════════════════════════════════════════════════════════════
     MODAL — AÑADIR PACIENTE (con pestañas)
═══════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalAnadir" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content cita-modal">
            <div class="modal-header cita-modal__header">
                <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Añadir Paciente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body cita-modal__body">

                <!-- Pestañas -->
                <ul class="nav nav-tabs nav-tabs-modal mb-4" id="tabsAnadir">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabExistente">
                            <i class="bi bi-search me-1"></i> Paciente existente
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabNuevo">
                            <i class="bi bi-person-plus me-1"></i> Nuevo paciente
                        </button>
                    </li>
                </ul>

                <div class="tab-content">

                    <!-- ── Pestaña 1: existente ── -->
                    <div class="tab-pane fade show active" id="tabExistente">
                        <form method="POST" action="/controllers/PacienteController.php?accion=asignar">
                            <div class="mb-3">
                                <label class="form-label cita-modal__label">Selecciona paciente</label>
                                <select name="id_paciente" class="form-select cita-modal__input" required>
                                    <option value="">Elige un paciente…</option>
                                    <?php foreach ($todos as $t): ?>
                                        <option value="<?= $t['id_paciente'] ?>">
                                            <?= htmlspecialchars($t['apellido'] . ', ' . $t['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn-guardar">
                                    <i class="bi bi-check-lg"></i> Asignar
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- ── Pestaña 2: nuevo ── -->
                    <div class="tab-pane fade" id="tabNuevo">
                        <form method="POST" action="/controllers/PacienteController.php?accion=crear_y_asignar">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label cita-modal__label">Nombre *</label>
                                    <input type="text" name="nombre" class="form-control cita-modal__input" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label cita-modal__label">Apellido *</label>
                                    <input type="text" name="apellido" class="form-control cita-modal__input" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label cita-modal__label">Fecha de nacimiento</label>
                                    <input type="date" name="fecha_nacimiento" class="form-control cita-modal__input">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label cita-modal__label">Género</label>
                                    <select name="genero" class="form-select cita-modal__input">
                                        <option value="">—</option>
                                        <option value="M">Masculino</option>
                                        <option value="F">Femenino</option>
                                        <option value="Otro">Otro</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label cita-modal__label">Teléfono</label>
                                    <input type="text" name="telefono" class="form-control cita-modal__input">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label cita-modal__label">Email</label>
                                    <input type="email" name="email" class="form-control cita-modal__input">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label cita-modal__label">Dirección</label>
                                    <input type="text" name="direccion" class="form-control cita-modal__input">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label cita-modal__label">DNI</label>
                                    <input type="text" name="dni" class="form-control cita-modal__input" placeholder="12345678A">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label cita-modal__label">Nº Seguridad Social</label>
                                    <input type="text" name="num_seguridad_social" class="form-control cita-modal__input">
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-2 mt-3">
                                <button type="button" class="btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn-guardar">
                                    <i class="bi bi-person-check"></i> Crear y añadir
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>


<!-- ═══════════════════════════════════════════════════════════════
     MODAL — HISTORIAL CLÍNICO
═══════════════════════════════════════════════════════════════ -->
<div class="modal fade modal-xl" id="modalHistorial" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content cita-modal">
            <div class="modal-header cita-modal__header">
                <h5 class="modal-title" id="historial-titulo">
                    <i class="bi bi-clipboard2-pulse me-2"></i>Historial clínico
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body cita-modal__body">

                <!-- Entradas del historial (se rellena por JS) -->
                <div id="historial-lista" class="historial-lista mb-4"></div>

                <!-- Formulario para añadir nueva entrada -->
                <div class="historial-nueva">
                    <div class="historial-nueva__titulo">
                        <i class="bi bi-plus-circle me-1"></i> Nueva entrada
                    </div>
                    <form method="POST" action="/controllers/PacienteController.php?accion=historial_guardar">
                        <input type="hidden" name="id_paciente" id="historial-id-paciente">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label cita-modal__label">Fecha</label>
                                <input type="date" name="fecha" class="form-control cita-modal__input"
                                       value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label cita-modal__label">Tipo de consulta</label>
                                <select name="tipo_consulta" class="form-select cita-modal__input">
                                    <option value="presencial">Presencial</option>
                                    <option value="urgencia">Urgencia</option>
                                    <option value="teleconsulta">Teleconsulta</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label cita-modal__label">Motivo de consulta</label>
                                <input type="text" name="motivo_consulta" class="form-control cita-modal__input"
                                       placeholder="Ej: Dolor de cabeza">
                            </div>
                            <div class="col-12">
                                <label class="form-label cita-modal__label">Diagnóstico</label>
                                <input type="text" name="diagnostico" class="form-control cita-modal__input"
                                       placeholder="Ej: Hipertensión leve" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label cita-modal__label">Tratamiento</label>
                                <input type="text" name="tratamiento" class="form-control cita-modal__input"
                                       placeholder="Medicación, dosis…">
                            </div>
                            <div class="col-12">
                                <label class="form-label cita-modal__label">Notas</label>
                                <textarea name="notas" class="form-control cita-modal__input" rows="2"
                                          placeholder="Observaciones adicionales…"></textarea>
                            </div>
                        </div>
                        <div class="mt-3 d-flex justify-content-end">
                            <button type="submit" class="btn-guardar">
                                <i class="bi bi-check-lg"></i> Guardar entrada
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>


<!-- Formulario oculto para desasignar paciente -->
<form id="form-desasignar" method="POST" action="/controllers/PacienteController.php?accion=desasignar" style="display:none">
    <input type="hidden" name="id_paciente" id="desasignar-id">
</form>

<!-- Datos del historial en JSON para usarlos desde JS -->
<script>
    // Historial de todos los pacientes cargado en PHP y pasado a JS
    const historialesPHP = <?= json_encode(
        array_reduce(
            $pacientes,
            function($carry, $p) use ($modelo) {
                $carry[$p['id_paciente']] = $modelo->obtenerHistorial($p['id_paciente']);
                return $carry;
            },
            []
        )
    ) ?>;
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


<script>
    function verHistorial(idPaciente, nombre) {
        document.getElementById('historial-titulo').innerHTML =
            '<i class="bi bi-clipboard2-pulse me-2"></i>Historial — ' + nombre;
        document.getElementById('historial-id-paciente').value = idPaciente;

        const entradas = historialesPHP[idPaciente] || [];
        const lista    = document.getElementById('historial-lista');

        if (entradas.length === 0) {
            lista.innerHTML = '<p class="text-muted" style="font-size:.875rem">Sin entradas registradas aún.</p>';
        } else {
            lista.innerHTML = entradas.map(e => `
                <div class="historial-entrada">
                    <div class="historial-entrada__fecha">${formatearFecha(e.fecha)} · <span class="historial-entrada__tipo">${e.tipo_consulta}</span></div>
                    ${e.motivo_consulta ? `<div class="historial-entrada__campo"><strong>Motivo:</strong> ${e.motivo_consulta}</div>` : ''}
                    <div class="historial-entrada__diagnostico">${e.diagnostico}</div>
                    ${e.tratamiento ? `<div class="historial-entrada__campo"><strong>Tratamiento:</strong> ${e.tratamiento}</div>` : ''}
                    ${e.notas       ? `<div class="historial-entrada__campo"><strong>Notas:</strong> ${e.notas}</div>` : ''}
                    <div class="historial-entrada__medico">Dr. ${e.medico_nombre} ${e.medico_apellido}</div>
                </div>
            `).join('');
        }

        new bootstrap.Modal(document.getElementById('modalHistorial')).show();
    }

    function confirmarDesasignar(id) {
        if (confirm('¿Quitar este paciente de tu lista? No se borrarán sus datos ni su historial.')) {
            document.getElementById('desasignar-id').value = id;
            document.getElementById('form-desasignar').submit();
        }
    }

    function formatearFecha(fechaISO) {
        const [y, m, d] = fechaISO.split('-');
        return `${d}/${m}/${y}`;
    }
</script>
