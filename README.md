# 🏥 Clínica General
### Sistema de Gestión Integral · Proyecto de Fin de Grado

**Alumno:** Alejandro Sánchez Araujo  
**Centro:** IES Albarregas · 2º ASIR  
**Dominio:** `clinicageneral.local`  
**Tecnologías:** Vagrant · VirtualBox · Ubuntu · Apache · PHP · MySQL · Nginx · Bootstrap

---

## 1. Descripción del proyecto

El proyecto consiste en el desarrollo e implantación de una infraestructura de red completa que soporte un sistema de gestión integral para una clínica general. La solución abarca cuatro módulos principales: servicios de red, administración de sistemas, base de datos y seguridad, e implantación de aplicaciones web.

La empresa sobre la que se realiza el proyecto es una clínica general con sede en Madrid, organizada en ocho departamentos y gestionada bajo el dominio `clinicageneral.local`. El sistema permite administrar pacientes, citas médicas, consultas, historiales clínicos, análisis de laboratorio, recetas y facturación desde una plataforma web centralizada accesible desde cualquier navegador dentro de la red interna.

Toda la infraestructura se despliega sobre máquinas virtuales Ubuntu 22.04 sin entorno gráfico mediante Vagrant y VirtualBox, simulando un entorno de producción real con criterios de disponibilidad, seguridad y eficiencia.

---

## 2. Módulos del proyecto

### 2.1 Servicios en red

Implementación de los servicios de red necesarios para el funcionamiento de la clínica:

- **DHCP:** asignación automática de direcciones IP a los equipos de la red LAN1 (`10.0.60.0/23`), donde reside el Windows Server, con reservas para servidores y equipos críticos.
- **DNS:** servidor de nombres interno con dominio `clinicageneral.local` para la red LAN1, con resolución directa e inversa e integración con el servidor web y FTP.
- **FTP:** servidor para el intercambio seguro de archivos entre departamentos (imágenes médicas, informes, resultados de laboratorio) con usuarios y permisos por departamento.
- **HTTP:** servidor web corporativo accesible desde el dominio gestionado por el DNS, con la aplicación de gestión de la clínica.

### 2.2 Administración de sistemas

Gestión y automatización de los sistemas operativos de la infraestructura:

- **Linux:** scripts bash para facilitar la administración del servidor, incluyendo gestión de usuarios, configuración de servicios y administración de permisos.
- **Windows Server:** implementación del dominio `clinicageneral.local` con estructura de unidades organizativas, grupos de seguridad, usuarios y políticas de grupo (GPO).

### 2.3 Base de datos y seguridad

Diseño e implementación de la base de datos relacional y las políticas de seguridad:

- Diseño del modelo entidad-relación con 19 tablas interrelacionadas que cubren todos los módulos del sistema clínico.
- Implementación en MySQL 8 con integridad referencial completa mediante claves foráneas y charset `utf8mb4`.
- Seguridad con iptables: política por defecto DROP, reglas específicas por IP para el acceso al puerto 3306, acceso SSH restringido a `10.0.0.0/8` y registro de eventos con prefijo `IPTABLES-DROP:`.

### 2.4 Implantación de aplicaciones web

Despliegue de una aplicación web de tres capas sobre infraestructura virtualizada:

- **Infraestructura:** dos servidores backend con Apache y PHP, un balanceador de carga Nginx con `ip_hash` y un servidor NFS para el código compartido, todos sobre Ubuntu sin entorno gráfico.
- **Aplicación web:** sistema de gestión clínica con módulos de pacientes, citas, historiales clínicos y control de acceso por roles. El código reside en un servidor NFS montado en ambos backends.
- **Alta disponibilidad:** balanceo de carga con `ip_hash` entre dos backends para garantizar la persistencia de sesión y la continuidad del servicio.

---

## 3. Arquitectura de red

La infraestructura sigue un modelo segmentado en cuatro redes privadas, cada una con una función específica. Todo el tráfico externo entra por el Router/NAT y se distribuye según la red de destino.

```
Router NAT / Firewall  (10.0.50.40 · 10.0.60.40)
│
├── LAN1 (10.0.60.0/23) — Servicios de red
│   ├── Windows Server (DHCP + DNS + AD)   10.0.60.10
│   └── FTP                                10.0.60.20
│
└── LAN2 (10.0.50.0/23) — Acceso exterior
    └── Balanceador    10.0.50.10  ← Nginx ip_hash
        │
        └── LAN Interna (10.0.30.0/23)
            ├── Backend 1  10.0.30.10  ← Apache + PHP
            ├── Backend 2  10.0.30.20  ← Apache + PHP
            └── NFS        10.0.30.40  ← Código compartido
                │
                └── LAN Datos (10.0.20.0/23)
                    └── SGBD   10.0.20.10  ← MySQL 8
```

<img width="1920" height="1080" alt="Balanceador (11)" src="https://github.com/user-attachments/assets/70f25f6d-2ef6-4afe-a910-03d6e64d149a" />

### 3.1 Segmentos de red

| Red | Rango | Función |
|-----|-------|---------|
| LAN Datos | 10.0.20.0/23 | Red exclusiva para el servidor de base de datos. Solo los backends tienen acceso permitido al puerto 3306. |
| LAN Interna | 10.0.30.0/23 | Red de los servidores backend y NFS. Comunicación entre backends, NFS y balanceador. |
| LAN2 | 10.0.50.0/23 | Red de acceso exterior al balanceador desde la red de la clínica. |
| LAN1 | 10.0.60.0/23 | Red de servicios. Aloja el Windows Server (DHCP+DNS+AD) y el servidor FTP. |

### 3.2 Servidores y direcciones IP

| Servidor | IP principal | IP secundaria | Función |
|----------|-------------|---------------|---------|
| SGBD | 10.0.20.10 | — | Base de datos MySQL 8 |
| Backend 1 | 10.0.30.10 | 10.0.20.20 | Apache + PHP (monta NFS) |
| Backend 2 | 10.0.30.20 | 10.0.20.30 | Apache + PHP (monta NFS) |
| NFS | 10.0.30.40 | — | Servidor NFS, exporta `/srv/app` |
| Balanceador | 10.0.50.10 | 10.0.30.30 | Nginx ip_hash |
| Router | 10.0.50.40 | 10.0.60.40 | NAT + IP forwarding entre redes |
| Windows Server | 10.0.60.10 | — | DHCP + DNS + Active Directory |
| FTP | 10.0.60.20 | — | vsftpd con usuarios por departamento |

---

## 4. Base de datos

Base de datos `clinica` implementada en MySQL 8 con `utf8mb4` y diseño normalizado con integridad referencial completa. Contraseñas almacenadas con `SHA2(password, 256)`.

| Tabla | Descripción |
|-------|-------------|
| `DEPARTAMENTO` | Departamentos de la clínica (8 departamentos) |
| `ESPECIALIDAD` | Especialidades médicas disponibles |
| `GRUPO` | Grupos de trabajo multidisciplinares |
| `PACIENTE` | Datos personales de los pacientes |
| `TIPO_PRUEBA` | Catálogo de análisis de laboratorio |
| `EMPLEADO` | Personal de la clínica con rol y departamento |
| `EMPLEADOS_GRUPOS` | Relación N:M entre empleados y grupos |
| `MEDICO_PACIENTE` | Asignación de pacientes a médicos |
| `HISTORIAL_MEDICO` | Historial clínico por paciente (1:1) |
| `HISTORIAL_CLINICO` | Entradas de historial con tipo de consulta |
| `CITA` | Citas programadas entre pacientes y médicos |
| `CONSULTA` | Consultas médicas con diagnóstico y tratamiento |
| `RECETA` | Recetas emitidas durante las consultas |
| `DETALLE_RECETA` | Medicamentos, dosis y duración por receta |
| `SOLICITUD_ANALISIS` | Solicitudes de análisis generadas en consulta |
| `RESULTADO_LABORATORIO` | Resultados numéricos de los análisis |
| `FACTURA` | Facturas asociadas a pacientes y consultas |
| `USUARIO` | Cuentas de acceso con rol y contraseña SHA-256 |
| `LOG_ACCESO` | Registro de inicios de sesión con IP y timestamp |

### Seguridad iptables (SGBD)

| Regla | Descripción |
|-------|-------------|
| Política INPUT DROP | Todo el tráfico entrante se rechaza por defecto |
| Puerto 3306 | Solo accesible desde `10.0.20.20` y `10.0.20.30` (backends) |
| Puerto 22 (SSH) | Restringido a `10.0.0.0/8` |
| LOG | Paquetes rechazados registrados con prefijo `IPTABLES-DROP:` |

---

## 5. Dominio Windows Server

El dominio `clinicageneral.local` está implementado en Windows Server (`10.0.60.10`) dentro de la red LAN1 (`10.0.60.0/23`), con Active Directory, DNS y DHCP. La estructura organizativa refleja los departamentos reales de la clínica.

### 5.1 Unidades organizativas

```
clinicageneral.local
└── CLINICA
    ├── Dirección         → director.general, subdirector
    ├── Administración    → jefe.administracion, facturacion1
    ├── RRHH              → jefe.rrhh, rrhh.tecnico1
    ├── Informática       → admin.sistema, tecnico.it1, tecnico.it2
    ├── Especialistas     → esp.cardiologia, esp.dermatologia, esp.traumatologia
    ├── Enfermería        → enf.ana, enf.carlos
    ├── Recepción         → recepcion1, recepcion2
    └── Laboratorio       → lab.tecnico1, lab.tecnico2
```

### 5.2 Grupos de seguridad

| Grupo | Ámbito | Descripción |
|-------|--------|-------------|
| DIR_Usuarios | Global | Dirección con acceso a recursos confidenciales |
| ADM_Usuarios | Global | Administración y Facturación |
| RRHH_Usuarios | Global | Recursos Humanos y gestión de personal |
| IT_Usuarios | Global | Departamento IT con acceso a herramientas internas |
| IT_Admins | Global | Administradores de dominio con permisos elevados |
| ESP_Usuarios | Global | Especialistas con acceso a historial y agenda |
| ENF_Usuarios | Global | Enfermería con acceso a historial y seguimiento |
| REC_Usuarios | Global | Recepción con acceso a gestión de citas |
| LAB_Usuarios | Global | Laboratorio con acceso a resultados y análisis |

### 5.3 Servicios de red (Windows Server)

- **DNS:** servidor de nombres para la red LAN1 (`10.0.60.0/23`). Gestiona la zona directa e inversa de `clinicageneral.local`. Los servidores Linux apuntan a `10.0.60.10` como DNS primario (el servidor FTP tiene `/etc/resolv.conf` bloqueado con `chattr +i`).
- **DHCP:** asigna IPs dinámicamente a los equipos de la red LAN1 (`10.0.60.0/23`), con reservas fijas para los servidores críticos de esa red.

---

## 6. Servicios en red Linux

### FTP (vsftpd)

Servidor FTP en `10.0.60.20` con usuarios por departamento y acceso restringido mediante chroot.

- 8 grupos de departamento creados en el sistema
- Modo pasivo con rango de puertos `40000–50000`
- Chroot por usuario (cada departamento accede solo a su carpeta)
- DNS apuntando a Windows Server (`10.0.60.10`) con `chattr +i` en `/etc/resolv.conf`

### NFS

Servidor en `10.0.30.40`. Exporta `/srv/app` a toda la red `10.0.30.0/23`.

```
/srv/app  10.0.30.0/23(rw,sync,no_subtree_check,no_root_squash)
```

Ambos backends montan el directorio vía `/etc/fstab`:

```
10.0.30.40:/srv/app  /var/www/html  nfs  defaults,_netdev  0  0
```

### Balanceador de carga (Nginx)

Nginx en `10.0.50.10` con `ip_hash` para persistencia de sesión PHP.

```nginx
upstream backends {
    ip_hash;
    server 10.0.30.10;
    server 10.0.30.20;
}
```

Ruta estática hacia la red de Windows Server (`10.0.60.0/23`) configurada vía netplan a través del router `10.0.50.40`.

---

## 6.5 Router / NAT

El router (`10.0.50.40` / `10.0.60.40`) interconecta la LAN2 (balanceador) con la LAN1 (Windows Server y FTP). Sin él, el balanceador no tendría visibilidad sobre Active Directory ni el servidor FTP.

- **IP forwarding** activado: `net.ipv4.ip_forward = 1` en `/etc/sysctl.conf`
- **Ruta estática en el balanceador** (netplan): destino `10.0.60.0/23` vía `10.0.50.40`
- **Flujo DNS:** balanceador → router (eth1 LAN2) → router (eth2 LAN1) → Windows Server `10.0.60.10`

| Interfaz | IP | Red | Conecta con |
|---|---|---|---|
| eth1 (LAN2) | 10.0.50.40 | 10.0.50.0/23 | Balanceador (10.0.50.10) |
| eth2 (LAN1) | 10.0.60.40 | 10.0.60.0/23 | Windows Server (10.0.60.10), FTP (10.0.60.20) |

---

## 7. Herramienta de monitorización

Consola de administración centralizada en Python con interfaz gráfica Tkinter. Permite supervisar el estado del hardware, gestionar el ciclo de vida de las máquinas virtuales y auditar la seguridad de la red.

### 7.1 Arquitectura

- **Frontend:** interfaz gráfica en Python + Tkinter (`monitor_sistema.py`).
- **Backend:** scripts bash modulares en `/scripts` que ejecutan las tareas de bajo nivel.
- **Comunicación:** `subprocess.Popen` con streaming asíncrono para no bloquear la interfaz durante la ejecución de comandos.

### 7.2 Funcionalidades

**Gestión de virtualización (Vagrant)**  
Control total de los nodos desde la interfaz: `up`, `halt`, `provision`, `status`. Acceso SSH rápido y visualización de configuración de red de cada VM.

**Monitorización de recursos**  
CPU (carga media, top de procesos), RAM y swap, temperatura del sistema vía `/sys/class/thermal` y `lm-sensors`, almacenamiento de discos virtuales (`.vdi`, `.vmdk`).

**Supervisión de servicios web**  
Estado de `apache2`, `nginx`, `mysql` y `php-fpm`. Puertos abiertos (80, 443, 3306) y estadísticas de tráfico RX/TX por interfaz.

**Seguridad y logs**  
Análisis de `/var/log/auth.log` y `journalctl` para detectar intentos fallidos de login. Extracción de las 10 IPs con más intentos para facilitar su bloqueo en el firewall.

### 7.3 Scripts de automatización

| Script | Función |
|--------|---------|
| `cpu.sh` | Uptime, carga media y top de procesos por consumo |
| `servicios_web.sh` | Estado de Apache, Nginx y MySQL |
| `vagrant_control.sh` | Orquestador de comandos para las VMs del proyecto |
| `discos_vdi.sh` | Espacio ocupado por discos virtuales |
| `login_fallidos.sh` | Auditoría de intentos de acceso fallidos |
| `trafico_red.sh` | Interfaces, rutas y conexiones TCP activas |

---

## 8. Despliegue de la infraestructura

```bash
# Levantar toda la infraestructura
vagrant up

# Levantar una máquina concreta (respetar el orden recomendado)
vagrant up sgbd
vagrant up nfs
vagrant up backend1
vagrant up backend2
vagrant up balanceador
vagrant up router
vagrant up ftp

# Acceder por SSH
vagrant ssh sgbd

# Reaprovisionar sin recrear la máquina
vagrant provision backend1

# Apagar todo
vagrant halt
```

> Se recomienda levantar primero `sgbd` y `nfs` antes que los backends, ya que estos montan el directorio NFS en el arranque.

---

## 9. Credenciales de prueba

### Aplicación web

| Usuario | Contraseña | Rol |
|---------|-----------|-----|
| `admin` | `admin1234` | Administrador |
| `pedro.alonso` | `medico1234` | Médico |
| `carmen.torres` | `enf1234` | Enfermera |
| `antonio.ruiz` | `rec1234` | Recepcionista |
| `miguel.serrano` | `lab1234` | Laboratorio |
| `maria.garcia` | `pac1234` | Paciente |

> Las contraseñas se almacenan como `SHA2(password, 256)` en la tabla `USUARIO`.

### Servidor FTP

Todos los usuarios FTP tienen la contraseña: `Clinica2025!`

### MySQL

| Usuario | Contraseña | Acceso |
|---------|-----------|--------|
| `clinica_user` | `1234` | Desde backends (`10.0.20.%`, `10.0.30.%`) |
| `clinica_admin` | `Admin_Cl1nica!` | Localhost en SGBD |

---


---

## 10. Análisis de `/app` *(práctica marzo – junio)*

> Esta sección corresponde a lo desarrollado durante el período de **marzo a junio**.

La carpeta `app/` contiene la aplicación web de **Clínica General**, un sistema de gestión para una clínica privada desarrollado en PHP puro sin framework, siguiendo el patrón MVC. Permite gestionar citas, pacientes, historiales clínicos y empleados a través de cuatro roles de acceso diferenciados:

- **Paciente** — solicita y cancela citas, consulta su historial clínico y gestiona su perfil.
- **Médico** — gestiona sus citas y pacientes asignados, registra entradas en el historial y dispone de un calendario interactivo.
- **Recepcionista** — administra todas las citas, asigna médicos a citas pendientes y da de alta pacientes.
- **Administrador** — gestiona empleados, consulta estadísticas globales y revisa el log de accesos.

Cada rol tiene su propio conjunto de vistas y controllers, con acceso restringido mediante `$_SESSION['rol']`.

---

### Estructura de directorios

```
app/
├── index.php
├── login.php
├── panel.php
├── configuracion.php
├── config/
│   └── db.php
├── controllers/
│   ├── AuthController.php
│   ├── RegisterController.php
│   ├── AdminController.php
│   ├── CitaController.php
│   ├── RecepcionistaController.php
│   ├── PacienteController.php
│   ├── PerfilPacienteController.php
│   ├── ConfiguracionController.php
│   └── CalendarioController.php
├── models/
│   ├── UsuarioModel.php
│   ├── AdminModel.php
│   ├── CitaModel.php
│   ├── PacienteModel.php
│   └── ConfiguracionModel.php
├── views/
│   ├── admin/
│   │   ├── panel_admin.php
│   │   ├── empleados_admin.php
│   │   ├── citas_admin.php
│   │   └── logs_admin.php
│   ├── medico/
│   │   ├── panel_medico.php
│   │   ├── citas_medico.php
│   │   ├── pacientes_medico.php
│   │   └── configuracion_medico.php
│   ├── paciente/
│   │   ├── panel_paciente.php
│   │   ├── citas_paciente.php
│   │   ├── pedir_cita.php
│   │   ├── historial_paciente.php
│   │   └── perfil_paciente.php
│   └── recepcionista/
│       ├── panel_recepcionista.php
│       └── citas_recepcionista.php
├── includes/
│   ├── sidebar_medico.php
│   ├── sidebar_admin.php
│   ├── sidebar_paciente.php
│   └── sidebar_recepcionista.php
└── assets/
    └── css/
        ├── index.css
        ├── panel_medico.css
        ├── panel_admin.css
        ├── panel_paciente.css
        ├── citas_medico.css
        ├── pacientes_medico.css
        └── configuracion_medico.css
```

---

### Archivos raíz y configuración

| Nombre | Archivo | Descripción |
|--------|---------|-------------|
| Página principal | `index.php` | Landing pública con formulario de auto-registro de paciente. Bootstrap 5.3.3 + Google Fonts + CSS custom properties. |
| Inicio de sesión | `login.php` | Formulario de login. Redirige a `panel.php` si ya hay sesión activa. |
| Enrutador | `panel.php` | Lee `$_SESSION['rol']` y carga la view correspondiente con `require_once`. |
| Configuración | `configuracion.php` | Carga la vista de configuración del médico. Solo rol `medico`. |
| Conexión BD | `config/db.php` | PDO a MySQL `10.0.20.10`, BD `clinica`, `utf8mb4`, `ERRMODE_EXCEPTION`. |

---

### Controladores

| Nombre | Archivo | Descripción |
|--------|---------|-------------|
| Autenticación | `AuthController.php` | Login y logout. Registra IP real en `LOG_ACCESO` (`HTTP_X_FORWARDED_FOR`). |
| Registro | `RegisterController.php` | Alta pública de paciente. Valida duplicados y ejecuta transacción atómica en `PACIENTE` + `USUARIO` con `SHA2`. |
| Administrador | `AdminController.php` | CRUD de empleados, toggle activo y reset de contraseña. Solo rol `administrador`. |
| Citas (médico) | `CitaController.php` | Guardar, actualizar, eliminar y completar citas. Solo rol `medico`. |
| Recepcionista | `RecepcionistaController.php` | CRUD de citas, asignación de médico (activa transición `pendiente → programada`) y alta de paciente. |
| Pacientes | `PacienteController.php` | Endpoint JSON de búsqueda por DNI/teléfono, asignar/desasignar paciente a médico y guardar historial. |
| Perfil paciente | `PerfilPacienteController.php` | Editar perfil, cambiar contraseña, solicitar y cancelar citas. Solo rol `paciente`. |
| Configuración médico | `ConfiguracionController.php` | Actualiza datos personales (refresca `$_SESSION` en tiempo real) y contraseña. |
| Calendario | `CalendarioController.php` | API REST JSON para FullCalendar. Devuelve eventos con color por estado. HTTP 403 sin sesión. |

---
## Patrón MVC en la aplicación

La aplicación sigue el patrón **Modelo-Vista-Controlador (MVC)** implementado de forma artesanal en PHP 8 sin ningún framework. Cada petición HTTP pasa por tres capas bien diferenciadas:

- **Modelo** — se comunica exclusivamente con la base de datos mediante PDO. Contiene todas las queries SQL y devuelve los datos en arrays PHP. No genera ningún HTML.
- **Vista** — recibe los datos del controlador y genera el HTML final. No ejecuta queries ni lógica de negocio.
- **Controlador** — actúa como intermediario. Valida la sesión y el rol, llama al modelo, y pasa el resultado a la vista con `require_once`.

### Flujo de una petición

```
Navegador → panel.php → Controller → Model → BD MySQL
                     ↑                         ↓
                   Vista ←───────── datos ──────┘
```

`panel.php` actúa como enrutador central: lee `$_SESSION['rol']` y carga el controlador correspondiente. Cada controlador comprueba el rol antes de ejecutar nada y devuelve HTTP 403 si no coincide.

### Ejemplo: el médico accede a sus citas

1. El navegador hace `GET panel.php?seccion=citas`
2. `panel.php` detecta `rol = medico` y carga `CitaController.php`
3. `CitaController` valida la sesión, llama a `CitaModel::getCitasMedico($id)`
4. `CitaModel` ejecuta la query con PDO y devuelve el array de citas
5. El controlador hace `require_once 'views/medico/citas_medico.php'`
6. La vista itera el array y genera el HTML con Bootstrap

### Separación por rol

Cada rol tiene su propio conjunto de controladores, modelos y vistas. El acceso entre roles está bloqueado a nivel de controlador:

| Capa | Médico | Paciente | Recepcionista | Administrador |
|------|--------|----------|---------------|---------------|
| Controller | `CitaController` · `PacienteController` · `ConfiguracionController` | `PerfilPacienteController` | `RecepcionistaController` | `AdminController` |
| Model | `CitaModel` · `PacienteModel` · `ConfiguracionModel` | `PacienteModel` | `CitaModel` | `AdminModel` |
| View | `views/medico/` | `views/paciente/` | `views/recepcionista/` | `views/admin/` |

### Modelos

| Nombre | Archivo | Descripción |
|--------|---------|-------------|
| Usuario | `UsuarioModel.php` | Un método: `buscarPorCredenciales`. LEFT JOIN a `EMPLEADO` y `PACIENTE` con `COALESCE` + `SHA2`. |
| Administrador | `AdminModel.php` | Estadísticas globales, ranking de citas por médico (`SUM` condicional), CRUD de empleados con `lastInsertId()`. |
| Cita | `CitaModel.php` | Modelo más extenso. Queries distintas por rol: médico, paciente y recepcionista. Incluye `programarSiPendiente()` y `completarCitaMedico()` con `rowCount()`. |
| Paciente | `PacienteModel.php` | Historial clínico, búsqueda por DNI o teléfono LIKE, gestión de perfil (solo campos editables) y contraseña con SHA2. |
| Configuración | `ConfiguracionModel.php` | Datos del médico (JOIN a `USUARIO` para el username). Verificación de contraseña con SHA2 antes de actualizar. |

---

### Vistas — Administrador

| Nombre | Archivo | Descripción |
|--------|---------|-------------|
| Panel admin | `panel_admin.php` | 6 tarjetas de estadísticas, top 5 médicos con barras proporcionales, últimos 10 accesos. |
| Empleados | `empleados_admin.php` | Tabla paginada (15/pág). 3 modales Bootstrap: nuevo, editar (relleno con `json_encode` + JS) y reset de contraseña. |
| Citas por médico | `citas_admin.php` | Ranking de citas, solo lectura. Barras proporcionales y avatares con iniciales. |
| Log de accesos | `logs_admin.php` | Tabla `LOG_ACCESO`, máx. 500 registros, paginada a 20. |

---

### Vistas — Médico

| Nombre | Archivo | Descripción |
|--------|---------|-------------|
| Panel médico | `panel_medico.php` | Estadísticas, FullCalendar (eventos cargados con `fetch()`), próximas citas y fecha dinámica con JS. |
| Mis citas | `citas_medico.php` | Filtros PHP con `array_filter`. Historial pre-cargado con `json_encode` y modal construido con template literals JS. |
| Mis pacientes | `pacientes_medico.php` | Búsqueda AJAX con `fetch()` + Enter. Historial pre-cargado con `json_encode(array_reduce(...))`. |
| Configuración | `configuracion_medico.php` | Dos tarjetas: datos personales y cambio de contraseña. Username en `disabled`. |

---

### Vistas — Paciente

| Nombre | Archivo | Descripción |
|--------|---------|-------------|
| Panel paciente | `panel_paciente.php` | Estadísticas, médico asignado y próximas citas (muestra "Pendiente de asignación" si no hay médico). |
| Mis citas | `citas_paciente.php` | Citas en 4 secciones con `array_filter` + arrow functions `fn()=>` (PHP 7.4+). |
| Pedir cita | `pedir_cita.php` | Formulario con `min=today`. Crea cita en estado `pendiente` sin médico asignado. |
| Mi historial | `historial_paciente.php` | Solo lectura. Filtros por tipo y fecha con `array_filter` en PHP. |
| Mi perfil | `perfil_paciente.php` | Editar teléfono, dirección y email. Nombre/DNI/NSS en `disabled`. |

---

### Vistas — Recepcionista

| Nombre | Archivo | Descripción |
|--------|---------|-------------|
| Panel recepcionista | `panel_recepcionista.php` | Cola de pendientes. Filtrado de médicos por especialidad con atributo `hidden` en PHP (sin JS). |
| Gestión de citas | `citas_recepcionista.php` | Multi-filtro, paginación PHP 15/pág, modales rellenos con `json_encode` + JS, select filtrado por `data-esp`. |

---

### Sidebars e includes

| Nombre | Archivo | Ítems del menú |
|--------|---------|----------------|
| Barra lateral médico | `sidebar_medico.php` | Dashboard, Mis Citas, Mis Pacientes, Configuración |
| Barra lateral admin | `sidebar_admin.php` | Dashboard, Empleados, Pacientes, Citas por médico, Log de accesos |
| Barra lateral paciente | `sidebar_paciente.php` | Dashboard, Mis Citas, Pedir Cita, Mi Historial, Mi Perfil |
| Barra lateral recepcionista | `sidebar_recepcionista.php` | Dashboard, Citas, Pacientes, Agenda |

Todos usan el helper `nav_activo()` para marcar el ítem activo. Estructura `.barra-lateral` definida en `panel_medico.css`.

---

### Hojas de estilo

| Nombre | Archivo | Descripción |
|--------|---------|-------------|
| Estilos landing | `index.css` | Navbar con `backdrop-filter: blur(12px)`, hero, registro. Define variables CSS globales (`--verde`, `--crema`…). |
| Estilos paneles | `panel_medico.css` | Base compartida por todos los paneles. `.barra-lateral` 260px fija, tarjetas, filtros, modales. |
| Estilos admin | `panel_admin.css` | Badges de rol, barras de progreso proporcionales, badges de estado. |
| Estilos paciente | `panel_paciente.css` | Tarjeta de médico asignado, filas de cita y etiquetas de estado. |
| Estilos citas | `citas_medico.css` | Filtros de fecha, tabla extendida, paginación. Compartido con logs y recepcionista. |
| Estilos pacientes médico | `pacientes_medico.css` | Entradas de historial, formulario de nueva entrada, cabecera de paciente. |
| Estilos configuración | `configuracion_medico.css` | Tarjetas de configuración y perfil, alertas de éxito/error. |

---

### Tecnologías usadas en `/app`

**PHP 8 (sin framework)** — MVC artesanal con `require_once`. Control de acceso por rol en cada controller con `$_SESSION`.

**PDO + MySQL** — Prepared statements en todas las queries. `SHA2(?,256)` para contraseñas, transacciones atómicas en el registro, `INSERT IGNORE` para evitar duplicados, `COALESCE` para unificar tipos de usuario.

**Bootstrap 5.3.3 + Bootstrap Icons 1.11.3** — CDN jsDelivr. Grid, modales, badges, navbar e iconografía en toda la app.

**Google Fonts** — Playfair Display (títulos) + DM Sans (cuerpo), cargadas desde CDN.

**CSS Custom Properties** — Variables `--verde`, `--crema`, `--borde`… definidas en `:root` y reutilizadas en todos los archivos CSS.

**FullCalendar** — CDN. Dashboard del médico. Consume `CalendarioController.php` como API REST JSON.

**JavaScript vanilla (ES6)** — `fetch()` para búsqueda AJAX, template literals para modales dinámicos, `bootstrap.Modal` programático, `toLocaleDateString` para fechas.

**`json_encode` PHP→JS** — Datos pre-cargados en servidor inyectados como constantes JS para evitar llamadas AJAX adicionales.

**PHP arrow functions (7.4+)** — `array_filter` + `fn($c) =>` en vistas de paciente.
