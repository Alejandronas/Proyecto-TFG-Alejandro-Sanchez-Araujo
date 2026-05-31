<?php

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/PacienteModel.php';

$modelo   = new PacienteModel($pdo);
$historial = $modelo->obtenerHistorial($_SESSION['id_paciente']);

$filtroTipo  = $_GET['tipo']  ?? '';
$filtroDesde = $_GET['desde'] ?? '';
$filtroHasta = $_GET['hasta'] ?? '';

$historialFiltrado = array_filter($historial, function($h) use ($filtroTipo, $filtroDesde, $filtroHasta) {
    if ($filtroTipo  && $h['tipo_consulta'] !== $filtroTipo) return false;
    if ($filtroDesde && $h['fecha']         <  $filtroDesde) return false;
    if ($filtroHasta && $h['fecha']         >  $filtroHasta) return false;
    return true;
});

$colorTipo = [
    'consulta'      => ['bg' => '#ddf6f1', 'color' => '#0a8a70'],
    'revision'      => ['bg' => '#e8f0fe', 'color' => '#1a6fa8'],
    'urgencia'      => ['bg' => '#fdeaea', 'color' => '#d95f5f'],
    'seguimiento'   => ['bg' => '#fdf3e3', 'color' => '#c8903a'],
];
?>

<link rel="stylesheet" href="/assets/css/panel_medico.css">
<link rel="stylesheet" href="/assets/css/citas_medico.css">
<link rel="stylesheet" href="/assets/css/panel_paciente.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<?php
$pagina_activa_pac = 'historial';
require_once __DIR__ . '/../../includes/sidebar_paciente.php';
?>

<main class="panel-principal">

    <div class="cabecera">
        <h1>Mi Historial</h1>
        <div class="cabecera__fecha">
            <i class="bi bi-calendar3"></i>
            <span><?= date('d/m/Y') ?></span>
        </div>
    </div>

    <!-- Filtros -->
    <form method="GET" action="/paciente/historial_paciente.php">
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
                <label>Tipo</label>
                <select name="tipo">
                    <option value="">Todos</option>
                    <option value="presencial"   <?= $filtroTipo === 'presencial'   ? 'selected' : '' ?>>Presencial</option>
                    <option value="urgencia"    <?= $filtroTipo === 'urgencia'    ? 'selected' : '' ?>>Urgencia</option>
                    <option value="teleconsulta"<?= $filtroTipo === 'teleconsulta'? 'selected' : '' ?>>Teleconsulta</option>
                </select>
            </div>
            <button type="submit" class="btn-filtrar"><i class="bi bi-search"></i> Filtrar</button>
            <a href="/paciente/historial_paciente.php" class="btn-limpiar">Limpiar</a>
        </div>
    </form>

    <!-- Entradas del historial -->
    <div class="tabla-card">
        <h5>Total: <?= count($historialFiltrado) ?> entrada<?= count($historialFiltrado) !== 1 ? 's' : '' ?></h5>

        <?php if (empty($historialFiltrado)): ?>
            <div class="sin-resultados">
                <i class="bi bi-clipboard2-x"></i>
                No hay entradas en tu historial con los filtros aplicados.
            </div>
        <?php else: ?>
            <?php foreach ($historialFiltrado as $h):
                $ct = $colorTipo[$h['tipo_consulta']] ?? ['bg' => '#f0f0f0', 'color' => '#555'];
            ?>
            <div class="historial-entrada-pac">
                <div class="historial-entrada-pac__cabecera">
                    <div class="historial-entrada-pac__fecha">
                        <i class="bi bi-calendar2-check"></i>
                        <?= date('d/m/Y', strtotime($h['fecha'])) ?>
                    </div>
                    <span class="historial-badge"
                          style="background:<?= $ct['bg'] ?>;color:<?= $ct['color'] ?>">
                        <?= ucfirst($h['tipo_consulta'] ?? '—') ?>
                    </span>
                    <div class="historial-entrada-pac__medico">
                        <i class="bi bi-person-badge"></i>
                        Dr. <?= htmlspecialchars(($h['medico_nombre'] ?? '') . ' ' . ($h['medico_apellido'] ?? '')) ?>
                    </div>
                </div>

                <?php if (!empty($h['motivo_consulta'])): ?>
                <div class="historial-campo">
                    <span class="historial-campo__label"><i class="bi bi-chat-left-text"></i> Motivo</span>
                    <span class="historial-campo__valor"><?= htmlspecialchars($h['motivo_consulta']) ?></span>
                </div>
                <?php endif; ?>

                <?php if (!empty($h['diagnostico'])): ?>
                <div class="historial-campo">
                    <span class="historial-campo__label"><i class="bi bi-clipboard2-pulse"></i> Diagnóstico</span>
                    <span class="historial-campo__valor"><?= htmlspecialchars($h['diagnostico']) ?></span>
                </div>
                <?php endif; ?>

                <?php if (!empty($h['tratamiento'])): ?>
                <div class="historial-campo">
                    <span class="historial-campo__label"><i class="bi bi-capsule"></i> Tratamiento</span>
                    <span class="historial-campo__valor"><?= htmlspecialchars($h['tratamiento']) ?></span>
                </div>
                <?php endif; ?>

                <?php if (!empty($h['notas'])): ?>
                <div class="historial-campo">
                    <span class="historial-campo__label"><i class="bi bi-sticky"></i> Notas</span>
                    <span class="historial-campo__valor historial-campo__valor--notas"><?= htmlspecialchars($h['notas']) ?></span>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</main>

<style>
.historial-entrada-pac {
    background: var(--fondo-tarjeta);
    border: 1px solid var(--borde);
    border-radius: 16px;
    padding: 20px 24px;
    margin-bottom: 14px;
}

.historial-entrada-pac__cabecera {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
    margin-bottom: 14px;
}

.historial-entrada-pac__fecha {
    display: flex;
    align-items: center;
    gap: 6px;
    font-weight: 700;
    font-size: .9rem;
    color: var(--gris-texto);
}

.historial-badge {
    font-size: .72rem;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
}

.historial-entrada-pac__medico {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: .8rem;
    color: var(--gris-suave);
    margin-left: auto;
}

.historial-campo {
    display: flex;
    gap: 10px;
    align-items: flex-start;
    padding: 8px 0;
    border-top: 1px solid var(--borde);
    font-size: .875rem;
}

.historial-campo__label {
    min-width: 120px;
    font-weight: 600;
    color: var(--gris-suave);
    display: flex;
    align-items: center;
    gap: 6px;
    flex-shrink: 0;
}

.historial-campo__valor {
    color: var(--gris-texto);
    flex: 1;
}

.historial-campo__valor--notas {
    font-style: italic;
    color: var(--gris-suave);
}
</style>
