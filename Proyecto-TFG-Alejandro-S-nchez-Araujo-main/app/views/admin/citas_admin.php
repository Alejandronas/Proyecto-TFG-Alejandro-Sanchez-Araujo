<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/AdminModel.php';

$modelo      = new AdminModel($pdo);
$citasMedico = $modelo->obtenerCitasPorMedico();
$maxCitas    = $citasMedico ? max(1, max(array_column($citasMedico, 'total'))) : 1;
$totalCitas  = array_sum(array_column($citasMedico, 'total'));
?>

<link rel="stylesheet" href="/assets/css/panel_medico.css">
<link rel="stylesheet" href="/assets/css/citas_medico.css">
<link rel="stylesheet" href="/assets/css/panel_admin.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<?php
$pagina_activa_admin = 'citas';
require_once __DIR__ . '/../../includes/sidebar_admin.php';
?>

<main class="panel-principal">

    <div class="cabecera">
        <h1>Citas por médico</h1>
        <div class="cabecera__fecha">
            <i class="bi bi-calendar3"></i>
            <span><?= date('d/m/Y') ?></span>
        </div>
    </div>

    <div class="tabla-card">
        <h5 style="margin-bottom:20px">
            Total de citas en el sistema: <strong><?= $totalCitas ?></strong>
        </h5>

        <?php if (empty($citasMedico)): ?>
            <div class="sin-resultados"><i class="bi bi-calendar-x"></i> Sin datos.</div>
        <?php else: ?>
            <table class="tabla-citas" style="width:100%">
                <thead>
                    <tr>
                        <th style="width:22%">Médico</th>
                        <th style="width:18%">Especialidad</th>
                        <th style="text-align:center;width:8%">Total</th>
                        <th style="text-align:center;width:10%">Programadas</th>
                        <th style="text-align:center;width:10%">Completadas</th>
                        <th style="text-align:center;width:10%">Canceladas</th>
                        <th style="text-align:center;width:10%">Pendientes</th>
                        <th style="width:22%">Carga</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $colores = ['#0a6e5c','#12907a','#2ec4a5','#c8903a','#d95f5f','#5a9e8f'];
                    foreach ($citasMedico as $i => $m):
                        $pct   = $maxCitas > 0 ? round((int)$m['total'] / $maxCitas * 100) : 0;
                        $color = $colores[$i % count($colores)];
                        $ini   = strtoupper(substr($m['nombre'],0,1).substr($m['apellido'],0,1));
                    ?>

                    
                    <tr>
                        <td>
                            <div class="paciente-cell">
                                <div class="mini-avatar" style="background:<?= $color ?>"><?= $ini ?></div>
                                Dr. <?= htmlspecialchars($m['nombre'].' '.$m['apellido']) ?>
                            </div>
                        </td>
                        <td style="font-size:.84rem"><?= htmlspecialchars($m['especialidad'] ?? '—') ?></td>
                        <td style="text-align:center;font-weight:700;font-size:1rem"><?= (int)$m['total'] ?></td>
                        <td style="text-align:center">
                            <span class="badge-programada"><?= (int)$m['programadas'] ?></span>
                        </td>
                        <td style="text-align:center">
                            <span class="badge-completada"><?= (int)$m['completadas'] ?></span>
                        </td>
                        <td style="text-align:center">
                            <span class="badge-cancelada"><?= (int)$m['canceladas'] ?></span>
                        </td>
                        <td style="text-align:center">
                            <span class="badge-pendiente"><?= (int)$m['pendientes'] ?></span>
                        </td>
                        <td>
                            <div class="barra-carga">
                                <div class="barra-carga__relleno" style="width:<?= $pct ?>%"></div>
                            </div>
                            <span style="font-size:.72rem;color:var(--gris-suave)"><?= $pct ?>%</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</main>
