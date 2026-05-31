<?php
session_start();

// Si ya está logueado, no tiene sentido registrarse
if (isset($_SESSION['usuario'])) {
    header('Location: /panel.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /index.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$nombre              = trim($_POST['nombre']              ?? '');
$apellido            = trim($_POST['apellido']            ?? '');
$email               = trim($_POST['email']               ?? '');
$dni                 = trim($_POST['dni']                 ?? '');
$telefono            = trim($_POST['telefono']            ?? '');
$num_seguridad_social= trim($_POST['num_seguridad_social']?? '');
$username            = trim($_POST['username']            ?? '');
$password            = $_POST['password']                 ?? '';
$confirm             = $_POST['password_confirm']         ?? '';

// Validaciones
if ($password !== $confirm) {
    header('Location: /index.php?error_reg=passwords');
    exit;
}

if (strlen($password) < 6) {
    header('Location: /index.php?error_reg=corta');
    exit;
}

// Comprobar username duplicado
$stmt = $pdo->prepare("SELECT COUNT(*) FROM USUARIO WHERE username = ?");
$stmt->execute([$username]);
if ($stmt->fetchColumn() > 0) {
    header('Location: /index.php?error_reg=usuario_dup');
    exit;
}

// Comprobar email duplicado
$stmt = $pdo->prepare("SELECT COUNT(*) FROM PACIENTE WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetchColumn() > 0) {
    header('Location: /index.php?error_reg=email_dup');
    exit;
}

// Comprobar DNI duplicado
$stmt = $pdo->prepare("SELECT COUNT(*) FROM PACIENTE WHERE dni = ?");
$stmt->execute([$dni]);
if ($stmt->fetchColumn() > 0) {
    header('Location: /index.php?error_reg=dni_dup');
    exit;
}

// Insertar
try {
    $pdo->beginTransaction();

    // 1. Crear registro en PACIENTE
    $stmt = $pdo->prepare("
        INSERT INTO PACIENTE (nombre, apellido, email, dni, telefono, num_seguridad_social)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$nombre, $apellido, $email, $dni, $telefono, $num_seguridad_social]);
    $id_paciente = $pdo->lastInsertId();

    // 2. Crear usuario vinculado al paciente
    $stmt = $pdo->prepare("
        INSERT INTO USUARIO (id_paciente, username, password, rol)
        VALUES (?, ?, SHA2(?, 256), 'paciente')
    ");
    $stmt->execute([$id_paciente, $username, $password]);

    $pdo->commit();

    header('Location: /login.php?ok=registrado');
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    header('Location: /index.php?error_reg=general');
    exit;
}
