<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'recepcionista') {
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
    <title>Citas — Clínica General</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php require_once '../views/recepcionista/citas_recepcionista.php'; ?>
</body>
</html>
