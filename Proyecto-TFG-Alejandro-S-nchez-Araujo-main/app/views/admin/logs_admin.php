<?php

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/AdminModel.php';

$modelo = new AdminModel($pdo);
$todos  = $modelo->obtenerLogs(500);

// Paginación
$porPagina = 20;
$pagina    = max(1, (int)($_GET['p'] ?? 1));
$total     = count($todos);
$totalPag  = max(1, (int)ceil($total / $porPagina));
$pagina    = min($pagina, $totalPag);
$logs      = array_slice($todos, ($pagina - 1) * $porPagina, $porPagina);
?>

<link rel="stylesheet" href="/assets/css/panel_medico.css">
<link rel="stylesheet" href="/assets/css/citas_medico.css">
<link rel="stylesheet" href="/assets/css/panel_admin.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<?php
$pagina_activa_admin = 'logs';
require_once __DIR__ . '/../../includes/sidebar_admin.php';
?>

<main class="panel-principal">

    <div class="cabecera">
        <h1>Log de accesos</h1>
        <div class="cabecera__fecha">
            <i class="bi bi-calendar3"></i>
            <span><?= date('d/m/Y') ?></span>
        </div>
    </div>

    <div class="tabla-card">
        <h5 style="margin-bottom:16px">Últimos <?= $total ?> accesos registrados</h5>

        <?php if (empty($logs)): ?>
            <div class="sin-resultados">
                <i class="bi bi-journal-x"></i> Sin accesos registrados.
            </div>
        <?php else: ?>
            <table class="tabla-citas tabla-log" style="width:100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>IP</th>
                        <th>Fecha y hora</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td style="color:var(--gris-suave);font-size:.78rem"><?= $log['id_log'] ?></td>
                        <td><strong><?= htmlspecialchars($log['username']) ?></strong></td>
                        <td>
                            <span class="badge-rol badge-rol-<?= $log['rol'] ?>">
                                <?= ucfirst($log['rol']) ?>
                            </span>
                        </td>
                        <td class="ip-cell"><?= htmlspecialchars($log['ip'] ?? '—') ?></td>
                        <td style="font-size:.84rem"><?= date('d/m/Y H:i:s', strtotime($log['fecha'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>




            
            <!-- Paginación: enlaces numéricos la página activa se resalta en verde -->
            <?php if ($totalPag > 1): ?>
            <div style="display:flex;gap:6px;justify-content:center;padding:16px 0;flex-wrap:wrap">
                <?php for ($i = 1; $i <= $totalPag; $i++): ?>
                    <a href="?p=<?= $i ?>"
                       style="padding:5px 12px;border-radius:8px;font-size:.82rem;text-decoration:none;
                              background:<?= $i === $pagina ? 'var(--verde)' : '#f0f4f3' ?>;
                              color:<?= $i === $pagina ? '#fff' : 'var(--gris-texto)' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

</main>
