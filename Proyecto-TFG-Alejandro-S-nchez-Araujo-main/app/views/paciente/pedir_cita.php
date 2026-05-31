<?php







$ok = $_GET['ok'] ?? '';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/CitaModel.php';
$modeloCita     = new CitaModel($pdo);
$especialidades = $modeloCita->obtenerEspecialidades(); 
?>

<link rel="stylesheet" href="/assets/css/panel_medico.css">
<link rel="stylesheet" href="/assets/css/panel_paciente.css">
<link rel="stylesheet" href="/assets/css/configuracion_medico.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<?php
$pagina_activa_pac = 'pedir_cita';
require_once __DIR__ . '/../../includes/sidebar_paciente.php';
?>

<main class="panel-principal">

    <div class="cabecera">
        <h1>Pedir Cita</h1>
        <div class="cabecera__fecha">
            <i class="bi bi-calendar3"></i>
            <span><?= date('d/m/Y') ?></span>
        </div>
    </div>

    <?php if ($ok): ?>
        <div class="conf-alerta conf-alerta--ok" style="margin-bottom:20px">
            <i class="bi bi-check-circle-fill"></i> Cita solicitada correctamente. El personal de recepción te asignará un médico en breve.
        </div>
    <?php endif; ?>

    <div class="conf-grid">
        <div class="conf-card">
            <h5><i class="bi bi-calendar-plus me-2"></i>Nueva solicitud de cita</h5>
            <p style="font-size:.875rem;color:var(--gris-suave);margin-bottom:24px;">
                Indica la fecha, hora y motivo de tu consulta. El médico será asignado por el personal de recepción.
            </p>

            <form method="POST" action="/controllers/PerfilPacienteController.php?accion=solicitar_cita">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="conf-label">Fecha</label>
                        <input type="date" name="fecha_cita" class="conf-input"
                               min="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="conf-label">Hora</label>
                        <input type="time" name="hora_cita" class="conf-input" required>
                    </div>
                    <div class="col-12">
                        <label class="conf-label">Especialidad</label>
                        <select name="id_especialidad" class="conf-input" required>
                            <option value="">Selecciona una especialidad…</option>
                            <?php foreach ($especialidades as $esp): ?>
                                <option value="<?= $esp['id_especialidad'] ?>">
                                    <?= htmlspecialchars($esp['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="conf-label">Motivo de la consulta</label>
                        <textarea name="motivo" class="conf-input" rows="4"
                                  placeholder="Describe brevemente el motivo de tu visita…"
                                  style="resize:vertical;" required></textarea>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="conf-btn-guardar">
                        <i class="bi bi-send"></i> Enviar solicitud
                    </button>
                </div>
            </form>
        </div>
    </div>

</main>
