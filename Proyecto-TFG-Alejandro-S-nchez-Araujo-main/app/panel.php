<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: /login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
  <link rel="icon" type="image/png" href="/img/logo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel — Clínica General</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php
if ($_SESSION['rol'] === 'medico') {
    require_once 'views/medico/panel_medico.php';
} elseif ($_SESSION['rol'] === 'paciente') {
    require_once 'views/paciente/panel_paciente.php';
} elseif ($_SESSION['rol'] === 'recepcionista') {
    require_once 'views/recepcionista/panel_recepcionista.php';
} elseif ($_SESSION['rol'] === 'administrador') {
    require_once 'views/admin/panel_admin.php';
} else {
    echo '<div class="container" style="margin-top:60px">';
    echo '<h2>Bienvenido, ' . htmlspecialchars($_SESSION['nombre']) . '</h2>';
    echo '<p>Rol: <strong>' . htmlspecialchars($_SESSION['rol']) . '</strong></p>';
    echo '<hr><p>Panel en construcción.</p>';
    echo '<a href="/controllers/AuthController.php?accion=cerrar">Cerrar sesión</a>';
    echo '</div>';
}
?>

</body>
</html>
