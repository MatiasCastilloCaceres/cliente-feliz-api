<?php
require_once __DIR__ . '/../config/database.php';

class Postulacion
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAll()
    {
        try {
            if ($this->conn === null) {
                throw new PDOException("Error de conexión a la base de datos");
            }

            $query = "SELECT p.*, o.titulo as titulo_oferta, u.nombre as nombre_candidato 
                     FROM Postulacion p
                     JOIN OfertaLaboral o ON p.oferta_laboral_id = o.id
                     JOIN Usuario u ON p.candidato_id = u.id";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $postulaciones = [];

            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $postulaciones[] = [
                        'id' => $row['id'],
                        'candidato_id' => $row['candidato_id'],
                        'nombre_candidato' => $row['nombre_candidato'],
                        'oferta_id' => $row['oferta_laboral_id'],
                        'titulo_oferta' => $row['titulo_oferta'],
                        'estado_postulacion' => $row['estado_postulacion'],
                        'fecha_postulacion' => $row['fecha_postulacion'],
                        'comentario' => $row['comentario']
                    ];
                }
            }

            return $postulaciones;
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }

    public function getPorCandidato($candidatoId)
    {
        try {
            if ($this->conn === null) {
                throw new PDOException("Error de conexión a la base de datos");
            }

            // Primero verificamos si el candidato existe
            $queryUsuario = "SELECT * FROM Usuario WHERE id = :candidato_id";
            $stmtUsuario = $this->conn->prepare($queryUsuario);
            $stmtUsuario->bindParam(':candidato_id', $candidatoId, PDO::PARAM_INT);
            $stmtUsuario->execute();

            if ($stmtUsuario->rowCount() == 0) {
                throw new Exception('El candidato con ID ' . $candidatoId . ' no existe');
            }

            // Consulta detallada con JOIN para obtener más información sobre las postulaciones
            $query = "SELECT p.*, o.titulo as titulo_oferta 
                     FROM Postulacion p
                     JOIN OfertaLaboral o ON p.oferta_laboral_id = o.id
                     WHERE p.candidato_id = :candidato_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':candidato_id', $candidatoId, PDO::PARAM_INT);
            $stmt->execute();

            $postulaciones = [];

            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $postulaciones[] = [
                        'id' => $row['id'],
                        'oferta_id' => $row['oferta_laboral_id'],
                        'titulo_oferta' => $row['titulo_oferta'],
                        'estado_postulacion' => $row['estado_postulacion'],
                        'fecha_postulacion' => $row['fecha_postulacion'],
                        'comentario' => $row['comentario']
                    ];
                }
            }

            return $postulaciones;
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }
}
?>