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

    // Obtener postulación por ID
    public function getById($id)
    {
        $query = "SELECT p.*, o.titulo as titulo_oferta, u.nombre as nombre_candidato 
                 FROM Postulacion p
                 JOIN OfertaLaboral o ON p.oferta_laboral_id = o.id
                 JOIN Usuario u ON p.candidato_id = u.id
                 WHERE p.id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener postulaciones por candidato
    public function getPorCandidato($candidatoId)
    {
        // Primero verificamos si el candidato existe
        $queryUsuario = "SELECT * FROM Usuario WHERE id = :candidato_id";
        $stmtUsuario = $this->conn->prepare($queryUsuario);
        $stmtUsuario->bindParam(':candidato_id', $candidatoId, PDO::PARAM_INT);
        $stmtUsuario->execute();

        if ($stmtUsuario->rowCount() == 0) {
            throw new Exception('El candidato con ID ' . $candidatoId . ' no existe');
        }

        // Consulta detallada con JOIN para obtener más información sobre las postulaciones
        $query = "SELECT p.*, o.titulo as titulo_oferta, o.ubicacion, o.tipo_contrato, o.salario
                 FROM Postulacion p
                 JOIN OfertaLaboral o ON p.oferta_laboral_id = o.id
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
    public function getPorOferta($ofertaId)
    {
        // Primero verificamos si la oferta existe
        $queryOferta = "SELECT * FROM OfertaLaboral WHERE id = :oferta_id";
        $stmtOferta = $this->conn->prepare($queryOferta);
        $stmtOferta->bindParam(':oferta_id', $ofertaId, PDO::PARAM_INT);
        $stmtOferta->execute();

        if ($stmtOferta->rowCount() == 0) {
            throw new Exception('La oferta laboral con ID ' . $ofertaId . ' no existe');
        }

        // Consulta detallada con JOIN para obtener información de los candidatos
        $query = "SELECT p.*, u.nombre, u.apellido, u.email, u.telefono
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

    // Verificar si ya existe una postulación para un candidato y oferta
    public function verificarExistente($candidatoId, $ofertaId)
    {
        $query = "SELECT * FROM Postulacion 
                 WHERE candidato_id = :candidato_id 
                 AND oferta_laboral_id = :oferta_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':candidato_id', $candidatoId, PDO::PARAM_INT);
        $stmt->bindParam(':oferta_id', $ofertaId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    // Crear nueva postulación
    public function crear($data)
    {
        $query = "INSERT INTO Postulacion 
                 (candidato_id, oferta_laboral_id, estado_postulacion, comentario) 
                 VALUES 
                 (:candidato_id, :oferta_laboral_id, :estado_postulacion, :comentario)";

        // Valor por defecto si no se proporciona
        $estado = isset($data['estado_postulacion']) ? $data['estado_postulacion'] : 'Postulando';
        $comentario = isset($data['comentario']) ? $data['comentario'] : null;

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':candidato_id', $data['candidato_id'], PDO::PARAM_INT);
        $stmt->bindParam(':oferta_laboral_id', $data['oferta_laboral_id'], PDO::PARAM_INT);
        $stmt->bindParam(':estado_postulacion', $estado, PDO::PARAM_STR);
        $stmt->bindParam(':comentario', $comentario, PDO::PARAM_STR);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    // Actualizar estado de postulación
    public function actualizarEstado($id, $estado)
    {
        $query = "UPDATE Postulacion SET estado_postulacion = :estado WHERE id = :id";

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