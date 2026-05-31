<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'medico') {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/ConfiguracionModel.php';

$modelo = new ConfiguracionModel($pdo);
$accion = $_GET['accion'] ?? '';

// ACTUALIZAR DATOS PERSONALES
if ($accion === 'actualizar_datos' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'nombre'          => trim($_POST['nombre']),
        'apellido'        => trim($_POST['apellido']),
        'id_especialidad' => $_POST['id_especialidad'] ?? null
    ];
    $modelo->actualizarDatos($_SESSION['id_empleado'], $datos);

    // Actualizar el nombre en sesión para que el sidebar lo refleje
    $_SESSION['nombre'] = $datos['nombre'];

    header('Location: /configuracion.php?ok=datos');
    exit;
}

// ACTUALIZAR CONTRASEÑA
if ($accion === 'actualizar_password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $actual   = $_POST['password_actual']   ?? '';
    $nueva    = $_POST['password_nueva']    ?? '';
    $confirma = $_POST['password_confirma'] ?? '';

    // Validaciones
    if ($nueva !== $confirma) {
        header('Location: /configuracion.php?error=no_coinciden');
        exit;
    }

    if (strlen($nueva) < 6) {
        header('Location: /configuracion.php?error=muy_corta');
        exit;
    }

    if (!$modelo->verificarPassword($_SESSION['id_empleado'], $actual)) {
        header('Location: /configuracion.php?error=incorrecta');
        exit;
    }

    $modelo->actualizarPassword($_SESSION['id_empleado'], $nueva);
    header('Location: /configuracion.php?ok=password');
    exit;
}

header('Location: /configuracion.php');
exit;
