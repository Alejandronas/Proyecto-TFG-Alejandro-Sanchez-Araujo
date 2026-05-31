<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/AdminModel.php';

$modelo = new AdminModel($pdo);
$accion = $_GET['accion'] ?? '';

// CREAR EMPLEADO
if ($accion === 'crear_empleado' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'nombre'             => trim($_POST['nombre']            ?? ''),
        'apellido'           => trim($_POST['apellido']          ?? ''),
        'fecha_contratacion' => $_POST['fecha_contratacion']     ?? '',
        'salario'            => $_POST['salario']                ?? null,
        'rol'                => $_POST['rol']                    ?? '',
        'id_departamento'    => $_POST['id_departamento']        ?? null,
        'id_especialidad'    => $_POST['id_especialidad']        ?? null,
        'username'           => trim($_POST['username']          ?? ''),
        'password'           => $_POST['password']               ?? '',
    ];
    $modelo->crearEmpleado($datos);
    header('Location: /admin/empleados_admin.php?ok=creado');
    exit;
}

// ACTUALIZAR EMPLEADO
if ($accion === 'actualizar_empleado' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id    = (int)($_POST['id_empleado'] ?? 0);
    $datos = [
        'nombre'             => trim($_POST['nombre']            ?? ''),
        'apellido'           => trim($_POST['apellido']          ?? ''),
        'fecha_contratacion' => $_POST['fecha_contratacion']     ?? '',
        'salario'            => $_POST['salario']                ?? null,
        'rol'                => $_POST['rol']                    ?? '',
        'id_departamento'    => $_POST['id_departamento']        ?? null,
        'id_especialidad'    => $_POST['id_especialidad']        ?? null,
    ];
    $modelo->actualizarEmpleado($id, $datos);
    header('Location: /admin/empleados_admin.php?ok=actualizado');
    exit;
}

// TOGGLE ACTIVO/INACTIVO
if ($accion === 'toggle_activo' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = (int)($_POST['id_empleado'] ?? 0);
    $activo = (int)($_POST['activo']      ?? 1);
    $modelo->toggleActivo($id, $activo);
    header('Location: /admin/empleados_admin.php?ok=estado');
    exit;
}

// RESET PASSWORD
if ($accion === 'reset_password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id       = (int)($_POST['id_empleado']    ?? 0);
    $password = $_POST['nueva_password']        ?? '';
    if ($id && strlen($password) >= 6) {
        $modelo->resetPassword($id, $password);
        header('Location: /admin/empleados_admin.php?ok=password');
    } else {
        header('Location: /admin/empleados_admin.php?err=password');
    }
    exit;
}

// CREAR PACIENTE
if ($accion === 'crear_paciente' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'nombre'               => trim($_POST['nombre']               ?? ''),
        'apellido'             => trim($_POST['apellido']             ?? ''),
        'dni'                  => trim($_POST['dni']                  ?? ''),
        'telefono'             => trim($_POST['telefono']             ?? ''),
        'email'                => trim($_POST['email']                ?? ''),
        'num_seguridad_social' => trim($_POST['num_seguridad_social'] ?? ''),
        'fecha_nacimiento'     => $_POST['fecha_nacimiento']          ?? null,
        'genero'               => $_POST['genero']                    ?? null,
        'direccion'            => trim($_POST['direccion']            ?? ''),
    ];
    $modelo->crearPaciente($datos);
    header('Location: /admin/pacientes_admin.php?ok=creado');
    exit;
}

// Fallback
header('Location: /admin/empleados_admin.php');
exit;
