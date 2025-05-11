<?php
namespace Controllers;

use Utils\Response;

class AntecedenteAcademicoController {
    private $db;

    public function __construct() {
        $database = new \Database();
        $this->db = $database->getConnection();
    }

    public function getAntecedentesAcademicos() {
        $query = "SELECT * FROM AntecedenteAcademico";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        Response::json(['success' => true, 'data' => $result]);
    }

    public function createAntecedenteAcademico() {
        $data = json_decode(file_get_contents("php://input"), true);
        $query = "INSERT INTO AntecedenteAcademico (candidato_id, institucion, titulo_obtenido, anio_ingreso, anio_egreso)
                  VALUES (:candidato_id, :institucion, :titulo_obtenido, :anio_ingreso, :anio_egreso)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':candidato_id' => $data['candidato_id'],
            ':institucion' => $data['institucion'],
            ':titulo_obtenido' => $data['titulo_obtenido'],
            ':anio_ingreso' => $data['anio_ingreso'],
            ':anio_egreso' => $data['anio_egreso']
        ]);
        Response::json(['success' => true, 'message' => 'Antecedente académico creado.']);
    }
}
?>
