<?php



require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/CitaModel.php';
require_once __DIR__ . '/../../models/PacienteModel.php';

$modelo         = new CitaModel($pdo);
$modeloPaciente = new PacienteModel($pdo);

$todasCitas = $modelo->obtenerTodasCitasMedico($_SESSION['id_empleado']);
$pacientes  = $modelo->obtenerPacientes();


// agrupado por id_paciente,
$historialesPorPaciente = [];
foreach ($todasCitas as $c) {
    $id = $c['id_paciente'];
    if (!isset($historialesPorPaciente[$id])) {
        $historialesPorPaciente[$id] = $modeloPaciente->obtenerHistorial($id);
    }
}

$filtroEstado = $_GET['estado'] ?? '';
$filtroDesde  = $_GET['desde']  ?? '';
$filtroHasta  = $_GET['hasta']  ?? '';

$citasFiltradas = array_filter($todasCitas, function($cita) use ($filtroEstado, $filtroDesde, $filtroHasta) {
    if ($filtroEstado && $cita['estado'] !== $filtroEstado) return false;
    if ($filtroDesde  && $cita['fecha_cita'] < $filtroDesde)  return false;
    if ($filtroHasta  && $cita['fecha_cita'] > $filtroHasta)  return false;
    return true;
});

$colores = ['#0a6e5c','#12907a','#2ec4a5','#c8903a','#d95f5f','#5a9e8f'];
?>

<link rel="stylesheet" href="/assets/css/panel_medico.css">
<link rel="stylesheet" href="/assets/css/citas_medico.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<?php
$pagina_activa = 'citas';
require_once __DIR__ . '/../../includes/sidebar_medico.php';
?>

<main class="panel-principal">

    <div class="cabecera">
        <h1>Mis Citas</h1>
        <div class="cabecera__fecha">
            <i class="bi bi-calendar3"></i>
            <span><?= date('d/m/Y') ?></span>
        </div>
    </div>

    <?php if (($_GET['ok'] ?? '') === 'completada'): ?>
        <div style="background:#ddf6f1;color:#0a6e5c;border:1px solid #b2e8de;border-radius:12px;padding:12px 18px;font-size:.875rem;margin-bottom:20px;display:flex;align-items:center;gap:10px;">
            <i class="bi bi-check-circle-fill"></i> Cita marcada como completada.
        </div>
    <?php endif; ?>

    <!-- Filtros -->
    <form method="GET" action="/medico/citas.php">
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
                    <option value="programada" <?= $filtroEstado === 'programada' ? 'selected' : '' ?>>Programada</option>
                    <option value="completada" <?= $filtroEstado === 'completada' ? 'selected' : '' ?>>Completada</option>
                    <option value="cancelada"  <?= $filtroEstado === 'cancelada'  ? 'selected' : '' ?>>Cancelada</option>
                </select>
            </div>
            <button type="submit" class="btn-filtrar">
                <i class="bi bi-search"></i> Filtrar
            </button>
            <a href="/medico/citas.php" class="btn-limpiar">Limpiar</a>
        </div>
    </form>

    <!-- Tabla -->
    <div class="tabla-card">
        <h5>Total: <?= count($citasFiltradas) ?> citas</h5>

        <?php if (empty($citasFiltradas)): ?>
            <div class="sin-resultados">
                <i class="bi bi-calendar-x"></i>
                No tienes citas próximas.
            </div>
        <?php else: ?>
            <table class="tabla-citas">
                <thead>
                    <tr>
                        <th>Paciente</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Estado</th>
                        <th style="text-align:center">Completar</th>
                        <th style="text-align:center">Historial</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($citasFiltradas as $i => $cita):
                        $iniciales   = strtoupper(substr($cita['nombre'],0,1) . substr($cita['apellido'],0,1));
                        $color       = $colores[$i % count($colores)];
                        $completada  = $cita['estado'] === 'completada';
                        $cancelada   = $cita['estado'] === 'cancelada';
                    ?>
                    <tr>
                        <td>
                            <div class="paciente-cell">
                                <div class="mini-avatar" style="background:<?= $color ?>"><?= $iniciales ?></div>
                                <?= htmlspecialchars($cita['nombre'] . ' ' . $cita['apellido']) ?>
                            </div>
                        </td>
                        <td><?= date('d/m/Y', strtotime($cita['fecha_cita'])) ?></td>
                        <td><?= substr($cita['hora_cita'], 0, 5) ?></td>
                        <td><span class="badge-<?= $cita['estado'] ?>"><?= ucfirst($cita['estado']) ?></span></td>
                        <td style="text-align:center">
                            <?php if ($completada): ?>
                                <!-- Checkbox desactivado si ya está completada -->
                                <input type="checkbox" checked disabled
                                       style="width:18px;height:18px;accent-color:var(--acento)">
                            <?php elseif (!$cancelada): ?>
                                <!-- Al marcar el checkbox se envía el formulario y completa la cita -->
                                <form method="POST" action="/controllers/CitaController.php?accion=completar"
                                      onsubmit="return confirm('¿Marcar esta cita como completada?')">
                                    <input type="hidden" name="id_cita" value="<?= $cita['id_cita'] ?>">
                                    <input type="checkbox" name="confirmar" onclick="this.form.submit()"
                                           style="width:18px;height:18px;cursor:pointer;accent-color:var(--acento)">
                                </form>
                            <?php else: ?>
                                <span style="color:#ccc;font-size:.8rem">—</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:center">
                            <button class="btn-accion btn-editar"
                                onclick="verHistorial(<?= $cita['id_paciente'] ?>, '<?= htmlspecialchars($cita['nombre'] . ' ' . $cita['apellido']) ?>')">
                                <i class="bi bi-clipboard2-pulse"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</main>


<!-- MODAL — HISTORIAL DEL PACIENTE -->
<div class="modal fade" id="modalHistorial" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content cita-modal">
            <div class="modal-header cita-modal__header">
                <h5 class="modal-title"><i class="bi bi-clipboard2-pulse me-2"></i>Historial de <span id="hist-nombre"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body cita-modal__body" id="hist-cuerpo" style="max-height:65vh;overflow-y:auto;">
                <!-- se rellena por JS -->
            </div>
            <div class="modal-footer cita-modal__footer">
                <button type="button" class="btn-cancelar" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const historialesPorPaciente = <?= json_encode($historialesPorPaciente) ?>;

const colorTipo = {
    presencial:    { bg: '#ddf6f1', color: '#0a8a70' },
    teleconsulta:  { bg: '#e8f0fe', color: '#1a6fa8' },
    urgencia:      { bg: '#fdeaea', color: '#d95f5f' },
};

function verHistorial(idPaciente, nombre) {
    document.getElementById('hist-nombre').textContent = nombre;
    const entradas = historialesPorPaciente[idPaciente] || [];
    const cuerpo   = document.getElementById('hist-cuerpo');

    if (!entradas.length) {
        cuerpo.innerHTML = '<p style="text-align:center;color:#aaa;padding:32px 0"><i class="bi bi-clipboard2-x" style="font-size:2rem;display:block;margin-bottom:10px"></i>Sin entradas en el historial.</p>';
    } else {
        cuerpo.innerHTML = entradas.map(h => {
            const ct    = colorTipo[h.tipo_consulta] || { bg: '#f0f0f0', color: '#555' };
            const [y, m, d] = (h.fecha || '').split('-');
            const fecha = d ? `${d}/${m}/${y}` : '—';
            const campos = [
                h.motivo_consulta ? `<div class="hc"><span class="hc-label"><i class="bi bi-chat-left-text"></i> Motivo</span><span>${h.motivo_consulta}</span></div>` : '',
                h.diagnostico     ? `<div class="hc"><span class="hc-label"><i class="bi bi-clipboard2-pulse"></i> Diagnóstico</span><span>${h.diagnostico}</span></div>` : '',
                h.tratamiento     ? `<div class="hc"><span class="hc-label"><i class="bi bi-capsule"></i> Tratamiento</span><span>${h.tratamiento}</span></div>` : '',
                h.notas           ? `<div class="hc"><span class="hc-label"><i class="bi bi-sticky"></i> Notas</span><span style="font-style:italic;color:#888">${h.notas}</span></div>` : '',
            ].join('');
            return `
            <div style="border:1px solid #dde8e5;border-radius:14px;padding:16px 20px;margin-bottom:12px">
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;flex-wrap:wrap">
                    <strong style="font-size:.9rem">${fecha}</strong>
                    <span style="background:${ct.bg};color:${ct.color};font-size:.7rem;font-weight:700;padding:3px 10px;border-radius:20px">${(h.tipo_consulta||'').charAt(0).toUpperCase()+(h.tipo_consulta||'').slice(1)}</span>
                </div>
                ${campos}
            </div>`;
        }).join('');
    }

    new bootstrap.Modal(document.getElementById('modalHistorial')).show();
}
</script>

<style>
.hc { display:flex; gap:10px; align-items:flex-start; padding:7px 0; border-top:1px solid #dde8e5; font-size:.85rem; }
.hc-label { min-width:120px; font-weight:600; color:#6b6b6b; display:flex; align-items:center; gap:5px; flex-shrink:0; }
</style>
