<?php

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/AdminModel.php';

$modelo        = new AdminModel($pdo);
$empleados     = $modelo->obtenerEmpleados();
$especialidades = $modelo->obtenerEspecialidades();
$departamentos  = $modelo->obtenerDepartamentos();

$ok  = $_GET['ok']  ?? '';
$err = $_GET['err'] ?? '';

// Paginación
$porPagina = 15;
$pagina    = max(1, (int)($_GET['p'] ?? 1));
$total     = count($empleados);
$totalPag  = max(1, (int)ceil($total / $porPagina));
$pagina    = min($pagina, $totalPag);
$empleadosPag = array_slice($empleados, ($pagina - 1) * $porPagina, $porPagina);

$roles = ['medico', 'recepcionista', 'administrador'];
?>

<link rel="stylesheet" href="/assets/css/panel_medico.css">
<link rel="stylesheet" href="/assets/css/citas_medico.css">
<link rel="stylesheet" href="/assets/css/panel_admin.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<?php
$pagina_activa_admin = 'empleados';
require_once __DIR__ . '/../../includes/sidebar_admin.php';
?>

<main class="panel-principal">

    <div class="cabecera">
        <h1>Empleados</h1>
        <div class="cabecera__fecha">
            <i class="bi bi-calendar3"></i>
            <span><?= date('d/m/Y') ?></span>
        </div>
    </div>

    <?php if ($ok === 'creado'): ?>
        <div class="conf-alerta conf-alerta--ok" style="background:#ddf6f1;color:#0a6e5c;border:1px solid #b2e8de;border-radius:12px;padding:12px 18px;font-size:.875rem;margin-bottom:20px;display:flex;align-items:center;gap:10px;">
            <i class="bi bi-check-circle-fill"></i> Empleado creado correctamente.
        </div>
    <?php elseif ($ok === 'actualizado'): ?>
        <div class="conf-alerta conf-alerta--ok" style="background:#ddf6f1;color:#0a6e5c;border:1px solid #b2e8de;border-radius:12px;padding:12px 18px;font-size:.875rem;margin-bottom:20px;display:flex;align-items:center;gap:10px;">
            <i class="bi bi-check-circle-fill"></i> Empleado actualizado.
        </div>
    <?php elseif ($ok === 'estado'): ?>
        <div class="conf-alerta conf-alerta--ok" style="background:#ddf6f1;color:#0a6e5c;border:1px solid #b2e8de;border-radius:12px;padding:12px 18px;font-size:.875rem;margin-bottom:20px;display:flex;align-items:center;gap:10px;">
            <i class="bi bi-check-circle-fill"></i> Estado del empleado actualizado.
        </div>
    <?php elseif ($ok === 'password'): ?>
        <div style="background:#ddf6f1;color:#0a6e5c;border:1px solid #b2e8de;border-radius:12px;padding:12px 18px;font-size:.875rem;margin-bottom:20px;display:flex;align-items:center;gap:10px;">
            <i class="bi bi-check-circle-fill"></i> Contraseña restablecida.
        </div>
    <?php elseif ($err === 'password'): ?>
        <div style="background:#fdeaea;color:#d95f5f;border:1px solid #f5c2c2;border-radius:12px;padding:12px 18px;font-size:.875rem;margin-bottom:20px;display:flex;align-items:center;gap:10px;">
            <i class="bi bi-x-circle-fill"></i> La contraseña debe tener al menos 6 caracteres.
        </div>
    <?php endif; ?>

    <!-- Barra superior -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
        <span style="font-size:.85rem;color:var(--gris-suave)">Total: <?= $total ?> empleados</span>
        <button class="btn-nueva-cita" data-bs-toggle="modal" data-bs-target="#modalNuevoEmpleado">
            <i class="bi bi-plus-lg"></i> Nuevo empleado
        </button>
    </div>

    <!-- Tabla de empleados cada fila tiene botones de editar reset pwd y toggle activo/inactivo -->
    <div class="tabla-card">
        <table class="tabla-citas">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Rol</th>
                    <th>Especialidad</th>
                    <th>Departamento</th>
                    <th>Usuario</th>
                    <th style="text-align:center">Estado</th>
                    <th style="text-align:center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($empleadosPag as $e): ?>
                <tr style="<?= !$e['activo'] ? 'opacity:.55' : '' ?>">
                    <td>
                        <strong><?= htmlspecialchars($e['nombre'] . ' ' . $e['apellido']) ?></strong>
                        <?php if ($e['fecha_contratacion']): ?>
                            <div style="font-size:.75rem;color:var(--gris-suave)">Desde <?= date('d/m/Y', strtotime($e['fecha_contratacion'])) ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge-rol badge-rol-<?= $e['rol'] ?>"><?= ucfirst($e['rol']) ?></span>
                    </td>
                    <td style="font-size:.84rem"><?= htmlspecialchars($e['especialidad'] ?? '—') ?></td>
                    <td style="font-size:.84rem"><?= htmlspecialchars($e['departamento'] ?? '—') ?></td>
                    <td style="font-size:.84rem;font-family:monospace"><?= htmlspecialchars($e['username'] ?? '—') ?></td>
                    <td style="text-align:center">
                        <span class="badge-<?= $e['activo'] ? 'activo' : 'inactivo' ?>">
                            <?= $e['activo'] ? 'Activo' : 'Inactivo' ?>
                        </span>
                    </td>
                    <td style="text-align:center">
                        <div style="display:flex;gap:6px;justify-content:center;flex-wrap:wrap">
                            <!-- Editar -->
                            <button class="btn-accion btn-editar"
                                onclick="abrirEditar(<?= htmlspecialchars(json_encode($e)) ?>)"
                                title="Editar">
                                <i class="bi bi-pencil-fill"></i>
                            </button>
                            <!-- Reset contraseña -->
                            <button class="btn-reset-pwd"
                                onclick="abrirReset(<?= $e['id_empleado'] ?>, '<?= htmlspecialchars($e['nombre'].' '.$e['apellido']) ?>')"
                                title="Restablecer contraseña">
                                <i class="bi bi-key-fill"></i>
                            </button>
                            <!-- Toggle activo -->
                            <?php if ($e['activo']): ?>
                                <form method="POST" action="/controllers/AdminController.php?accion=toggle_activo"
                                      onsubmit="return confirm('¿Desactivar a <?= htmlspecialchars($e['nombre']) ?>?')">
                                    <input type="hidden" name="id_empleado" value="<?= $e['id_empleado'] ?>">
                                    <input type="hidden" name="activo" value="0">
                                    <button type="submit" class="btn-toggle-off" title="Desactivar">
                                        <i class="bi bi-slash-circle"></i>
                                    </button>
                                </form>
                            <?php else: ?>
                                <form method="POST" action="/controllers/AdminController.php?accion=toggle_activo">
                                    <input type="hidden" name="id_empleado" value="<?= $e['id_empleado'] ?>">
                                    <input type="hidden" name="activo" value="1">
                                    <button type="submit" class="btn-toggle-on" title="Activar">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
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
    </div>

</main>


<!-- MODAL para— NUEVO EMPLEADO -->
<div class="modal fade" id="modalNuevoEmpleado" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content cita-modal">
            <div class="modal-header cita-modal__header">
                <h5 class="modal-title"><i class="bi bi-person-plus-fill me-2"></i>Nuevo empleado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/controllers/AdminController.php?accion=crear_empleado">
                <div class="modal-body cita-modal__body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label cita-modal__label">Nombre</label>
                            <input type="text" name="nombre" class="form-control cita-modal__input" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label cita-modal__label">Apellido</label>
                            <input type="text" name="apellido" class="form-control cita-modal__input" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label cita-modal__label">Rol</label>
                            <select name="rol" class="form-select cita-modal__input" required>
                                <option value="">— Seleccionar —</option>
                                <?php foreach ($roles as $r): ?>
                                    <option value="<?= $r ?>"><?= ucfirst($r) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label cita-modal__label">Especialidad</label>
                            <select name="id_especialidad" class="form-select cita-modal__input">
                                <option value="">— Ninguna —</option>
                                <?php foreach ($especialidades as $esp): ?>
                                    <option value="<?= $esp['id_especialidad'] ?>"><?= htmlspecialchars($esp['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label cita-modal__label">Departamento</label>
                            <select name="id_departamento" class="form-select cita-modal__input">
                                <option value="">— Ninguno —</option>
                                <?php foreach ($departamentos as $d): ?>
                                    <option value="<?= $d['id_departamento'] ?>"><?= htmlspecialchars($d['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label cita-modal__label">Fecha de contratación</label>
                            <input type="date" name="fecha_contratacion" class="form-control cita-modal__input">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label cita-modal__label">Salario (€)</label>
                            <input type="number" name="salario" step="0.01" min="0" class="form-control cita-modal__input">
                        </div>
                        <div class="col-12"><hr style="border-color:var(--borde);margin:4px 0"></div>
                        <div class="col-md-6">
                            <label class="form-label cita-modal__label">Nombre de usuario</label>
                            <input type="text" name="username" class="form-control cita-modal__input"
                                   placeholder="p.ej. ana.garcia">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label cita-modal__label">Contraseña inicial</label>
                            <input type="password" name="password" class="form-control cita-modal__input"
                                   placeholder="Mínimo 6 caracteres">
                        </div>
                    </div>
                </div>
                <div class="modal-footer cita-modal__footer">
                    <button type="button" class="btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-guardar"><i class="bi bi-check-lg"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- MODAL para — EDITAR EMPLEADO -->
<div class="modal fade" id="modalEditarEmpleado" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content cita-modal">
            <div class="modal-header cita-modal__header">
                <h5 class="modal-title"><i class="bi bi-pencil-fill me-2"></i>Editar empleado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/controllers/AdminController.php?accion=actualizar_empleado">
                <div class="modal-body cita-modal__body">
                    <input type="hidden" name="id_empleado" id="edit-id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label cita-modal__label">Nombre</label>
                            <input type="text" name="nombre" id="edit-nombre" class="form-control cita-modal__input" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label cita-modal__label">Apellido</label>
                            <input type="text" name="apellido" id="edit-apellido" class="form-control cita-modal__input" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label cita-modal__label">Rol</label>
                            <select name="rol" id="edit-rol" class="form-select cita-modal__input" required>
                                <?php foreach ($roles as $r): ?>
                                    <option value="<?= $r ?>"><?= ucfirst($r) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label cita-modal__label">Especialidad</label>
                            <select name="id_especialidad" id="edit-especialidad" class="form-select cita-modal__input">
                                <option value="">— Ninguna —</option>
                                <?php foreach ($especialidades as $esp): ?>
                                    <option value="<?= $esp['id_especialidad'] ?>"><?= htmlspecialchars($esp['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label cita-modal__label">Departamento</label>
                            <select name="id_departamento" id="edit-departamento" class="form-select cita-modal__input">
                                <option value="">— Ninguno —</option>
                                <?php foreach ($departamentos as $d): ?>
                                    <option value="<?= $d['id_departamento'] ?>"><?= htmlspecialchars($d['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label cita-modal__label">Fecha de contratación</label>
                            <input type="date" name="fecha_contratacion" id="edit-fecha" class="form-control cita-modal__input">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label cita-modal__label">Salario (€)</label>
                            <input type="number" name="salario" id="edit-salario" step="0.01" min="0" class="form-control cita-modal__input">
                        </div>
                    </div>
                </div>
                <div class="modal-footer cita-modal__footer">
                    <button type="button" class="btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-guardar"><i class="bi bi-check-lg"></i> Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- MODAL para— RESET CONTRASEÑA -->
<div class="modal fade" id="modalResetPwd" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content cita-modal">
            <div class="modal-header cita-modal__header">
                <h5 class="modal-title"><i class="bi bi-key-fill me-2"></i>Restablecer contraseña</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/controllers/AdminController.php?accion=reset_password">
                <div class="modal-body cita-modal__body">
                    <input type="hidden" name="id_empleado" id="reset-id">
                    <p style="font-size:.88rem;margin-bottom:16px">
                        Establece una nueva contraseña para <strong id="reset-nombre"></strong>.
                    </p>
                    <label class="form-label cita-modal__label">Nueva contraseña</label>
                    <input type="password" name="nueva_password" class="form-control cita-modal__input"
                           minlength="6" placeholder="Mínimo 6 caracteres" required>
                </div>
                <div class="modal-footer cita-modal__footer">
                    <button type="button" class="btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-guardar"><i class="bi bi-check-lg"></i> Restablecer</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// abrirEditar: recibe el objeto empleado (serializado con json_encode en PHP),
// rellena los campos del modal de edición y lo muestra.
function abrirEditar(e) {
    document.getElementById('edit-id').value          = e.id_empleado;
    document.getElementById('edit-nombre').value      = e.nombre;
    document.getElementById('edit-apellido').value    = e.apellido;
    document.getElementById('edit-rol').value         = e.rol;
    document.getElementById('edit-especialidad').value= e.id_especialidad || '';
    document.getElementById('edit-departamento').value= e.id_departamento || '';
    document.getElementById('edit-fecha').value       = e.fecha_contratacion || '';
    document.getElementById('edit-salario').value     = e.salario || '';
    new bootstrap.Modal(document.getElementById('modalEditarEmpleado')).show();
}

// abrirReset: precarga el id y el nombre visible en el modal de reset de contraseña.
function abrirReset(id, nombre) {
    document.getElementById('reset-id').value     = id;
    document.getElementById('reset-nombre').textContent = nombre;
    new bootstrap.Modal(document.getElementById('modalResetPwd')).show();
}
</script>
