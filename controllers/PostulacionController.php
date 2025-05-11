<?php
require_once __DIR__ . '/../models/Postulacion.php';
require_once __DIR__ . '/../utils/Response.php';

use Utils\Response;

class PostulacionController
{
    public function getPostulaciones()
    {
        try {
            $postulacionModel = new Postulacion();
            $postulaciones = $postulacionModel->getAll();

            Response::json([
                'success' => true,
                'data' => $postulaciones
            ]);
        } catch (PDOException $e) {
            Response::json([
                'success' => false,
                'message' => 'Error al obtener postulaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    public function porCandidato($candidatoId)
    {
        try {
            $db = new Database();
            $conn = $db->getConnection();

            if ($conn === null) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error de conexión a la base de datos'
                ]);
                return;
            }

            // Primero verificamos si el candidato existe
            $queryUsuario = "SELECT * FROM Usuario WHERE id = :candidato_id";
            $stmtUsuario = $conn->prepare($queryUsuario);
            $stmtUsuario->bindParam(':candidato_id', $candidatoId, PDO::PARAM_INT);
            $stmtUsuario->execute();

            if ($stmtUsuario->rowCount() == 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'El candidato con ID ' . $candidatoId . ' no existe'
                ]);
                return;
            }

            // Consulta detallada con JOIN para obtener más información sobre las postulaciones
            $query = "SELECT p.*, o.titulo as titulo_oferta 
                     FROM Postulacion p
                     JOIN OfertaLaboral o ON p.oferta_laboral_id = o.id
                     WHERE p.candidato_id = :candidato_id";

            $stmt = $conn->prepare($query);
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

            echo json_encode([
                'success' => true,
                'data' => $postulaciones
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener postulaciones: ' . $e->getMessage()
            ]);
        }
    }
}
?>