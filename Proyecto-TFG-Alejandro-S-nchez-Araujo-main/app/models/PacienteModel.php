<?php
class PacienteModel {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Pacientes asignados a este médico
    public function obtenerPacientesMedico($id_empleado) {
        $sql = "SELECT p.id_paciente, p.nombre, p.apellido, p.telefono,
                       mp.fecha_alta
                FROM MEDICO_PACIENTE mp
                JOIN PACIENTE p ON mp.id_paciente = p.id_paciente
                WHERE mp.id_empleado = ?
                ORDER BY p.apellido ASC, p.nombre ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_empleado]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Todos los pacientes para el select de añadir y lista recepcionista
    public function obtenerTodos() {
        $sql = "SELECT id_paciente, nombre, apellido, dni, telefono FROM PACIENTE ORDER BY apellido ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Asignar paciente a médico
    public function asignar($id_empleado, $id_paciente) {
        $sql  = "INSERT IGNORE INTO MEDICO_PACIENTE (id_empleado, id_paciente) VALUES (?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_empleado, $id_paciente]);
    }

    // Desasignar paciente de médico
    public function desasignar($id_empleado, $id_paciente) {
        $sql  = "DELETE FROM MEDICO_PACIENTE WHERE id_empleado = ? AND id_paciente = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_empleado, $id_paciente]);
    }

    // Historial clínico de un paciente
    public function obtenerHistorial($id_paciente) {
        $sql = "SELECT h.id_historial, h.fecha, h.motivo_consulta, h.tipo_consulta,
                       h.diagnostico, h.tratamiento, h.notas, h.fecha_registro,
                       e.nombre AS medico_nombre, e.apellido AS medico_apellido
                FROM HISTORIAL_CLINICO h
                JOIN EMPLEADO e ON h.id_empleado = e.id_empleado
                WHERE h.id_paciente = ?
                ORDER BY h.fecha DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_paciente]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Todas las entradas de historial del médico vista global
    public function obtenerTodoElHistorial($id_empleado) {
        $sql = "SELECT h.id_historial, h.fecha, h.motivo_consulta, h.tipo_consulta,
                       h.diagnostico, h.tratamiento, h.notas, h.fecha_registro,
                       p.nombre AS paciente_nombre, p.apellido AS paciente_apellido
                FROM HISTORIAL_CLINICO h
                JOIN PACIENTE p ON h.id_paciente = p.id_paciente
                WHERE h.id_empleado = ?
                ORDER BY h.fecha DESC, h.fecha_registro DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_empleado]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // MÉTODOS DE PERFIL DEL PACIENTE
    // Obtener datos del paciente
    public function obtenerPaciente($id_paciente) {
        $sql  = "SELECT * FROM PACIENTE WHERE id_paciente = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_paciente]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualizar datos personales del paciente
    public function actualizarPerfil($id_paciente, $datos) {
        $sql = "UPDATE PACIENTE SET telefono = ?, direccion = ?, email = ?
                WHERE id_paciente = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $datos['telefono'],
            $datos['direccion'],
            $datos['email'],
            $id_paciente
        ]);
    }

    // Verificar contraseña actual del paciente
    public function verificarPasswordPaciente($id_paciente, $password) {
        $sql  = "SELECT id_usuario FROM USUARIO
                 WHERE id_paciente = ? AND password = SHA2(?, 256)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_paciente, $password]);
        return $stmt->fetch() !== false;
    }

    // Actualizar contraseña del paciente
    public function actualizarPasswordPaciente($id_paciente, $nueva) {
        $sql  = "UPDATE USUARIO SET password = SHA2(?, 256) WHERE id_paciente = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$nueva, $id_paciente]);
    }

    // Crear nuevo paciente y devolver su id
    public function crear($datos) {
        $sql = "INSERT INTO PACIENTE (nombre, apellido, fecha_nacimiento, genero, direccion, telefono, email, dni, num_seguridad_social)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $datos['nombre'],
            $datos['apellido'],
            $datos['fecha_nacimiento'] ?: null,
            $datos['genero']           ?: null,
            $datos['direccion']        ?: null,
            $datos['telefono']         ?: null,
            $datos['email']            ?: null,
            $datos['dni']              ?: null,
            $datos['num_seguridad_social'] ?: null
        ]);
        return $this->pdo->lastInsertId();
    }

    // Buscar paciente por teléfono o DNI
    public function buscarPorTelODni($q) {
        $like = '%' . $q . '%';
        $sql  = "SELECT id_paciente, nombre, apellido, dni, telefono, email
                 FROM PACIENTE
                 WHERE dni = ? OR telefono LIKE ?
                 LIMIT 10";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$q, $like]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Añadir entrada al historial
    public function agregarHistorial($datos) {
        $sql = "INSERT INTO HISTORIAL_CLINICO (id_paciente, id_empleado, fecha, motivo_consulta, tipo_consulta, diagnostico, tratamiento, notas)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $datos['id_paciente'],
            $datos['id_empleado'],
            $datos['fecha'],
            $datos['motivo_consulta'],
            $datos['tipo_consulta'],
            $datos['diagnostico'],
            $datos['tratamiento'],
            $datos['notas']
        ]);
    }
}
