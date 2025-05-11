<?php
namespace Controllers;

use Utils\Response;

class AntecedenteLaboralController {
    private $db;

    public function __construct() {
        $database = new \Database();
        $this->db = $database->getConnection();
    }

    public function getAntecedentesLaborales() {
        $query = "SELECT * FROM AntecedenteLaboral";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        Response::json(['success' => true, 'data' => $result]);
    }

    public function createAntecedenteLaboral() {
        $data = json_decode(file_get_contents("php://input"), true);
        $query = "INSERT INTO AntecedenteLaboral (candidato_id, empresa, cargo, funciones, fecha_inicio, fecha_termino)
                  VALUES (:candidato_id, :empresa, :cargo, :funciones, :fecha_inicio, :fecha_termino)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':candidato_id' => $data['candidato_id'],
            ':empresa' => $data['empresa'],
            ':cargo' => $data['cargo'],
            ':funciones' => $data['funciones'],
            ':fecha_inicio' => $data['fecha_inicio'],
            ':fecha_termino' => $data['fecha_termino']
        ]);
        Response::json(['success' => true, 'message' => 'Antecedente laboral creado.']);
    }
}
?>
