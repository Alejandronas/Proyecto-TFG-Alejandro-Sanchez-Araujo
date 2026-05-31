<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'medico') {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/PacienteModel.php';

$modelo = new PacienteModel($pdo);
$accion = $_GET['accion'] ?? '';

// BUSCAR paciente por teléfono o DNI (responde JSON)
if ($accion === 'buscar_paciente') {
    $q       = trim($_GET['q'] ?? '');
    $results = $q ? $modelo->buscarPorTelODni($q) : [];
    header('Content-Type: application/json');
    echo json_encode($results);
    exit;
}

// AÑADIR paciente al médico
if ($accion === 'asignar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $modelo->asignar($_SESSION['id_empleado'], (int) $_POST['id_paciente']);
    header('Location: /medico/pacientes.php');
    exit;
}

// ELIMINAR paciente del médico
if ($accion === 'desasignar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $modelo->desasignar($_SESSION['id_empleado'], (int) $_POST['id_paciente']);
    header('Location: /medico/pacientes.php');
    exit;
}

// AÑADIR entrada al historial
if ($accion === 'historial_guardar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'id_paciente'    => (int) $_POST['id_paciente'],
        'id_empleado'    => $_SESSION['id_empleado'],
        'fecha'          => $_POST['fecha'],
        'motivo_consulta'=> $_POST['motivo_consulta'] ?? '',
        'tipo_consulta'  => $_POST['tipo_consulta']   ?? 'presencial',
        'diagnostico'    => $_POST['diagnostico'],
        'tratamiento'    => $_POST['tratamiento']      ?? '',
        'notas'          => $_POST['notas']            ?? ''
    ];
    $modelo->agregarHistorial($datos);
    header('Location: /medico/pacientes.php');
    exit;
}

// CREAR nuevo paciente y asignarlo al médico
if ($accion === 'crear_y_asignar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'nombre'               => $_POST['nombre'],
        'apellido'             => $_POST['apellido'],
        'fecha_nacimiento'     => $_POST['fecha_nacimiento']     ?? '',
        'genero'               => $_POST['genero']               ?? '',
        'direccion'            => $_POST['direccion']            ?? '',
        'telefono'             => $_POST['telefono']             ?? '',
        'email'                => $_POST['email']                ?? '',
        'dni'                  => $_POST['dni']                  ?? '',
        'num_seguridad_social' => $_POST['num_seguridad_social'] ?? ''
    ];
    $id_paciente = $modelo->crear($datos);
    $modelo->asignar($_SESSION['id_empleado'], $id_paciente);
    header('Location: /medico/pacientes.php');
    exit;
}

// Redirigir si no hay acción válida
header('Location: /medico/pacientes.php');
exit;
