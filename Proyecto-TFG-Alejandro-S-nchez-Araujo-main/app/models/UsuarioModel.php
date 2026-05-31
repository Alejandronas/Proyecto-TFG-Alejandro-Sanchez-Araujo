<?php
class UsuarioModel {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function buscarPorCredenciales($username, $password) {
        $sql = "SELECT u.*,
                       COALESCE(e.nombre, p.nombre)   AS nombre_real,
                       COALESCE(e.apellido, p.apellido) AS apellido_real
                FROM USUARIO u
                LEFT JOIN EMPLEADO e ON u.id_empleado = e.id_empleado
                LEFT JOIN PACIENTE p ON u.id_paciente = p.id_paciente
                WHERE u.username = ? AND u.password = SHA2(?, 256)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$username, $password]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
