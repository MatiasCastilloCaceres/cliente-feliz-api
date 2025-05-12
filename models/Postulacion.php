<?php
// filepath: c:\xampp\htdocs\cliente-feliz-api\models\Postulacion.php
require_once __DIR__ . '/../config/database.php';

class Postulacion
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Obtener todas las postulaciones
    public function getAll()
    {
        $query = "SELECT p.*, o.titulo as titulo_oferta, u.nombre as nombre_candidato 
                 FROM Postulacion p
                 JOIN OfertaLaboral o ON p.oferta_laboral_id = o.id
                 JOIN Usuario u ON p.candidato_id = u.id
                 ORDER BY p.fecha_postulacion DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $postulaciones = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $postulaciones[] = $row;
        }

        return $postulaciones;
    }

    // Obtener postulaciones por candidato
    public function getByCandidato($candidatoId)
    {
        $query = "SELECT p.*, o.titulo as titulo_oferta, o.descripcion as descripcion_oferta, 
                 o.ubicacion, o.salario, o.tipo_contrato, o.fecha_publicacion, o.fecha_cierre,
                 u.nombre as nombre_reclutador, u.email as email_reclutador
                 FROM Postulacion p
                 JOIN OfertaLaboral o ON p.oferta_laboral_id = o.id
                 JOIN Usuario u ON o.reclutador_id = u.id
                 WHERE p.candidato_id = :candidato_id
                 ORDER BY p.fecha_postulacion DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':candidato_id', $candidatoId, PDO::PARAM_INT);
        $stmt->execute();

        $postulaciones = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $postulaciones[] = $row;
        }

        return $postulaciones;
    }

    // Obtener postulaciones por oferta
    public function getByOferta($ofertaId)
    {
        $query = "SELECT p.*, u.nombre as nombre_candidato, u.email as email_candidato, 
                 u.telefono, u.curriculum_url, u.linkedIn_url
                 FROM Postulacion p
                 JOIN Usuario u ON p.candidato_id = u.id
                 WHERE p.oferta_laboral_id = :oferta_id
                 ORDER BY p.fecha_postulacion DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':oferta_id', $ofertaId, PDO::PARAM_INT);
        $stmt->execute();

        $postulaciones = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $postulaciones[] = $row;
        }

        return $postulaciones;
    }

    // Crear una nueva postulación
    public function crear($data)
    {
        $query = "INSERT INTO Postulacion 
                 (candidato_id, oferta_laboral_id, comentario, fecha_postulacion, estado) 
                 VALUES 
                 (:candidato_id, :oferta_laboral_id, :comentario, CURRENT_TIMESTAMP, 'Pendiente')";

        $comentario = isset($data['comentario']) ? $data['comentario'] : '';

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':candidato_id', $data['candidato_id'], PDO::PARAM_INT);
        $stmt->bindParam(':oferta_laboral_id', $data['oferta_laboral_id'], PDO::PARAM_INT);
        $stmt->bindParam(':comentario', $comentario, PDO::PARAM_STR);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    // Actualizar estado de postulación
    public function actualizarEstado($id, $estado)
    {
        $query = "UPDATE Postulacion SET estado = :estado WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Actualizar comentario de postulación
    public function actualizarComentario($id, $comentario)
    {
        $query = "UPDATE Postulacion SET comentario = :comentario WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':comentario', $comentario, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }
}
?>