<?php




require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/PacienteModel.php';

$modelo   = new PacienteModel($pdo);
$paciente = $modelo->obtenerPaciente($_SESSION['id_paciente']);

$ok    = $_GET['ok']    ?? '';
$error = $_GET['error'] ?? '';
?>



<link rel="stylesheet" href="/assets/css/panel_medico.css">
<link rel="stylesheet" href="/assets/css/panel_paciente.css">
<link rel="stylesheet" href="/assets/css/configuracion_medico.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<?php
$pagina_activa_pac = 'perfil';
require_once __DIR__ . '/../../includes/sidebar_paciente.php';
?>

<main class="panel-principal">

    <div class="cabecera">
        <h1>Mi Perfil</h1>
        <div class="cabecera__fecha">
            <i class="bi bi-calendar3"></i>
            <span><?= date('d/m/Y') ?></span>
        </div>
    </div>

    <!-- Alertas -->
    <?php if ($ok === 'datos'): ?>
        <div class="conf-alerta conf-alerta--ok">
            <i class="bi bi-check-circle-fill"></i> Datos actualizados correctamente.
        </div>
    <?php elseif ($ok === 'password'): ?>
        <div class="conf-alerta conf-alerta--ok">
            <i class="bi bi-check-circle-fill"></i> Contraseña actualizada correctamente.
        </div>
    <?php elseif ($error === 'no_coinciden'): ?>
        <div class="conf-alerta conf-alerta--error">
            <i class="bi bi-exclamation-circle-fill"></i> Las contraseñas nuevas no coinciden.
        </div>
    <?php elseif ($error === 'muy_corta'): ?>
        <div class="conf-alerta conf-alerta--error">
            <i class="bi bi-exclamation-circle-fill"></i> La contraseña debe tener al menos 6 caracteres.
        </div>
    <?php elseif ($error === 'incorrecta'): ?>
        <div class="conf-alerta conf-alerta--error">
            <i class="bi bi-exclamation-circle-fill"></i> La contraseña actual no es correcta.
        </div>
    <?php endif; ?>

    <div class="conf-grid">

        <!-- Datos personales -->
        <div class="conf-card">
            <h5><i class="bi bi-person-circle me-2"></i>Datos Personales</h5>
            <form method="POST" action="/controllers/PerfilPacienteController.php?accion=actualizar_perfil">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="conf-label">Nombre</label>
                        <input type="text" class="conf-input conf-input--readonly"
                               value="<?= htmlspecialchars($paciente['nombre'] ?? '') ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="conf-label">Apellido</label>
                        <input type="text" class="conf-input conf-input--readonly"
                               value="<?= htmlspecialchars($paciente['apellido'] ?? '') ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="conf-label">Email</label>
                        <input type="email" name="email" class="conf-input"
                               value="<?= htmlspecialchars($paciente['email'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="conf-label">Teléfono</label>
                        <input type="text" name="telefono" class="conf-input"
                               value="<?= htmlspecialchars($paciente['telefono'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="conf-label">Dirección</label>
                        <input type="text" name="direccion" class="conf-input"
                               value="<?= htmlspecialchars($paciente['direccion'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="conf-label">DNI</label>
                        <input type="text" class="conf-input conf-input--readonly"
                               value="<?= htmlspecialchars($paciente['dni'] ?? '—') ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="conf-label">Nº Seguridad Social</label>
                        <input type="text" class="conf-input conf-input--readonly"
                               value="<?= htmlspecialchars($paciente['num_seguridad_social'] ?? '—') ?>" readonly>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="conf-btn-guardar">
                        <i class="bi bi-floppy2"></i> Guardar cambios
                    </button>
                </div>
            </form>
        </div>

        <!-- Cambiar contraseña -->
        <div class="conf-card">
            <h5><i class="bi bi-shield-lock me-2"></i>Cambiar Contraseña</h5>
            <form method="POST" action="/controllers/PerfilPacienteController.php?accion=actualizar_password">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="conf-label">Contraseña actual</label>
                        <input type="password" name="password_actual" class="conf-input" required>
                    </div>
                    <div class="col-md-4">
                        <label class="conf-label">Nueva contraseña</label>
                        <input type="password" name="password_nueva" class="conf-input" required>
                    </div>
                    <div class="col-md-4">
                        <label class="conf-label">Confirmar contraseña</label>
                        <input type="password" name="password_confirma" class="conf-input" required>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="conf-btn-guardar">
                        <i class="bi bi-key"></i> Actualizar contraseña
                    </button>
                </div>
            </form>
        </div>

    </div>

</main>
