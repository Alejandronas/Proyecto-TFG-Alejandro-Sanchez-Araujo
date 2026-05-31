<?php

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/CitaModel.php';

$modelo     = new CitaModel($pdo);
$todasCitas = $modelo->obtenerCitasPaciente($_SESSION['id_paciente']);

$filtroEstado = $_GET['estado'] ?? '';
$filtroDesde  = $_GET['desde']  ?? '';
$filtroHasta  = $_GET['hasta']  ?? '';

$citasFiltradas = array_values(array_filter($todasCitas, function($c) use ($filtroEstado, $filtroDesde, $filtroHasta) {
    if ($filtroEstado && $c['estado'] !== $filtroEstado) return false;
    if ($filtroDesde  && $c['fecha_cita'] < $filtroDesde)  return false;
    if ($filtroHasta  && $c['fecha_cita'] > $filtroHasta)  return false;
    return true;
}));

// Separar por estado
$pendientes  = array_values(array_filter($citasFiltradas, fn($c) => $c['estado'] === 'pendiente'));
$programadas = array_values(array_filter($citasFiltradas, fn($c) => $c['estado'] === 'programada'));
$completadas = array_values(array_filter($citasFiltradas, fn($c) => $c['estado'] === 'completada'));
$canceladas  = array_values(array_filter($citasFiltradas, fn($c) => $c['estado'] === 'cancelada'));

$colores = ['#0a6e5c','#12907a','#2ec4a5','#c8903a','#d95f5f','#5a9e8f'];
$ok = $_GET['ok'] ?? '';
?>

<link rel="stylesheet" href="/assets/css/panel_medico.css">
<link rel="stylesheet" href="/assets/css/citas_medico.css">
<link rel="stylesheet" href="/assets/css/panel_paciente.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<?php
$pagina_activa_pac = 'citas';
require_once __DIR__ . '/../../includes/sidebar_paciente.php';
?>

<main class="panel-principal">

    <div class="cabecera">
        <h1>Mis Citas</h1>
        <div class="cabecera__fecha">
            <i class="bi bi-calendar3"></i>
            <span><?= date('d/m/Y') ?></span>
        </div>
    </div>

    <?php if ($ok === '1' || $ok === 'solicitada'): ?>
        <div class="conf-alerta conf-alerta--ok" style="background:#ddf6f1;color:#0a6e5c;border:1px solid #b2e8de;border-radius:12px;padding:12px 18px;font-size:.875rem;margin-bottom:20px;display:flex;align-items:center;gap:10px;">
            <i class="bi bi-check-circle-fill"></i> Cita solicitada correctamente.
        </div>
    <?php elseif ($ok === 'cancelada'): ?>
        <div style="background:#fdeaea;color:#d95f5f;border:1px solid #f5c2c2;border-radius:12px;padding:12px 18px;font-size:.875rem;margin-bottom:20px;display:flex;align-items:center;gap:10px;">
            <i class="bi bi-x-circle-fill"></i> Cita cancelada correctamente.
        </div>
    <?php endif; ?>

    <!-- Filtros -->
    <form method="GET" action="/paciente/citas_paciente.php">
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
                    <option value="pendiente"  <?= $filtroEstado === 'pendiente'  ? 'selected' : '' ?>>Pendiente</option>
                    <option value="programada" <?= $filtroEstado === 'programada' ? 'selected' : '' ?>>Programada</option>
                    <option value="completada" <?= $filtroEstado === 'completada' ? 'selected' : '' ?>>Completada</option>
                    <option value="cancelada"  <?= $filtroEstado === 'cancelada'  ? 'selected' : '' ?>>Cancelada</option>
                </select>
            </div>
            <button type="submit" class="btn-filtrar"><i class="bi bi-search"></i> Filtrar</button>
            <a href="/paciente/citas_paciente.php" class="btn-limpiar">Limpiar</a>
            <a href="/paciente/pedir_cita.php" class="btn-nueva-cita ms-auto" style="text-decoration:none">
                <i class="bi bi-plus-lg"></i> Solicitar Cita
            </a>
        </div>
    </form>

    <?php if (empty($citasFiltradas)): ?>
        <div class="tabla-card">
            <div class="sin-resultados">
                <i class="bi bi-calendar-x"></i>
                No se encontraron citas.
            </div>
        </div>
    <?php endif; ?>

    <?php
    $secciones = [
        ['datos' => $pendientes,  'titulo' => 'Pendientes de asignación', 'icono' => 'bi-hourglass-split',   'color' => '#6d28d9', 'cancelable' => true],
        ['datos' => $programadas, 'titulo' => 'Programadas',              'icono' => 'bi-calendar-check',    'color' => '#c8903a', 'cancelable' => true],
        ['datos' => $completadas, 'titulo' => 'Completadas',              'icono' => 'bi-check-circle-fill', 'color' => '#0a6e5c', 'cancelable' => false],
        ['datos' => $canceladas,  'titulo' => 'Canceladas',               'icono' => 'bi-x-circle-fill',     'color' => '#d95f5f', 'cancelable' => false],
    ];
    foreach ($secciones as $sec):
        if (empty($sec['datos'])) continue;
    ?>
    <div class="titulo-seccion" style="color:<?= $sec['color'] ?>;margin-top:24px">
        <i class="bi <?= $sec['icono'] ?> me-1"></i>
        <?= $sec['titulo'] ?> <span style="font-size:.8rem;font-weight:400">(<?= count($sec['datos']) ?>)</span>
    </div>
    <div class="tabla-card" style="margin-top:8px">
        <table class="tabla-citas">
            <thead>
                <tr>
                    <th style="width:25%">Médico</th>
                    <th style="width:18%">Especialidad</th>
                    <th style="width:11%">Fecha</th>
                    <th style="width:7%">Hora</th>
                    <th>Motivo</th>
                    <?php if ($sec['cancelable']): ?>
                    <th style="width:8%;text-align:center"></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sec['datos'] as $i => $c):
                    $color = $colores[$i % count($colores)];
                    $tieneMedico = !empty($c['medico_nombre']);
                    $iniciales   = $tieneMedico ? strtoupper(substr($c['medico_nombre'],0,1).substr($c['medico_apellido'],0,1)) : '?';
                ?>
                <tr>
                    <td>
                        <div class="paciente-cell">
                            <div class="mini-avatar" style="background:<?= $color ?>"><?= $iniciales ?></div>
                            <?php if ($tieneMedico): ?>
                                Dr. <?= htmlspecialchars($c['medico_nombre'].' '.$c['medico_apellido']) ?>
                            <?php else: ?>
                                <em style="color:var(--gris-suave);font-size:.85rem">Pendiente de asignar</em>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($c['especialidad'] ?? '—') ?></td>
                    <td><?= date('d/m/Y', strtotime($c['fecha_cita'])) ?></td>
                    <td><?= substr($c['hora_cita'],0,5) ?></td>
                    <td style="font-size:.82rem;color:var(--gris-suave)"><?= htmlspecialchars($c['motivo'] ?? '—') ?></td>
                    <?php if ($sec['cancelable']): ?>
                    <td style="text-align:center">
                        <form method="POST" action="/controllers/PerfilPacienteController.php?accion=cancelar_cita"
                              onsubmit="return confirm('¿Seguro que quieres cancelar esta cita?')">
                            <input type="hidden" name="id_cita" value="<?= $c['id_cita'] ?>">
                            <button type="submit" class="btn-accion btn-eliminar" title="Cancelar cita" style="display:inline-flex;margin:0 auto">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </form>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endforeach; ?>

</main>




<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
