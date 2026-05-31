<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'recepcionista') {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/CitaModel.php';
require_once __DIR__ . '/../models/PacienteModel.php';

$modeloCita     = new CitaModel($pdo);
$modeloPaciente = new PacienteModel($pdo);
$accion         = $_GET['accion'] ?? '';

// ASIGNAR MÉDICO
if ($accion === 'asignar_medico' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cita     = (int)($_POST['id_cita']     ?? 0);
    $id_empleado = (int)($_POST['id_empleado'] ?? 0);
    $modeloCita->asignarMedico($id_cita, $id_empleado);
    // Si la cita estaba pendiente, pasarla a programada automáticamente
    $modeloCita->programarSiPendiente($id_cita);
    header('Location: /panel.php?ok=asignado');
    exit;
}

// CREAR CITA
if ($accion === 'guardar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'id_paciente' => (int)($_POST['id_paciente'] ?? 0),
        'id_empleado' => $_POST['id_empleado'] !== '' ? (int)$_POST['id_empleado'] : null,
        'fecha_cita'  => $_POST['fecha_cita'],
        'hora_cita'   => $_POST['hora_cita'],
        'motivo'      => trim($_POST['motivo'] ?? '')
    ];
    $modeloCita->crearRecepcionista($datos);
    header('Location: /recepcionista/citas_recepcionista.php?ok=creada');
    exit;
}

// ACTUALIZAR CITA
if ($accion === 'actualizar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id    = (int)($_POST['id_cita'] ?? 0);
    $datos = [
        'id_empleado' => ($_POST['id_empleado'] ?? '') !== '' ? (int)$_POST['id_empleado'] : null,
        'fecha_cita'  => $_POST['fecha_cita'],
        'hora_cita'   => $_POST['hora_cita'],
        'estado'      => $_POST['estado'],
        'motivo'      => trim($_POST['motivo'] ?? '')
    ];
    $modeloCita->actualizarCompleta($id, $datos);
    header('Location: /recepcionista/citas_recepcionista.php?ok=actualizada');
    exit;
}

// ELIMINAR CITA
if ($accion === 'eliminar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $modeloCita->eliminar((int)($_POST['id_cita'] ?? 0));
    header('Location: /recepcionista/citas_recepcionista.php?ok=eliminada');
    exit;
}

// CREAR PACIENTE
if ($accion === 'crear_paciente' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'nombre'              => trim($_POST['nombre']),
        'apellido'            => trim($_POST['apellido']),
        'fecha_nacimiento'    => $_POST['fecha_nacimiento']     ?? null,
        'genero'              => $_POST['genero']               ?? null,
        'telefono'            => trim($_POST['telefono']        ?? ''),
        'email'               => trim($_POST['email']           ?? ''),
        'direccion'           => trim($_POST['direccion']       ?? ''),
        'dni'                 => trim($_POST['dni']             ?? ''),
        'num_seguridad_social'=> trim($_POST['num_seguridad_social'] ?? '')
    ];
    $modeloPaciente->crear($datos);
    header('Location: /recepcionista/pacientes_recepcionista.php?ok=creado');
    exit;
}

header('Location: /panel.php');
exit;
