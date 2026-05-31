<?php
// API que devuelve las citas del médico en formato JSON para FullCalendar
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'medico') {
    http_response_code(403);
    echo json_encode([]);
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/CitaModel.php';

$modelo = new CitaModel($pdo);
$citas  = $modelo->obtenerTodasCitasMedico($_SESSION['id_empleado']);

$eventos = [];
foreach ($citas as $cita) {
    // Color según estado
    if ($cita['estado'] === 'programada')  $color = '#856404';
    elseif ($cita['estado'] === 'completada') $color = '#0a6e5c';
    else $color = '#842029';

    $eventos[] = [
        'id'    => $cita['id_cita'],
        'title' => $cita['nombre'] . ' ' . $cita['apellido'],
        'start' => $cita['fecha_cita'] . 'T' . $cita['hora_cita'],
        'color' => $color,
        'extendedProps' => [
            'estado' => $cita['estado']
        ]
    ];
}

header('Content-Type: application/json');
echo json_encode($eventos);
