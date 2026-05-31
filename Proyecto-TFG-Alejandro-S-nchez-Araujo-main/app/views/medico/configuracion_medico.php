<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/ConfiguracionModel.php';

$modelo         = new ConfiguracionModel($pdo);
$medico         = $modelo->obtenerMedico($_SESSION['id_empleado']);
$especialidades = $modelo->obtenerEspecialidades();

$ok    = $_GET['ok']    ?? '';
$error = $_GET['error'] ?? '';

$mensajesOk = [
    'datos'    => 'Datos actualizados correctamente.',
    'password' => 'Contraseña cambiada correctamente.'
];
$mensajesError = [
    'no_coinciden' => 'Las contraseñas nuevas no coinciden.',
    'muy_corta'    => 'La contraseña debe tener al menos 6 caracteres.',
    'incorrecta'   => 'La contraseña actual no es correcta.'
];
?>

<link rel="stylesheet" href="/assets/css/panel_medico.css">
<link rel="stylesheet" href="/assets/css/configuracion_medico.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<?php
$pagina_activa = 'configuracion';
require_once __DIR__ . '/../../includes/sidebar_medico.php';
?>

<main class="panel-principal">

    <div class="cabecera">
        <h1>Configuración</h1>
        <div class="cabecera__fecha">
            <i class="bi bi-calendar3"></i>
            <span><?= date('d/m/Y') ?></span>
        </div>
    </div>

    <!-- Alertas -->
    <?php if ($ok && isset($mensajesOk[$ok])): ?>
        <div class="conf-alerta conf-alerta--ok">
            <i class="bi bi-check-circle-fill"></i> <?= $mensajesOk[$ok] ?>
        </div>
    <?php endif; ?>
    <?php if ($error && isset($mensajesError[$error])): ?>
        <div class="conf-alerta conf-alerta--error">
            <i class="bi bi-exclamation-circle-fill"></i> <?= $mensajesError[$error] ?>
        </div>
    <?php endif; ?>

    <div class="conf-grid">

        <!-- Datos personales -->
        <div class="conf-card">
            <div class="conf-card__titulo">
                <i class="bi bi-person-fill"></i> Datos personales
            </div>
            <form method="POST" action="/controllers/ConfiguracionController.php?accion=actualizar_datos">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="conf-label">Nombre</label>
                        <input type="text" name="nombre" class="conf-input"
                               value="<?= htmlspecialchars($medico['nombre']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="conf-label">Apellido</label>
                        <input type="text" name="apellido" class="conf-input"
                               value="<?= htmlspecialchars($medico['apellido']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="conf-label">Usuario</label>
                        <input type="text" class="conf-input conf-input--readonly"
                               value="<?= htmlspecialchars($medico['username']) ?>" disabled>
                        <small class="conf-ayuda">El nombre de usuario no se puede cambiar.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="conf-label">Especialidad</label>
                        <select name="id_especialidad" class="conf-input">
                            <option value="">— Sin especialidad —</option>
                            <?php foreach ($especialidades as $e): ?>
                                <option value="<?= $e['id_especialidad'] ?>"
                                    <?= $medico['id_especialidad'] == $e['id_especialidad'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($e['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <button type="submit" class="conf-btn-guardar">
                    <i class="bi bi-check-lg"></i> Guardar cambios
                </button>
            </form>
        </div>

        <!-- Cambio de contraseña -->
        <div class="conf-card">
            <div class="conf-card__titulo">
                <i class="bi bi-shield-lock-fill"></i> Cambiar contraseña
            </div>
            <form method="POST" action="/controllers/ConfiguracionController.php?accion=actualizar_password">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="conf-label">Contraseña actual</label>
                        <input type="password" name="password_actual" class="conf-input" required>
                    </div>
                    <div class="col-md-4">
                        <label class="conf-label">Nueva contraseña</label>
                        <input type="password" name="password_nueva" class="conf-input"
                               placeholder="Mínimo 6 caracteres" required>
                    </div>
                    <div class="col-md-4">
                        <label class="conf-label">Confirmar nueva contraseña</label>
                        <input type="password" name="password_confirma" class="conf-input" required>
                    </div>
                </div>
                <button type="submit" class="conf-btn-guardar">
                    <i class="bi bi-lock-fill"></i> Cambiar contraseña
                </button>
            </form>
        </div>

    </div>

</main>
