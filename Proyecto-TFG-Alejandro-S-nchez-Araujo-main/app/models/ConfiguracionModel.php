<?php
class ConfiguracionModel {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Datos del médico empleado + usuario
    public function obtenerMedico($id_empleado) {
        $sql = "SELECT e.id_empleado, e.nombre, e.apellido, e.id_especialidad,
                       u.username
                FROM EMPLEADO e
                JOIN USUARIO u ON u.id_empleado = e.id_empleado
                WHERE e.id_empleado = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_empleado]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Lista de especialidades para el select
    public function obtenerEspecialidades() {
        $sql  = "SELECT id_especialidad, nombre FROM ESPECIALIDAD ORDER BY nombre ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Actualizar datos personales y especialidad
    public function actualizarDatos($id_empleado, $datos) {
        $sql  = "UPDATE EMPLEADO SET nombre = ?, apellido = ?, id_especialidad = ?
                 WHERE id_empleado = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $datos['nombre'],
            $datos['apellido'],
            $datos['id_especialidad'] ?: null,
            $id_empleado
        ]);
    }

    // Comprobar si la contraseña actual es correcta
    public function verificarPassword($id_empleado, $password) {
        $sql  = "SELECT id_usuario FROM USUARIO
                 WHERE id_empleado = ? AND password = SHA2(?, 256)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_empleado, $password]);
        return $stmt->fetch() !== false;
    }

    // Actualizar contraseña
    public function actualizarPassword($id_empleado, $nueva) {
        $sql  = "UPDATE USUARIO SET password = SHA2(?, 256) WHERE id_empleado = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$nueva, $id_empleado]);
    }
}
