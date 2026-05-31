<?php
class CitaModel {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Citas de hoy para las tarjetas de estado
    public function obtenerCitasMedico($id_empleado) {
        $sql = "SELECT c.id_cita, c.hora_cita, c.fecha_cita, c.estado,
                       p.nombre, p.apellido
                FROM CITA c
                JOIN PACIENTE p ON c.id_paciente = p.id_paciente
                WHERE c.id_empleado_sanitario = ?
                AND c.fecha_cita = CURDATE()
                ORDER BY c.hora_cita ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_empleado]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Todas las citas del médico desde hoy en adelante
    public function obtenerTodasCitasMedico($id_empleado) {
        $sql = "SELECT c.id_cita, c.id_paciente, c.fecha_cita, c.hora_cita, c.estado,
                       p.nombre, p.apellido
                FROM CITA c
                JOIN PACIENTE p ON c.id_paciente = p.id_paciente
                WHERE c.id_empleado_sanitario = ?
                AND c.fecha_cita >= CURDATE()
                ORDER BY c.fecha_cita ASC, c.hora_cita ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_empleado]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Proximas citas (esta semana) para la tabla lateral
    public function obtenerProximasCitas($id_empleado) {
        $sql = "SELECT c.fecha_cita, c.hora_cita, c.estado,
                       p.nombre, p.apellido
                FROM CITA c
                JOIN PACIENTE p ON c.id_paciente = p.id_paciente
                WHERE c.id_empleado_sanitario = ?
                AND c.fecha_cita BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                ORDER BY c.fecha_cita ASC, c.hora_cita ASC
                LIMIT 6";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_empleado]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Nmero de pacientes distintos del médico
    public function contarPacientesMedico($id_empleado) {
        $sql = "SELECT COUNT(DISTINCT id_paciente) as total
                FROM CITA
                WHERE id_empleado_sanitario = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_empleado]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // Obtener todos los pacientes (para el select del formulario)
    public function obtenerPacientes() {
        $sql = "SELECT id_paciente, nombre, apellido FROM PACIENTE ORDER BY apellido ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Crear una cita nueva
    public function crear($datos) {
        $sql = "INSERT INTO CITA (id_paciente, id_empleado_sanitario, fecha_cita, hora_cita, estado)
                VALUES (?, ?, ?, ?, 'programada')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $datos['id_paciente'],
            $datos['id_empleado'],
            $datos['fecha_cita'],
            $datos['hora_cita']
        ]);
    }

    // Actualizar una cita existente
    public function actualizar($id, $datos) {
        $sql = "UPDATE CITA SET fecha_cita = ?, hora_cita = ?, estado = ?
                WHERE id_cita = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $datos['fecha_cita'],
            $datos['hora_cita'],
            $datos['estado'],
            $id
        ]);
    }

    // Eliminar una cita
    public function eliminar($id) {
        $sql  = "DELETE FROM CITA WHERE id_cita = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
    }

    // MÉTODOS PARA PACIENTE
    // Todas las citas del paciente  um LEFT JOIN para incluir las sin médico asignado
    public function obtenerCitasPaciente($id_paciente) {
        $sql = "SELECT c.id_cita, c.fecha_cita, c.hora_cita, c.estado, c.motivo,
                       e.nombre AS medico_nombre, e.apellido AS medico_apellido,
                       esp.nombre AS especialidad
                FROM CITA c
                LEFT JOIN EMPLEADO e        ON c.id_empleado_sanitario = e.id_empleado
                LEFT JOIN ESPECIALIDAD esp  ON e.id_especialidad = esp.id_especialidad
                WHERE c.id_paciente = ?
                ORDER BY c.fecha_cita DESC, c.hora_cita DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_paciente]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Próximas citas del paciente 
    public function obtenerProximasCitasPaciente($id_paciente) {
        $sql = "SELECT c.fecha_cita, c.hora_cita, c.estado,
                       e.nombre AS medico_nombre, e.apellido AS medico_apellido,
                       COALESCE(esp_med.nombre, esp_cita.nombre) AS especialidad
                FROM CITA c
                LEFT JOIN EMPLEADO e          ON c.id_empleado_sanitario = e.id_empleado
                LEFT JOIN ESPECIALIDAD esp_med  ON e.id_especialidad       = esp_med.id_especialidad
                LEFT JOIN ESPECIALIDAD esp_cita ON c.id_especialidad        = esp_cita.id_especialidad
                WHERE c.id_paciente = ?
                AND c.fecha_cita >= CURDATE()
                AND c.estado NOT IN ('cancelada')
                ORDER BY c.fecha_cita ASC, c.hora_cita ASC
                LIMIT 5";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_paciente]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Médico asignado al paciente
    public function obtenerMedicoAsignado($id_paciente) {
        $sql = "SELECT e.nombre, e.apellido, esp.nombre AS especialidad
                FROM MEDICO_PACIENTE mp
                JOIN EMPLEADO e ON mp.id_empleado = e.id_empleado
                LEFT JOIN ESPECIALIDAD esp ON e.id_especialidad = esp.id_especialidad
                WHERE mp.id_paciente = ?
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_paciente]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Especialidades médicas 
    public function obtenerEspecialidades() {
        $sql  = "SELECT id_especialidad, nombre FROM ESPECIALIDAD
                 WHERE nombre NOT IN ('Enfermería', 'Análisis Clínicos')
                 ORDER BY nombre ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Médicos de una especialidad para solicitar cita
    public function obtenerMedicosPorEspecialidad($id_especialidad) {
        $sql = "SELECT e.id_empleado, e.nombre, e.apellido
                FROM EMPLEADO e
                WHERE e.id_especialidad = ? AND e.rol = 'medico'
                ORDER BY e.apellido ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_especialidad]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // MÉTODOS PARA RECEPCIONISTA
    // Todas las citas con paciente, médico, especialidad y motivo
    public function obtenerTodasCitas() {
        $sql = "SELECT c.id_cita, c.id_paciente, c.fecha_cita, c.hora_cita, c.estado, c.motivo,
                       c.id_especialidad AS cita_especialidad_id,
                       esp_cita.nombre   AS cita_especialidad_nombre,
                       p.nombre AS paciente_nombre, p.apellido AS paciente_apellido,
                       e.nombre AS medico_nombre,   e.apellido AS medico_apellido,
                       e.id_empleado,
                       esp_med.id_especialidad AS medico_especialidad_id
                FROM CITA c
                JOIN PACIENTE p ON c.id_paciente = p.id_paciente
                LEFT JOIN EMPLEADO e       ON c.id_empleado_sanitario = e.id_empleado
                LEFT JOIN ESPECIALIDAD esp_med  ON e.id_especialidad   = esp_med.id_especialidad
                LEFT JOIN ESPECIALIDAD esp_cita ON c.id_especialidad   = esp_cita.id_especialidad
                ORDER BY c.fecha_cita DESC, c.hora_cita ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Citas sin médico asignado
    public function obtenerCitasSinMedico() {
        $sql = "SELECT c.id_cita, c.id_paciente, c.fecha_cita, c.hora_cita, c.estado, c.motivo,
                       c.id_especialidad,
                       esp.nombre AS especialidad_nombre,
                       p.nombre AS paciente_nombre, p.apellido AS paciente_apellido
                FROM CITA c
                JOIN PACIENTE p ON c.id_paciente = p.id_paciente
                LEFT JOIN ESPECIALIDAD esp ON c.id_especialidad = esp.id_especialidad
                WHERE c.id_empleado_sanitario IS NULL
                ORDER BY c.fecha_cita ASC, c.hora_cita ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Todas las citas de hoy
    public function obtenerCitasHoyTodas() {
        $sql = "SELECT c.id_cita, c.hora_cita, c.estado, c.motivo,
                       p.nombre AS paciente_nombre, p.apellido AS paciente_apellido,
                       e.nombre AS medico_nombre,   e.apellido AS medico_apellido
                FROM CITA c
                JOIN PACIENTE p ON c.id_paciente = p.id_paciente
                LEFT JOIN EMPLEADO e ON c.id_empleado_sanitario = e.id_empleado
                WHERE c.fecha_cita = CURDATE()
                ORDER BY c.hora_cita ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Todos los médicos para asignar
    public function obtenerMedicos() {
        $sql = "SELECT id_empleado, nombre, apellido FROM EMPLEADO
                WHERE rol = 'medico' ORDER BY apellido ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Todos los médicos con su especialidad para filtralo
    public function obtenerMedicosConEspecialidad() {
        $sql = "SELECT e.id_empleado, e.nombre, e.apellido,
                       COALESCE(esp.id_especialidad, 0)  AS id_especialidad,
                       COALESCE(esp.nombre, 'Sin especialidad') AS especialidad
                FROM EMPLEADO e
                LEFT JOIN ESPECIALIDAD esp ON e.id_especialidad = esp.id_especialidad
                WHERE e.rol = 'medico'
                ORDER BY esp.nombre ASC, e.apellido ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Asignar médico a una cita
    public function asignarMedico($id_cita, $id_empleado) {
        $sql  = "UPDATE CITA SET id_empleado_sanitario = ? WHERE id_cita = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_empleado, $id_cita]);
    }

    // Si la cita estaba pendiente pasarla a programada
    public function programarSiPendiente($id_cita) {
        $sql  = "UPDATE CITA SET estado = 'programada' WHERE id_cita = ? AND estado = 'pendiente'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_cita]);
    }

    // Crear cita desde recepcionista con médico y motivo
    public function crearRecepcionista($datos) {
        $sql = "INSERT INTO CITA (id_paciente, id_empleado_sanitario, fecha_cita, hora_cita, estado, motivo)
                VALUES (?, ?, ?, ?, 'programada', ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $datos['id_paciente'],
            $datos['id_empleado'] ?: null,
            $datos['fecha_cita'],
            $datos['hora_cita'],
            $datos['motivo'] ?? null
        ]);
    }

    // Actualizar cita completa recepcionista puede cambiar médico también
    public function actualizarCompleta($id, $datos) {
        $sql = "UPDATE CITA SET id_empleado_sanitario = ?, fecha_cita = ?, hora_cita = ?, estado = ?, motivo = ?
                WHERE id_cita = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $datos['id_empleado'] ?: null,
            $datos['fecha_cita'],
            $datos['hora_cita'],
            $datos['estado'],
            $datos['motivo'] ?? null,
            $id
        ]);
    }

    // Solicitar nueva cita desde el paciente
    public function solicitarCita($datos) {
        $sql = "INSERT INTO CITA (id_paciente, id_empleado_sanitario, fecha_cita, hora_cita, estado, motivo, id_especialidad)
                VALUES (?, NULL, ?, ?, 'pendiente', ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $datos['id_paciente'],
            $datos['fecha_cita'],
            $datos['hora_cita'],
            $datos['motivo'] ?? null,
            $datos['id_especialidad'] ?? null
        ]);
    }

    // Completar cita desde el médico solo si le pertenece y está programada
    public function completarCitaMedico($id_cita, $id_empleado) {
        $sql  = "UPDATE CITA SET estado = 'completada'
                 WHERE id_cita = ? AND id_empleado_sanitario = ?
                 AND estado = 'programada'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_cita, $id_empleado]);
        return $stmt->rowCount() > 0;
    }

    // Cancelar cita desde el paciente solo si le pertenece y está pendiente/programada
    public function cancelarCitaPaciente($id_cita, $id_paciente) {
        $sql  = "UPDATE CITA SET estado = 'cancelada'
                 WHERE id_cita = ? AND id_paciente = ?
                 AND estado IN ('pendiente', 'programada')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_cita, $id_paciente]);
    }
}
