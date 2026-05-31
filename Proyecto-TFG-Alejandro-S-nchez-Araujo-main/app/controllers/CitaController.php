<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'medico') {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/CitaModel.php';

$modelo = new CitaModel($pdo);
$accion = $_GET['accion'] ?? '';

// GUARDAR LA NUEVA CITA
if ($accion === 'guardar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'id_paciente' => $_POST['id_paciente'],
        'id_empleado' => $_SESSION['id_empleado'],
        'fecha_cita'  => $_POST['fecha_cita'],
        'hora_cita'   => $_POST['hora_cita']
    ];
    $modelo->crear($datos);
    header('Location: /medico/citas.php');
    exit;
}

// ACTUALIZAR EDITAR LA CITA
if ($accion === 'actualizar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id    = (int)($_POST['id_cita'] ?? 0);
    $datos = [
        'fecha_cita' => $_POST['fecha_cita'],
        'hora_cita'  => $_POST['hora_cita'],
        'estado'     => $_POST['estado']
    ];
    $modelo->actualizar($id, $datos);
    header('Location: /medico/citas.php');
    exit;
}

// ELIMINAR
if ($accion === 'eliminar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id_cita'] ?? 0);
    $modelo->eliminar($id);
    header('Location: /medico/citas.php');
    exit;
}

// COMPLETAR
if ($accion === 'completar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id_cita'] ?? 0);
    $modelo->completarCitaMedico($id, $_SESSION['id_empleado']);
    header('Location: /medico/citas.php?ok=completada');
    exit;
}

// Si llega aquí sin acción correcta, redirigir
header('Location: /medico/citas.php');
exit;
