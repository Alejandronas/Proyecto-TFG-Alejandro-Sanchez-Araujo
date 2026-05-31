<?php
session_start();

if (isset($_SESSION['usuario'])) {
    header('Location: /panel.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <link rel="icon" type="image/png" href="/img/logo.png">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Acceso personal — Clínica General</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="/assets/css/index.css" rel="stylesheet">
  <style>
    .login-card {
      background: #fff;
      border: 1px solid var(--borde);
      border-radius: 16px;
      padding: 40px 36px;
      box-shadow: 0 8px 32px rgba(10,110,92,0.08);
    }
    .login-card h2 {
      font-family: 'Playfair Display', serif;
      font-size: 26px;
      font-weight: 700;
      color: var(--gris-texto);
      margin-bottom: 6px;
    }
    .login-card .sub {
      font-size: 14px;
      color: var(--gris-suave);
      margin-bottom: 28px;
    }
    .login-card .form-label {
      font-size: 11px;
      letter-spacing: 0.8px;
      text-transform: uppercase;
      color: var(--gris-suave);
      margin-bottom: 6px;
    }
    .login-card .form-control {
      border: 1px solid var(--borde);
      border-radius: 8px;
      font-family: 'DM Sans', sans-serif;
      font-size: 14px;
      padding: 10px 14px;
    }
    .login-card .form-control:focus {
      border-color: var(--verde);
      box-shadow: 0 0 0 3px rgba(10,110,92,0.1);
    }
    .btn-entrar {
      width: 100%;
      padding: 12px;
      background: var(--verde);
      color: #fff;
      border: none;
      border-radius: 8px;
      font-size: 15px;
      font-weight: 500;
      font-family: 'DM Sans', sans-serif;
      transition: background 0.2s;
      cursor: pointer;
    }
    .btn-entrar:hover { background: var(--verde-claro); }
    .alerta-error {
      background: #fef2f2;
      border: 1px solid #fecaca;
      color: #b91c1c;
      border-radius: 8px;
      padding: 10px 14px;
      font-size: 13px;
      margin-bottom: 20px;
    }
    .alerta-ok {
      background: #f0fdf4;
      border: 1px solid #bbf7d0;
      color: #15803d;
      border-radius: 8px;
      padding: 10px 14px;
      font-size: 13px;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top px-4">
  <a class="navbar-brand" href="/index.php">
    <div class="brand-name">Clínica General</div>
    <div class="brand-sub">clinicageneral.local</div>
  </a>
</nav>

<main style="padding-top: 72px; min-height: calc(100vh - 72px); display:flex; align-items:center; background: var(--crema);">
  <div class="container" style="max-width: 420px;">
    <div class="login-card">
      <h2>Acceso personal</h2>
      <p class="sub">Introduce tus credenciales para entrar al portal</p>

      <?php if (isset($_GET['ok']) && $_GET['ok'] === 'registrado'): ?>
        <div class="alerta-ok">✓ Cuenta creada correctamente. Ya puedes iniciar sesión.</div>
      <?php endif; ?>

      <?php if (isset($_GET['error'])): ?>
        <div class="alerta-error">Usuario o contraseña incorrectos.</div>
      <?php endif; ?>

      <form method="POST" action="/controllers/AuthController.php?accion=login">
        <div class="mb-3">
          <label class="form-label">Usuario</label>
          <input type="text" name="username" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
          <label class="form-label">Contraseña</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn-entrar">Entrar →</button>
      </form>

      <p style="font-size:13px;color:var(--gris-suave);text-align:center;margin-top:20px;margin-bottom:0">
        ¿No tienes cuenta? <a href="/index.php#registro" style="color:var(--verde);font-weight:500">Regístrate</a>
      </p>
    </div>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
