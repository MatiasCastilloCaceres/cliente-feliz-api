
<?php
class OfertaLaboral {
    private $db;
    public function __construct() {
        $this->db = new PDO("mysql:host=localhost;dbname=cliente_feliz", "root", "");
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getVigentes() {
        $stmt = $this->db->prepare("SELECT * FROM OfertaLaboral WHERE estado = 'Vigente'");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByUbicacion($ubicacion) {
        $stmt = $this->db->prepare("SELECT * FROM OfertaLaboral WHERE ubicacion LIKE ?");
        $stmt->execute(["%$ubicacion%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByTipoContrato($tipo) {
        $stmt = $this->db->prepare("SELECT * FROM OfertaLaboral WHERE tipo_contrato = ?");
        $stmt->execute([$tipo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM OfertaLaboral WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
