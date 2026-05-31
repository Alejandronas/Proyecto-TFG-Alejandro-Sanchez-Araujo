<?php

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/AdminModel.php';

$modelo   = new AdminModel($pdo);
$pacientes = $modelo->obtenerPacientes();

// Paginación
$porPagina = 15;
$pagina    = max(1, (int)($_GET['p'] ?? 1));
$total     = count($pacientes);
$totalPag  = max(1, (int)ceil($total / $porPagina));
$pagina    = min($pagina, $totalPag);
$pacientesPag = array_slice($pacientes, ($pagina - 1) * $porPagina, $porPagina);
?>

<link rel="stylesheet" href="/assets/css/panel_medico.css">
<link rel="stylesheet" href="/assets/css/citas_medico.css">
<link rel="stylesheet" href="/assets/css/panel_admin.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<?php
$pagina_activa_admin = 'pacientes';
require_once __DIR__ . '/../../includes/sidebar_admin.php';
?>

<main class="panel-principal">

    <div class="cabecera">
        <h1>Pacientes</h1>
        <div class="cabecera__fecha">
            <i class="bi bi-calendar3"></i>
            <span><?= date('d/m/Y') ?></span>
        </div>
    </div>

    <!-- Barra de acciones -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
        <div style="font-size:.9rem;color:var(--gris-suave)">Total: <strong><?= $total ?></strong> pacientes registrados</div>
        <button type="button" class="btn-nueva-cita" data-bs-toggle="modal" data-bs-target="#modalNuevoPaciente">
            <i class="bi bi-person-plus"></i> Nuevo paciente
        </button>
    </div>

    <div class="tabla-card">
        <h5 style="margin-bottom:16px">Total: <?= $total ?> pacientes registrados</h5>

        <?php if (empty($pacientesPag)): ?>
            <div class="sin-resultados">
                <i class="bi bi-people"></i> No hay pacientes registrados.
            </div>
        <?php else: ?>
            <table class="tabla-citas">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>DNI</th>
                        <th>Fecha nacimiento</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Usuario</th>
                        <th style="text-align:center">Citas totales</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $colores = ['#0a6e5c','#12907a','#2ec4a5','#c8903a','#d95f5f','#5a9e8f'];
                    foreach ($pacientesPag as $i => $p):
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
                        <td style="font-family:monospace;font-size:.84rem"><?= htmlspecialchars($p['dni'] ?? '—') ?></td>
                        <td><?= $p['fecha_nacimiento'] ? date('d/m/Y', strtotime($p['fecha_nacimiento'])) : '—' ?></td>
                        <td style="font-size:.84rem"><?= htmlspecialchars($p['telefono'] ?? '—') ?></td>
                        <td style="font-size:.82rem;color:var(--gris-suave)"><?= htmlspecialchars($p['email'] ?? '—') ?></td>
                        <td style="font-family:monospace;font-size:.82rem"><?= htmlspecialchars($p['username'] ?? '—') ?></td>
                        <td style="text-align:center;font-weight:700"><?= (int)$p['total_citas'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Paginación -->
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

<!-- MODAL para— NUEVO PACIENTE: recoge datos básicos y los envía a AdminController -->
<!-- acción crear_paciente, que inserta en PACIENTE y crea el usuario en USUARIO -->
<div class="modal fade" id="modalNuevoPaciente" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content cita-modal">
            <div class="modal-header cita-modal__header">
                <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Nuevo Paciente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/controllers/AdminController.php?accion=crear_paciente">
                <div class="modal-body cita-modal__body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label cita-modal__label">Nombre *</label>
                            <input type="text" name="nombre" class="form-control cita-modal__input" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label cita-modal__label">Apellido *</label>
                            <input type="text" name="apellido" class="form-control cita-modal__input" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label cita-modal__label">DNI</label>
                            <input type="text" name="dni" class="form-control cita-modal__input" placeholder="12345678A">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label cita-modal__label">Teléfono</label>
                            <input type="text" name="telefono" class="form-control cita-modal__input" placeholder="600000000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label cita-modal__label">Email</label>
                            <input type="email" name="email" class="form-control cita-modal__input">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label cita-modal__label">Nº Seguridad Social</label>
                            <input type="text" name="num_seguridad_social" class="form-control cita-modal__input">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label cita-modal__label">Fecha nacimiento</label>
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
                            <label class="form-label cita-modal__label">Dirección</label>
                            <input type="text" name="direccion" class="form-control cita-modal__input">
                        </div>
                    </div>
                </div>
                <div class="modal-footer cita-modal__footer">
                    <button type="button" class="btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-guardar"><i class="bi bi-check-lg"></i> Crear paciente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
