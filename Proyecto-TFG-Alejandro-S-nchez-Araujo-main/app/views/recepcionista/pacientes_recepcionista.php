<?php





require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/PacienteModel.php';

$modelo = new PacienteModel($pdo);
$buscar = trim($_GET['buscar'] ?? ''); // Término de búsqueda (filtra en PHP sobre el array completo)
$ok     = $_GET['ok'] ?? '';

// Obtener todos y filtrar por búsqueda en PHP
$todos = $modelo->obtenerTodos();
if ($buscar) {
    $todos = array_values(array_filter($todos, function($p) use ($buscar) {
        return stripos($p['nombre'].' '.$p['apellido'], $buscar) !== false
            || stripos($p['dni'] ?? '', $buscar) !== false
            || stripos($p['telefono'] ?? '', $buscar) !== false;
    }));
} else {
    $todos = array_values($todos);
}

// Paginación
$porPagina    = 15;
$totalPacs    = count($todos);
$totalPags    = max(1, (int)ceil($totalPacs / $porPagina));
$paginaActual = max(1, min((int)($_GET['pag'] ?? 1), $totalPags));
$offset       = ($paginaActual - 1) * $porPagina;
$pacsPagina   = array_slice($todos, $offset, $porPagina);

$colores = ['#0a6e5c','#12907a','#2ec4a5','#c8903a','#d95f5f','#5a9e8f'];
?>

<link rel="stylesheet" href="/assets/css/panel_medico.css">
<link rel="stylesheet" href="/assets/css/pacientes_medico.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<?php
$pagina_activa_rec = 'pacientes';
require_once __DIR__ . '/../../includes/sidebar_recepcionista.php';
?>

<main class="panel-principal">

    <div class="cabecera">
        <h1>Pacientes</h1>
        <div class="cabecera__fecha">
            <i class="bi bi-calendar3"></i>
            <span><?= date('d/m/Y') ?></span>
        </div>
    </div>

    <?php if ($ok === 'creado'): ?>
        <div style="background:#ddf6f1;color:#0a6e5c;border:1px solid #b2e8de;border-radius:12px;padding:12px 18px;font-size:.875rem;margin-bottom:20px;display:flex;align-items:center;gap:10px;">
            <i class="bi bi-check-circle-fill"></i> Paciente creado correctamente.
        </div>
    <?php endif; ?>

    <div class="tabla-card">
        <div class="pac-header">
            <!-- Buscador -->
            <form method="GET" action="/recepcionista/pacientes_recepcionista.php" style="display:flex;gap:8px;align-items:center">
                <input type="text" name="buscar" value="<?= htmlspecialchars($buscar) ?>"
                       class="form-control" style="border-radius:10px;border:1px solid var(--borde);font-size:.875rem;padding:7px 14px;width:240px"
                       placeholder="Nombre, DNI o teléfono…">
                <button type="submit" class="btn-filtrar" style="margin:0"><i class="bi bi-search"></i></button>
                <?php if ($buscar): ?><a href="/recepcionista/pacientes_recepcionista.php" class="btn-limpiar" style="margin:0">Limpiar</a><?php endif; ?>
            </form>
            <h5 style="margin:0"><?= $totalPacs ?> pacientes</h5>
            <button type="button" class="btn-nueva-cita" data-bs-toggle="modal" data-bs-target="#modalNuevoPaciente">
                <i class="bi bi-person-plus"></i> Nuevo Paciente
            </button>
        </div>

        <?php if (empty($todos)): ?>
            <div class="sin-resultados">
                <i class="bi bi-people"></i>
                No se encontraron pacientes.
            </div>
        <?php else: ?>
            <table class="tabla-citas">
                <thead>
                    <tr>
                        <th>Paciente</th>
                        <th>DNI</th>
                        <th>Teléfono</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pacsPagina as $i => $p):
                        $iniciales = strtoupper(substr($p['nombre'],0,1).substr($p['apellido'],0,1));
                        $color     = $colores[($offset + $i) % count($colores)];
                    ?>
                    <tr>
                        <td>
                            <div class="paciente-cell">
                                <div class="mini-avatar" style="background:<?= $color ?>"><?= $iniciales ?></div>
                                <?= htmlspecialchars($p['nombre'].' '.$p['apellido']) ?>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($p['dni'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($p['telefono'] ?? '—') ?></td>
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


<!-- MODAL — NUEVO PACIENTE -->
<div class="modal fade" id="modalNuevoPaciente" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content cita-modal">
            <div class="modal-header cita-modal__header">
                <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Nuevo Paciente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/controllers/RecepcionistaController.php?accion=crear_paciente">
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
                </div>
                <div class="modal-footer cita-modal__footer">
                    <button type="button" class="btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-guardar"><i class="bi bi-person-check"></i> Crear paciente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
