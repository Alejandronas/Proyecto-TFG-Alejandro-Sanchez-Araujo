<?php
session_start();
$pagina_actual = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <link rel="icon" type="image/png" href="/img/logo.png">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inicio — clinicageneral.local</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="/assets/css/index.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top px-4">
  <a class="navbar-brand" href="/index.php">
    <div class="brand-name">Clínica General</div>
    <div class="brand-sub">clinicageneral.local</div>
  </a>
  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse justify-content-end" id="navMenu">
    <ul class="navbar-nav align-items-center gap-2">
      <li class="nav-item">
        <a class="nav-link activo" href="/index.php">Inicio</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#servicios">Servicios</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#departamentos">Departamentos</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#contacto">Contacto</a>
      </li>

      <?php if (isset($_SESSION['usuario'])): ?>
        <li class="nav-item">
          <a class="nav-link btn-login ms-2" href="/panel.php">
            👤 <?= htmlspecialchars($_SESSION['nombre']) ?>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link btn-cita" href="/controllers/AuthController.php?accion=cerrar">Cerrar sesión</a>
        </li>
      <?php else: ?>
        <li class="nav-item">
          <a class="nav-link btn-cita ms-2" href="/login.php">Acceso personal</a>
        </li>
        <li class="nav-item">
          <a class="nav-link btn-cita" href="/login.php">Pedir cita</a>
        </li>
      <?php endif; ?>
    </ul>
  </div>
</nav>

<main style="padding-top: 72px;">

<!-- HERO -->
<section class="hero" id="registro">
  <div class="row g-0" style="min-height: calc(100vh - 72px);">
    <div class="col-lg-6 hero-left">
      <div class="hero-tag">Clínica General · Madrid</div>
      <h1>Tu salud,<br>nuestra <em>prioridad</em></h1>
      <p class="hero-desc">
        Atención médica integral para toda la familia. Especialistas, enfermería,
        laboratorio y administración reunidos en un mismo centro.
      </p>
      <div class="d-flex gap-3 flex-wrap">
        <a href="/login.php" class="btn btn-cita px-4 py-2">Solicitar cita</a>
        <a href="#servicios" class="btn btn-cita px-4 py-2">Ver servicios</a>
      </div>
      <div class="hero-stats row g-0">
        <div class="col-4"><div class="stat-num">8</div><div class="stat-label">Departamentos</div></div>
        <div class="col-4"><div class="stat-num">24h</div><div class="stat-label">Servicio continuo</div></div>
        <div class="col-4"><div class="stat-num">+500</div><div class="stat-label">Pacientes atendidos</div></div>
      </div>
    </div>
    <div class="col-lg-6 hero-right">
      <div class="cita-card">
        <h3>Crear cuenta</h3>
        <p class="sub">Regístrate y gestiona tus citas desde el portal</p>

        <?php if (isset($_GET['error_reg'])): ?>
          <div style="background:rgba(220,53,69,0.15);border:1px solid rgba(220,53,69,0.4);border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:13px;color:#fff;">
            <?php
              $err = $_GET['error_reg'];
              if ($err === 'passwords')       echo 'Las contraseñas no coinciden.';
              elseif ($err === 'corta')       echo 'La contraseña debe tener al menos 6 caracteres.';
              elseif ($err === 'usuario_dup') echo 'Ese nombre de usuario ya está en uso.';
              elseif ($err === 'email_dup')   echo 'Ese email ya tiene una cuenta asociada.';
              elseif ($err === 'dni_dup')     echo 'Ese DNI ya está registrado en el sistema.';
              else                            echo 'Error al crear la cuenta. Inténtalo de nuevo.';
            ?>
          </div>
        <?php endif; ?>

        <form method="POST" action="/controllers/RegisterController.php">
          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label">Nombre</label>
              <input type="text" name="nombre" class="form-control" placeholder="María" required>
            </div>
            <div class="col-6">
              <label class="form-label">Apellido</label>
              <input type="text" name="apellido" class="form-control" placeholder="García" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" placeholder="maria@correo.com" required>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label">DNI</label>
              <input type="text" name="dni" class="form-control" placeholder="12345678A" required>
            </div>
            <div class="col-6">
              <label class="form-label">Teléfono</label>
              <input type="text" name="telefono" class="form-control" placeholder="600000000" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Nº Seguridad Social</label>
            <input type="text" name="num_seguridad_social" class="form-control" placeholder="28 00123456 78" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Usuario</label>
            <input type="text" name="username" class="form-control" placeholder="maria.garcia" required>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label">Contraseña</label>
              <input type="password" name="password" class="form-control" required>
            </div>
            <div class="col-6">
              <label class="form-label">Confirmar</label>
              <input type="password" name="password_confirm" class="form-control" required>
            </div>
          </div>
          <button type="submit" class="btn-cita-form mt-1">Crear cuenta →</button>
        </form>

        <p style="font-size:12px;color:rgba(255,255,255,0.5);text-align:center;margin-top:16px;margin-bottom:0">
          ¿Ya tienes cuenta? <a href="/login.php" style="color:rgba(255,255,255,0.8)">Inicia sesión</a>
        </p>
      </div>
    </div>
  </div>
</section>

<!-- SERVICIOS -->
<section class="servicios" id="servicios">
  <div class="container">
    <div class="text-center mb-5 reveal">
      <div class="section-tag">Lo que ofrecemos</div>
      <h2 class="section-title">Servicios médicos</h2>
      <p class="section-desc mx-auto mt-3" style="max-width:520px">Contamos con todas las especialidades para cubrir las necesidades de salud de nuestros pacientes.</p>
    </div>
    <div class="row g-4">
      <div class="col-md-6 col-lg-3 reveal">
        <div class="servicio-card">
          <div class="servicio-icon"><i class="bi bi-heart-pulse"></i></div>
          <h3>Consultas médicas</h3>
          <p>Atención por especialistas en medicina general, dermatología, traumatología y más.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-3 reveal">
        <div class="servicio-card">
          <div class="servicio-icon"><i class="bi bi-droplet"></i></div>
          <h3>Laboratorio</h3>
          <p>Análisis clínicos completos con resultados rápidos. Hemograma, glucosa, colesterol y más.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-3 reveal">
        <div class="servicio-card">
          <div class="servicio-icon"><i class="bi bi-capsule"></i></div>
          <h3>Recetas y tratamientos</h3>
          <p>Gestión digital de recetas y seguimiento de tratamientos integrado en el historial clínico.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-3 reveal">
        <div class="servicio-card">
          <div class="servicio-icon"><i class="bi bi-file-medical"></i></div>
          <h3>Historial clínico</h3>
          <p>Historial médico completo y accesible para cada paciente, con antecedentes y alergias.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- DEPARTAMENTOS -->
<section class="departamentos" id="departamentos">
  <div class="container">
    <div class="text-center mb-5 reveal">
      <div class="section-tag">Nuestra estructura</div>
      <h2 class="section-title">Departamentos</h2>
      <p class="section-desc mx-auto mt-3" style="max-width:520px">Organización interna diseñada para ofrecer la mejor atención a cada paciente.</p>
    </div>
    <div class="row">
      <div class="col-md-6 col-lg-3 reveal"><div class="dept-item"><div class="dept-dot"></div><span>Dirección</span></div></div>
      <div class="col-md-6 col-lg-3 reveal"><div class="dept-item"><div class="dept-dot"></div><span>Administración</span></div></div>
      <div class="col-md-6 col-lg-3 reveal"><div class="dept-item"><div class="dept-dot"></div><span>Recursos Humanos</span></div></div>
      <div class="col-md-6 col-lg-3 reveal"><div class="dept-item"><div class="dept-dot"></div><span>Informática / IT</span></div></div>
      <div class="col-md-6 col-lg-3 reveal"><div class="dept-item"><div class="dept-dot"></div><span>Especialistas</span></div></div>
      <div class="col-md-6 col-lg-3 reveal"><div class="dept-item"><div class="dept-dot"></div><span>Enfermería</span></div></div>
      <div class="col-md-6 col-lg-3 reveal"><div class="dept-item"><div class="dept-dot"></div><span>Recepción</span></div></div>
      <div class="col-md-6 col-lg-3 reveal"><div class="dept-item"><div class="dept-dot"></div><span>Laboratorio</span></div></div>
    </div>
  </div>
</section>

<!-- CONTACTO -->
<section class="contacto-strip" id="contacto">
  <div class="container">
    <div class="row align-items-center gy-4">
      <div class="col-lg-6">
        <h2>¿Necesitas <em>atención médica</em>?<br>Estamos aquí para ayudarte.</h2>
      </div>
      <div class="col-lg-6">
        <div class="row text-center g-3">
          <div class="col-4 contacto-dato"><div class="label">Teléfono</div><div class="valor">900 000 001</div></div>
          <div class="col-4 contacto-dato"><div class="label">Email</div><div class="valor" style="font-size:12px">info@clinicageneral.local</div></div>
          <div class="col-4 contacto-dato"><div class="label">Horario</div><div class="valor">Lun–Vie 8–20h</div></div>
        </div>
      </div>
    </div>
  </div>
</section>

</main>

<footer style="background: #2c2c2c; padding: 36px 0; margin-top: auto;">
  <div class="container d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
      <div style="font-family: 'Playfair Display', serif; font-size: 18px; color: #fff;">
        Clínica General
      </div>
      <div style="font-size: 12px; color: rgba(255,255,255,0.4); margin-top: 2px;">
        © <?= date('Y') ?> · Alejandro Sánchez Araujo · 2º ASIR
      </div>
    </div>
    <p style="font-size: 13px; color: rgba(255,255,255,0.4); margin: 0;">
      clinicageneral.local · Sistema de gestión integral
    </p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const observer = new IntersectionObserver((entries) => {
    entries.forEach((e, i) => {
      if (e.isIntersecting) setTimeout(() => e.target.classList.add('visible'), i * 80);
    });
  }, { threshold: 0.1 });
  document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
</script>

</body>
</html>
