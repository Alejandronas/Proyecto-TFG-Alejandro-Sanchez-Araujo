<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'paciente') {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/PacienteModel.php';
require_once __DIR__ . '/../models/CitaModel.php';

$modeloPaciente = new PacienteModel($pdo);
$modeloCita     = new CitaModel($pdo);
$accion         = $_GET['accion'] ?? '';
$id_paciente    = $_SESSION['id_paciente'];

// ACTUALIZAR DATOS PERSONALES
if ($accion === 'actualizar_perfil' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'telefono'  => trim($_POST['telefono']  ?? ''),
        'direccion' => trim($_POST['direccion'] ?? ''),
        'email'     => trim($_POST['email']     ?? '')
    ];
    $modeloPaciente->actualizarPerfil($id_paciente, $datos);
    header('Location: /paciente/perfil_paciente.php?ok=datos');
    exit;
}

// ACTUALIZAR CONTRASEÑA
if ($accion === 'actualizar_password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $actual   = $_POST['password_actual']   ?? '';
    $nueva    = $_POST['password_nueva']    ?? '';
    $confirma = $_POST['password_confirma'] ?? '';

    if ($nueva !== $confirma) {
        header('Location: /paciente/perfil_paciente.php?error=no_coinciden');
        exit;
    }
    if (strlen($nueva) < 6) {
        header('Location: /paciente/perfil_paciente.php?error=muy_corta');
        exit;
    }
    if (!$modeloPaciente->verificarPasswordPaciente($id_paciente, $actual)) {
        header('Location: /paciente/perfil_paciente.php?error=incorrecta');
        exit;
    }
    $modeloPaciente->actualizarPasswordPaciente($id_paciente, $nueva);
    header('Location: /paciente/perfil_paciente.php?ok=password');
    exit;
}

// SOLICITAR CITA
if ($accion === 'solicitar_cita' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'id_paciente'     => $id_paciente,
        'fecha_cita'      => $_POST['fecha_cita'],
        'hora_cita'       => $_POST['hora_cita'],
        'motivo'          => trim($_POST['motivo'] ?? ''),
        'id_especialidad' => !empty($_POST['id_especialidad']) ? (int)$_POST['id_especialidad'] : null
    ];
    $modeloCita->solicitarCita($datos);
    header('Location: /paciente/pedir_cita.php?ok=1');
    exit;
}

// CANCELAR CITA
if ($accion === 'cancelar_cita' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cita = (int)($_POST['id_cita'] ?? 0);
    // Verificar que la cita pertenece al paciente y está en estado cancelable
    $modeloCita->cancelarCitaPaciente($id_cita, $id_paciente);
    header('Location: /paciente/citas_paciente.php?ok=cancelada');
    exit;
}

header('Location: /panel.php');
exit;
