<?php
class AdminModel {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // RESUMEN PARA EL DASHBOARD
    public function obtenerResumen() {
        $datos = [];

        // total_empleados 
        try {
            $datos['total_empleados'] = $this->pdo
                ->query("SELECT COUNT(*) FROM EMPLEADO WHERE activo = 1")
                ->fetchColumn();
        } catch (Exception $e) {
            $datos['total_empleados'] = $this->pdo
                ->query("SELECT COUNT(*) FROM EMPLEADO")
                ->fetchColumn();
        }

        // total_medicos
        try {
            $datos['total_medicos'] = $this->pdo
                ->query("SELECT COUNT(*) FROM EMPLEADO WHERE rol = 'medico' AND activo = 1")
                ->fetchColumn();
        } catch (Exception $e) {
            $datos['total_medicos'] = $this->pdo
                ->query("SELECT COUNT(*) FROM EMPLEADO WHERE rol = 'medico'")
                ->fetchColumn();
        }

        $datos['total_pacientes'] = $this->pdo
            ->query("SELECT COUNT(*) FROM PACIENTE")
            ->fetchColumn();

        $datos['citas_hoy'] = $this->pdo
            ->query("SELECT COUNT(*) FROM CITA WHERE fecha_cita = CURDATE()")
            ->fetchColumn();

        $datos['citas_pendientes'] = $this->pdo
            ->query("SELECT COUNT(*) FROM CITA WHERE estado = 'pendiente'")
            ->fetchColumn();

        
        try {
            $datos['accesos_hoy'] = $this->pdo
                ->query("SELECT COUNT(*) FROM LOG_ACCESO WHERE DATE(fecha) = CURDATE()")
                ->fetchColumn();
        } catch (Exception $e) {
            $datos['accesos_hoy'] = 0;
        }

        return $datos;
    }

    // EMPLEADOS
    public function obtenerEmpleados() {
        
        try {
            $sql = "SELECT e.id_empleado, e.nombre, e.apellido, e.rol, e.salario,
                           e.fecha_contratacion, e.activo,
                           d.nombre AS departamento,
                           esp.nombre AS especialidad,
                           u.username, u.id_usuario
                    FROM EMPLEADO e
                    LEFT JOIN DEPARTAMENTO d   ON e.id_departamento  = d.id_departamento
                    LEFT JOIN ESPECIALIDAD esp ON e.id_especialidad   = esp.id_especialidad
                    LEFT JOIN USUARIO u        ON u.id_empleado       = e.id_empleado
                    ORDER BY e.activo DESC, e.apellido ASC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $sql = "SELECT e.id_empleado, e.nombre, e.apellido, e.rol, e.salario,
                           e.fecha_contratacion, 1 AS activo,
                           d.nombre AS departamento,
                           esp.nombre AS especialidad,
                           u.username, u.id_usuario
                    FROM EMPLEADO e
                    LEFT JOIN DEPARTAMENTO d   ON e.id_departamento  = d.id_departamento
                    LEFT JOIN ESPECIALIDAD esp ON e.id_especialidad   = esp.id_especialidad
                    LEFT JOIN USUARIO u        ON u.id_empleado       = e.id_empleado
                    ORDER BY e.apellido ASC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    public function obtenerEmpleado($id) {
        $sql = "SELECT e.*, u.username, u.id_usuario
                FROM EMPLEADO e
                LEFT JOIN USUARIO u ON u.id_empleado = e.id_empleado
                WHERE e.id_empleado = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crearEmpleado($datos) {
        // Insertar en EMPLEADO
        $sql = "INSERT INTO EMPLEADO (nombre, apellido, fecha_contratacion, salario, rol, id_departamento, id_especialidad, activo)
                VALUES (?, ?, ?, ?, ?, ?, ?, 1)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $datos['nombre'],
            $datos['apellido'],
            $datos['fecha_contratacion'] ?: null,
            $datos['salario'] ?: null,
            $datos['rol'],
            $datos['id_departamento'] ?: null,
            $datos['id_especialidad'] ?: null,
        ]);
        $id_empleado = $this->pdo->lastInsertId();

        // Insertar en USUARIO si se proporciona usuario y contraseña
        if (!empty($datos['username']) && !empty($datos['password'])) {
            $sqlU = "INSERT INTO USUARIO (username, password, rol, id_empleado)
                     VALUES (?, SHA2(?, 256), ?, ?)";
            $stmtU = $this->pdo->prepare($sqlU);
            $stmtU->execute([$datos['username'], $datos['password'], $datos['rol'], $id_empleado]);
        }

        return $id_empleado;
    }

    public function actualizarEmpleado($id, $datos) {
        $sql = "UPDATE EMPLEADO
                SET nombre = ?, apellido = ?, fecha_contratacion = ?, salario = ?,
                    rol = ?, id_departamento = ?, id_especialidad = ?
                WHERE id_empleado = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $datos['nombre'],
            $datos['apellido'],
            $datos['fecha_contratacion'] ?: null,
            $datos['salario'] ?: null,
            $datos['rol'],
            $datos['id_departamento'] ?: null,
            $datos['id_especialidad'] ?: null,
            $id
        ]);

        // Actualiza rol en USUARIO también
        $sqlU = "UPDATE USUARIO SET rol = ? WHERE id_empleado = ?";
        $stmtU = $this->pdo->prepare($sqlU);
        $stmtU->execute([$datos['rol'], $id]);
    }

    public function toggleActivo($id, $activo) {
        try {
            $sql  = "UPDATE EMPLEADO SET activo = ? WHERE id_empleado = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([(int)$activo, $id]);
        } catch (Exception $e) {
            
        }
    }

    public function resetPassword($id_empleado, $nueva_password) {
        $sql  = "UPDATE USUARIO SET password = SHA2(?, 256) WHERE id_empleado = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$nueva_password, $id_empleado]);
        return $stmt->rowCount() > 0;
    }

    // PACIENTES
    public function obtenerPacientes() {
        $sql = "SELECT p.id_paciente, p.nombre, p.apellido, p.fecha_nacimiento, p.genero,
                       p.telefono, p.email, p.dni,
                       MAX(u.username) AS username,
                       COUNT(c.id_cita) AS total_citas
                FROM PACIENTE p
                LEFT JOIN USUARIO u ON u.id_paciente = p.id_paciente
                LEFT JOIN CITA c    ON c.id_paciente  = p.id_paciente
                GROUP BY p.id_paciente, p.nombre, p.apellido, p.fecha_nacimiento,
                         p.genero, p.telefono, p.email, p.dni
                ORDER BY p.apellido ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // CITAS POR MÉDICO
    public function obtenerCitasPorMedico() {
        try {
            $sql = "SELECT e.id_empleado, e.nombre, e.apellido,
                           esp.nombre AS especialidad,
                           COUNT(c.id_cita)             AS total,
                           SUM(c.estado = 'programada') AS programadas,
                           SUM(c.estado = 'completada') AS completadas,
                           SUM(c.estado = 'cancelada')  AS canceladas,
                           SUM(c.estado = 'pendiente')  AS pendientes
                    FROM EMPLEADO e
                    LEFT JOIN ESPECIALIDAD esp ON e.id_especialidad       = esp.id_especialidad
                    LEFT JOIN CITA c           ON c.id_empleado_sanitario = e.id_empleado
                    WHERE e.rol = 'medico' AND e.activo = 1
                    GROUP BY e.id_empleado, e.nombre, e.apellido, esp.nombre
                    ORDER BY total DESC, e.apellido ASC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {

            
            $sql = "SELECT e.id_empleado, e.nombre, e.apellido,
                           esp.nombre AS especialidad,
                           COUNT(c.id_cita)             AS total,
                           SUM(c.estado = 'programada') AS programadas,
                           SUM(c.estado = 'completada') AS completadas,
                           SUM(c.estado = 'cancelada')  AS canceladas,
                           SUM(c.estado = 'pendiente')  AS pendientes
                    FROM EMPLEADO e
                    LEFT JOIN ESPECIALIDAD esp ON e.id_especialidad       = esp.id_especialidad
                    LEFT JOIN CITA c           ON c.id_empleado_sanitario = e.id_empleado
                    WHERE e.rol = 'medico'
                    GROUP BY e.id_empleado, e.nombre, e.apellido, esp.nombre
                    ORDER BY total DESC, e.apellido ASC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    // LOG DE ACCESOS
    public function obtenerLogs($limite = 200) {
        $limite = (int)$limite;
        $sql    = "SELECT l.id_log, l.username, l.rol, l.ip, l.fecha
                   FROM LOG_ACCESO l
                   ORDER BY l.fecha DESC
                   LIMIT $limite";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // CREAR PACIENTE
    public function crearPaciente($datos) {
        $sql = "INSERT INTO PACIENTE (nombre, apellido, dni, telefono, email, num_seguridad_social, fecha_nacimiento, genero, direccion)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $datos['nombre'],
            $datos['apellido'],
            $datos['dni']                  ?: null,
            $datos['telefono']             ?: null,
            $datos['email']                ?: null,
            $datos['num_seguridad_social'] ?: null,
            $datos['fecha_nacimiento']     ?: null,
            $datos['genero']               ?: null,
            $datos['direccion']            ?: null,
        ]);
        return $this->pdo->lastInsertId();
    }

    // CATÁLOGOS
    public function obtenerEspecialidades() {
        $sql  = "SELECT id_especialidad, nombre FROM ESPECIALIDAD ORDER BY nombre ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerDepartamentos() {
        $sql  = "SELECT id_departamento, nombre FROM DEPARTAMENTO ORDER BY nombre ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
