
<?php
class Usuario {
    private $db;
    public function __construct() {
        $this->db = new PDO("mysql:host=localhost;dbname=cliente_feliz", "root", "");
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function crear($data) {
        $sql = "INSERT INTO Usuario (nombre, apellido, email, contraseña, rol) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['nombre'], $data['apellido'], $data['email'],
            password_hash($data['contraseña'], PASSWORD_DEFAULT),
            $data['rol']
        ]);
    }
}
?>
