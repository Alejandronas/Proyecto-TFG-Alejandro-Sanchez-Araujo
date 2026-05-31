<?php
session_start();
$inicio = microtime(true);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/UsuarioModel.php';

$accion = $_GET['accion'] ?? '';

if ($accion === 'login') {

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $modelo  = new UsuarioModel($pdo);
    $usuario = $modelo->buscarPorCredenciales($username, $password);

    if ($usuario) {
        $_SESSION['usuario']     = $usuario['username'];
        $_SESSION['nombre']      = trim($usuario['nombre_real'] . ' ' . $usuario['apellido_real']);
        $_SESSION['rol']         = $usuario['rol'];
        $_SESSION['id_empleado'] = $usuario['id_empleado'];
        $_SESSION['id_paciente'] = $usuario['id_paciente'];
        error_log("Tiempo total: " . (microtime(true) - $inicio) . " segundos");

        // Registrar acceso en LOG_ACCESO
        try {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
            $stmt = $pdo->prepare(
                "INSERT INTO LOG_ACCESO (id_usuario, username, rol, ip) VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([$usuario['id_usuario'], $usuario['username'], $usuario['rol'], $ip]);
        } catch (Exception $e) {
            // No bloquear el login si falla el log
        }

        header('Location: /panel.php');
    } else {
        header('Location: /login.php?error=1');
    }
    exit;

} elseif ($accion === 'cerrar') {

    session_destroy();
    header('Location: /index.php');
    exit;
}
