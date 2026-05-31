<?php

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/AdminModel.php';

$modelo      = new AdminModel($pdo);
$resumen     = $modelo->obtenerResumen();      // Contadores globales del sistema
$logs        = $modelo->obtenerLogs(10);        // Últimos 10 accesos para el widget de actividad
$citasMedico = $modelo->obtenerCitasPorMedico();






// Top 5 médicos con más citas para las barras de carga del dashboard
$top5     = array_slice($citasMedico, 0, 5);
$maxCitas = $top5 ? max(1, max(array_column($top5, 'total'))) : 1;
?>

<link rel="stylesheet" href="/assets/css/panel_medico.css">
<link rel="stylesheet" href="/assets/css/citas_medico.css">
<link rel="stylesheet" href="/assets/css/panel_admin.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<?php
$pagina_activa_admin = 'dashboard';
require_once __DIR__ . '/../../includes/sidebar_admin.php';
?>

<main class="panel-principal">

    <div class="cabecera">
        <h1>Dashboard Admin</h1>
        <div class="cabecera__fecha">
            <i class="bi bi-calendar3"></i>
            <span><?= date('d/m/Y') ?></span>
        </div>
    </div>

    <!-- Banner -->
    <div class="banner-bienvenida">
        <p class="banner-bienvenida__subtitulo">Panel de administración</p>
        <h2><?= htmlspecialchars($_SESSION['nombre']) ?> <i class="bi bi-emoji-smile"></i></h2>
        <p>Gestiona empleados, revisa el log de accesos y monitoriza la actividad de la clínica.</p>
        <a href="/admin/empleados_admin.php" class="boton-blanco">
            <i class="bi bi-person-badge-fill"></i> Gestionar empleados
        </a>
    </div>

    <!-- Tarjetas de estadísticas -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-2">
            <div class="stat-admin">
                <div class="stat-admin__icono stat-admin__icono--verde"><i class="bi bi-person-badge-fill"></i></div>
                <div>
                    <div class="stat-admin__numero"><?= $resumen['total_empleados'] ?></div>
                    <div class="stat-admin__etiqueta">Empleados activos</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-2">
            <div class="stat-admin">
                <div class="stat-admin__icono stat-admin__icono--azul"><i class="bi bi-heart-pulse-fill"></i></div>
                <div>
                    <div class="stat-admin__numero"><?= $resumen['total_medicos'] ?></div>
                    <div class="stat-admin__etiqueta">Médicos activos</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-2">
            <div class="stat-admin">
                <div class="stat-admin__icono stat-admin__icono--lila"><i class="bi bi-people-fill"></i></div>
                <div>
                    <div class="stat-admin__numero"><?= $resumen['total_pacientes'] ?></div>
                    <div class="stat-admin__etiqueta">Pacientes</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-2">
            <div class="stat-admin">
                <div class="stat-admin__icono stat-admin__icono--naranja"><i class="bi bi-calendar2-check"></i></div>
                <div>
                    <div class="stat-admin__numero"><?= $resumen['citas_hoy'] ?></div>
                    <div class="stat-admin__etiqueta">Citas hoy</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-2">
            <div class="stat-admin">
                <div class="stat-admin__icono stat-admin__icono--rojo"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <div class="stat-admin__numero"><?= $resumen['citas_pendientes'] ?></div>
                    <div class="stat-admin__etiqueta">Citas pendientes</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-2">
            <div class="stat-admin">
                <div class="stat-admin__icono stat-admin__icono--gris"><i class="bi bi-journal-text"></i></div>
                <div>
                    <div class="stat-admin__numero"><?= $resumen['accesos_hoy'] ?></div>
                    <div class="stat-admin__etiqueta">Accesos hoy</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top médicos y yltimos accesos -->
    <div class="row g-3">
        <!-- Top 5 médicos -->
        <div class="col-12 col-xl-6">
            <div class="titulo-seccion">Carga por médico (top 5)</div>
            <div class="tabla-card" style="padding:20px 24px">
                <?php if (empty($top5)): ?>
                    <p style="color:var(--gris-suave);text-align:center;padding:20px 0">Sin datos.</p>
                <?php else: ?>
                    <?php foreach ($top5 as $m):
                        $pct = $maxCitas > 0 ? round($m['total'] / $maxCitas * 100) : 0;
                    ?>
                    <div style="margin-bottom:14px">
                        <div style="display:flex;justify-content:space-between;font-size:.84rem;margin-bottom:4px">
                            <span><strong>Dr. <?= htmlspecialchars($m['nombre'].' '.$m['apellido']) ?></strong>
                                <span style="color:var(--gris-suave);font-size:.78rem"> — <?= htmlspecialchars($m['especialidad'] ?? '—') ?></span>
                            </span>
                            <span style="font-weight:700"><?= $m['total'] ?></span>
                        </div>
                        <div class="barra-carga">
                            <div class="barra-carga__relleno" style="width:<?= $pct ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <a href="/admin/citas_admin.php" style="font-size:.8rem;color:var(--verde);text-decoration:none">
                        Ver todos los médicos →
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Últimos accesos -->
        <div class="col-12 col-xl-6">
            <div class="titulo-seccion">Últimos accesos</div>
            <div class="tabla-card" style="padding:0">
                <?php if (empty($logs)): ?>
                    <p style="color:var(--gris-suave);text-align:center;padding:20px">Sin accesos registrados.</p>
                <?php else: ?>
                    <table class="tabla-citas tabla-log" style="width:100%">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th>IP</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($log['username']) ?></strong></td>
                                <td>
                                    <span class="badge-rol badge-rol-<?= $log['rol'] ?>">
                                        <?= ucfirst($log['rol']) ?>
                                    </span>
                                </td>
                                <td class="ip-cell"><?= htmlspecialchars($log['ip']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($log['fecha'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div style="padding:10px 20px;font-size:.8rem">
                        <a href="/admin/logs_admin.php" style="color:var(--verde);text-decoration:none">Ver log completo →</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</main>
