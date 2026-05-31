<?php



require_once __DIR__ . '/../../models/CitaModel.php';

$modelo         = new CitaModel($pdo);
$todasCitas     = $modelo->obtenerTodasCitas();            // Todas las citas con datos de paciente, médico y especialidad
$medicos        = $modelo->obtenerMedicosConEspecialidad(); // Para los selects de médico con filtrado por especialidad
$especialidades = $modelo->obtenerEspecialidades();         // Para los selects de especialidad en modales
$pacientes      = $modelo->obtenerPacientes();              // Para el select de paciente en "Nueva Cita"

$filtroEstado    = $_GET['estado']     ?? '';
$filtroDesde     = $_GET['desde']      ?? '';
$filtroHasta     = $_GET['hasta']      ?? '';
$filtroSinMedico = $_GET['sin_medico'] ?? '';
$ok              = $_GET['ok']         ?? '';

$citasFiltradas = array_values(array_filter($todasCitas, function($c) use ($filtroEstado, $filtroDesde, $filtroHasta, $filtroSinMedico) {
    if ($filtroEstado    && $c['estado']     !== $filtroEstado)  return false;
    if ($filtroDesde     && $c['fecha_cita'] <  $filtroDesde)   return false;
    if ($filtroHasta     && $c['fecha_cita'] >  $filtroHasta)   return false;
    if ($filtroSinMedico && !empty($c['id_empleado']))           return false;
    return true;
}));

// Paginación
$porPagina   = 15;
$totalCitas  = count($citasFiltradas);
$totalPags   = max(1, (int)ceil($totalCitas / $porPagina));
$paginaActual = max(1, min((int)($_GET['pag'] ?? 1), $totalPags));
$offset      = ($paginaActual - 1) * $porPagina;
$citasPagina = array_slice($citasFiltradas, $offset, $porPagina);

$colores = ['#0a6e5c','#12907a','#2ec4a5','#c8903a','#d95f5f','#5a9e8f'];
$mensajesOk = [
    'asignado'    => 'Médico asignado correctamente.',
    'creada'      => 'Cita creada correctamente.',
    'actualizada' => 'Cita actualizada correctamente.',
    'eliminada'   => 'Cita eliminada correctamente.'
];
?>

<link rel="stylesheet" href="/assets/css/panel_medico.css">
<link rel="stylesheet" href="/assets/css/citas_medico.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<?php
$pagina_activa_rec = 'citas';
require_once __DIR__ . '/../../includes/sidebar_recepcionista.php';
?>

<main class="panel-principal">

    <div class="cabecera">
        <h1>Citas</h1>
        <div class="cabecera__fecha">
            <i class="bi bi-calendar3"></i>
            <span><?= date('d/m/Y') ?></span>
        </div>
    </div>

    <?php if ($ok && isset($mensajesOk[$ok])): ?>
        <div class="conf-alerta conf-alerta--ok" style="background:#ddf6f1;color:#0a6e5c;border:1px solid #b2e8de;border-radius:12px;padding:12px 18px;font-size:.875rem;margin-bottom:20px;display:flex;align-items:center;gap:10px;">
            <i class="bi bi-check-circle-fill"></i> <?= $mensajesOk[$ok] ?>
        </div>
    <?php endif; ?>

    <!-- Filtros -->
    <form method="GET" action="/recepcionista/citas_recepcionista.php">
        <div class="filtros-card">
            <div>
                <label>Desde</label>
                <input type="date" name="desde" value="<?= htmlspecialchars($filtroDesde) ?>">
            </div>
            <div>
                <label>Hasta</label>
                <input type="date" name="hasta" value="<?= htmlspecialchars($filtroHasta) ?>">
            </div>
            <div>
                <label>Estado</label>
                <select name="estado">
                    <option value="">Todos</option>
                    <option value="pendiente"  <?= $filtroEstado === 'pendiente'  ? 'selected':'' ?>>Pendiente</option>
                    <option value="programada" <?= $filtroEstado === 'programada' ? 'selected':'' ?>>Programada</option>
                    <option value="completada" <?= $filtroEstado === 'completada' ? 'selected':'' ?>>Completada</option>
                    <option value="cancelada"  <?= $filtroEstado === 'cancelada'  ? 'selected':'' ?>>Cancelada</option>
                </select>
            </div>
            <div style="display:flex;align-items:center;gap:8px;padding-top:20px">
                <input type="checkbox" name="sin_medico" value="1" id="chk-sin-medico" <?= $filtroSinMedico ? 'checked':'' ?> style="width:16px;height:16px">
                <label for="chk-sin-medico" style="font-size:.85rem;font-weight:600;color:var(--gris-suave);margin:0">Sin médico</label>
            </div>
            <button type="submit" class="btn-filtrar"><i class="bi bi-search"></i> Filtrar</button>
            <a href="/recepcionista/citas_recepcionista.php" class="btn-limpiar">Limpiar</a>
            <button type="button" class="btn-nueva-cita ms-auto" data-bs-toggle="modal" data-bs-target="#modalNuevaCita">
                <i class="bi bi-plus-lg"></i> Nueva Cita
            </button>
        </div>
    </form>

    <!-- Tabla -->
    <div class="tabla-card">
        <h5>Total: <?= $totalCitas ?> citas</h5>
        <?php if (empty($citasFiltradas)): ?>
            <div class="sin-resultados">
                <i class="bi bi-calendar-x"></i>
                No se encontraron citas con los filtros aplicados.
            </div>
        <?php else: ?>
            <table class="tabla-citas">
                <thead>
                    <tr>
                        <th>Paciente</th>
                        <th>Médico</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Motivo</th>
                        <th>Estado</th>
                        <th style="text-align:center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($citasPagina as $i => $c):
                        $iniciales = strtoupper(substr($c['paciente_nombre'],0,1).substr($c['paciente_apellido'],0,1));
                        $color     = $colores[($offset + $i) % count($colores)];
                    ?>
                    <tr>
                        <td>
                            <div class="paciente-cell">
                                <div class="mini-avatar" style="background:<?= $color ?>"><?= $iniciales ?></div>
                                <?= htmlspecialchars($c['paciente_nombre'].' '.$c['paciente_apellido']) ?>
                            </div>
                        </td>
                        <td>
                            <?php if ($c['medico_nombre']): ?>
                                Dr. <?= htmlspecialchars($c['medico_nombre'].' '.$c['medico_apellido']) ?>
                            <?php else: ?>
                                <span style="color:var(--advertencia);font-weight:600;font-size:.78rem">
                                    <i class="bi bi-exclamation-circle"></i> Sin asignar
                                </span>
                                <?php if (!empty($c['cita_especialidad_nombre'])): ?>
                                    <div style="font-size:.74rem;color:var(--verde);margin-top:2px">
                                        <i class="bi bi-hospital"></i> <?= htmlspecialchars($c['cita_especialidad_nombre']) ?>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d/m/Y', strtotime($c['fecha_cita'])) ?></td>
                        <td><?= substr($c['hora_cita'],0,5) ?></td>
                        <td style="max-width:160px;font-size:.82rem;color:var(--gris-suave)">
                            <?= htmlspecialchars($c['motivo'] ?? '—') ?>
                        </td>
                        <td><span class="badge-<?= $c['estado'] ?>"><?= ucfirst($c['estado']) ?></span></td>
                        <td style="text-align:center">
                            <div class="acciones-cell" style="justify-content:center">
                                <button class="btn-accion btn-editar"
                                    onclick="abrirEditar(
                                        <?= $c['id_cita'] ?>,
                                        '<?= $c['id_empleado'] ?? '' ?>',
                                        '<?= $c['medico_especialidad_id'] ?? '' ?>',
                                        '<?= $c['fecha_cita'] ?>',
                                        '<?= substr($c['hora_cita'],0,5) ?>',
                                        '<?= $c['estado'] ?>',
                                        '<?= htmlspecialchars(addslashes($c['motivo'] ?? '')) ?>'
                                    )" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn-accion btn-eliminar"
                                    onclick="confirmarEliminar(<?= $c['id_cita'] ?>)" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($totalPags > 1): ?>
            <?php
          
            $params = array_filter($_GET, fn($k) => $k !== 'pag', ARRAY_FILTER_USE_KEY);
            $qs     = $params ? '&'.http_build_query($params) : '';
            ?>
            <nav style="display:flex;justify-content:center;gap:6px;margin-top:18px;flex-wrap:wrap">
                <?php if ($paginaActual > 1): ?>
                    <a href="?pag=<?= $paginaActual-1 ?><?= $qs ?>" class="btn-filtrar" style="margin:0;padding:6px 12px">&laquo;</a>
                <?php endif; ?>
                <?php for ($p = 1; $p <= $totalPags; $p++): ?>
                    <a href="?pag=<?= $p ?><?= $qs ?>"
                       class="btn-filtrar" style="margin:0;padding:6px 12px;<?= $p === $paginaActual ? 'background:var(--acento);color:#fff' : '' ?>">
                        <?= $p ?>
                    </a>
                <?php endfor; ?>
                <?php if ($paginaActual < $totalPags): ?>
                    <a href="?pag=<?= $paginaActual+1 ?><?= $qs ?>" class="btn-filtrar" style="margin:0;padding:6px 12px">&raquo;</a>
                <?php endif; ?>
            </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>

</main>


<!-- MODAL — NUEVA CITA -->
<div class="modal fade" id="modalNuevaCita" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content cita-modal">
            <div class="modal-header cita-modal__header">
                <h5 class="modal-title"><i class="bi bi-calendar-plus me-2"></i>Nueva Cita</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/controllers/RecepcionistaController.php?accion=guardar">
                <div class="modal-body cita-modal__body">
                    <div class="mb-3">
                        <label class="form-label cita-modal__label">Paciente</label>
                        <select name="id_paciente" class="form-select cita-modal__input" required>
                            <option value="">Selecciona…</option>
                            <?php foreach ($pacientes as $p): ?>
                                <option value="<?= $p['id_paciente'] ?>"><?= htmlspecialchars($p['apellido'].', '.$p['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label cita-modal__label">Especialidad</label>
                        <select id="nueva-especialidad" class="form-select cita-modal__input" onchange="filtrarMedicos('nueva-especialidad','nueva-medico')">
                            <option value="">— Todas —</option>
                            <?php foreach ($especialidades as $esp): ?>
                                <option value="<?= $esp['id_especialidad'] ?>"><?= htmlspecialchars($esp['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label cita-modal__label">Médico</label>
                        <select name="id_empleado" id="nueva-medico" class="form-select cita-modal__input">
                            <option value="">Sin asignar</option>
                            <?php foreach ($medicos as $m): ?>
                                <option value="<?= $m['id_empleado'] ?>" data-esp="<?= $m['id_especialidad'] ?>">
                                    Dr. <?= htmlspecialchars($m['apellido'].', '.$m['nombre']) ?> — <?= htmlspecialchars($m['especialidad']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label cita-modal__label">Fecha</label>
                            <input type="date" name="fecha_cita" class="form-control cita-modal__input" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label cita-modal__label">Hora</label>
                            <input type="time" name="hora_cita" class="form-control cita-modal__input" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label cita-modal__label">Motivo</label>
                        <textarea name="motivo" class="form-control cita-modal__input" rows="2" placeholder="Motivo de la consulta…"></textarea>
                    </div>
                </div>
                <div class="modal-footer cita-modal__footer">
                    <button type="button" class="btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-guardar"><i class="bi bi-check-lg"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- MODAL — EDITAR CITA -->
<div class="modal fade" id="modalEditar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content cita-modal">
            <div class="modal-header cita-modal__header">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Cita</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/controllers/RecepcionistaController.php?accion=actualizar">
                <input type="hidden" name="id_cita" id="edit-id">
                <div class="modal-body cita-modal__body">
                    <div class="mb-3">
                        <label class="form-label cita-modal__label">Especialidad</label>
                        <select id="edit-especialidad" class="form-select cita-modal__input" onchange="filtrarMedicos('edit-especialidad','edit-medico')">
                            <option value="">— Todas —</option>
                            <?php foreach ($especialidades as $esp): ?>
                                <option value="<?= $esp['id_especialidad'] ?>"><?= htmlspecialchars($esp['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label cita-modal__label">Médico</label>
                        <select name="id_empleado" id="edit-medico" class="form-select cita-modal__input">
                            <option value="">Sin asignar</option>
                            <?php foreach ($medicos as $m): ?>
                                <option value="<?= $m['id_empleado'] ?>" data-esp="<?= $m['id_especialidad'] ?>">
                                    Dr. <?= htmlspecialchars($m['apellido'].', '.$m['nombre']) ?> — <?= htmlspecialchars($m['especialidad']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label cita-modal__label">Fecha</label>
                            <input type="date" name="fecha_cita" id="edit-fecha" class="form-control cita-modal__input" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label cita-modal__label">Hora</label>
                            <input type="time" name="hora_cita" id="edit-hora" class="form-control cita-modal__input" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label cita-modal__label">Estado</label>
                        <select name="estado" id="edit-estado" class="form-select cita-modal__input">
                            <option value="pendiente">Pendiente</option>
                            <option value="programada">Programada</option>
                            <option value="completada">Completada</option>
                            <option value="cancelada">Cancelada</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label cita-modal__label">Motivo</label>
                        <textarea name="motivo" id="edit-motivo" class="form-control cita-modal__input" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer cita-modal__footer">
                    <button type="button" class="btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-guardar"><i class="bi bi-check-lg"></i> Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="form-eliminar" method="POST" action="/controllers/RecepcionistaController.php?accion=eliminar" style="display:none">
    <input type="hidden" name="id_cita" id="eliminar-id">
</form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Filtra el select de médicos según la especialidad seleccionada
function filtrarMedicos(idEsp, idMed) {
    const espVal = document.getElementById(idEsp).value;
    const medSel = document.getElementById(idMed);
    Array.from(medSel.options).forEach(opt => {
        if (!opt.value) return; // "Sin asignar" siempre visible
        opt.hidden = espVal !== '' && opt.dataset.esp !== espVal;
    });
    // Si el médico actual quedó oculto resetearlo
    const cur = medSel.options[medSel.selectedIndex];
    if (cur && cur.hidden) medSel.value = '';
}

function abrirEditar(id, idMedico, idEspecialidad, fecha, hora, estado, motivo) {
    document.getElementById('edit-id').value            = id;
    document.getElementById('edit-especialidad').value  = idEspecialidad || '';
    filtrarMedicos('edit-especialidad', 'edit-medico');
    document.getElementById('edit-medico').value        = idMedico;
    document.getElementById('edit-fecha').value         = fecha;
    document.getElementById('edit-hora').value          = hora;
    document.getElementById('edit-estado').value        = estado;
    document.getElementById('edit-motivo').value        = motivo;
    new bootstrap.Modal(document.getElementById('modalEditar')).show();
}
function confirmarEliminar(id) {
    if (confirm('¿Eliminar esta cita? Esta acción no se puede deshacer.')) {
        document.getElementById('eliminar-id').value = id;
        document.getElementById('form-eliminar').submit();
    }
}
</script>
