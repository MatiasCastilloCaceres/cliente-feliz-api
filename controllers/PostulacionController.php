<?php
// filepath: c:\xampp\htdocs\cliente-feliz-api\controllers\PostulacionController.php
require_once __DIR__ . '/../models/Postulacion.php';
require_once __DIR__ . '/../utils/Response.php';

use Utils\Response;

class PostulacionController
{
    private $model;

    public function __construct()
    {
        $this->model = new Postulacion();
    }

    public function getPostulaciones()
    {
        try {
            $postulaciones = $this->model->getAll();
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
            $postulaciones = $this->model->getByCandidato($candidatoId);
            Response::json([
                'success' => true,
                'data' => $postulaciones
            ]);
        } catch (PDOException $e) {
            Response::json([
                'success' => false,
                'message' => 'Error al obtener postulaciones por candidato: ' . $e->getMessage()
            ], 500);
        }
    }

    public function porOferta($ofertaId)
    {
        try {
            $postulaciones = $this->model->getByOferta($ofertaId);
            Response::json([
                'success' => true,
                'data' => $postulaciones
            ]);
        } catch (PDOException $e) {
            Response::json([
                'success' => false,
                'message' => 'Error al obtener postulaciones por oferta: ' . $e->getMessage()
            ], 500);
        }
    }

    public function crearPostulacion()
    {
        try {
            $data = json_decode(file_get_contents("php://input"), true);

            // Validar datos
            if (!isset($data['candidato_id']) || !isset($data['oferta_laboral_id'])) {
                Response::json([
                    'success' => false,
                    'message' => 'Datos incompletos para crear postulación'
                ], 400);
                return;
            }

            $resultado = $this->model->crear($data);

            if ($resultado) {
                Response::json([
                    'success' => true,
                    'message' => 'Postulación creada exitosamente',
                    'data' => ['id' => $resultado]
                ], 201);
            } else {
                Response::json([
                    'success' => false,
                    'message' => 'Error al crear postulación'
                ], 500);
            }
        } catch (PDOException $e) {
            Response::json([
                'success' => false,
                'message' => 'Error al crear postulación: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cambiarEstado($id)
    {
        try {
            $data = json_decode(file_get_contents("php://input"), true);

            if (!isset($data['estado'])) {
                Response::json([
                    'success' => false,
                    'message' => 'Se requiere el nuevo estado'
                ], 400);
                return;
            }

            $resultado = $this->model->actualizarEstado($id, $data['estado']);

            if ($resultado) {
                Response::json([
                    'success' => true,
                    'message' => 'Estado de postulación actualizado'
                ]);
            } else {
                Response::json([
                    'success' => false,
                    'message' => 'Error al actualizar estado de postulación'
                ], 500);
            }
        } catch (PDOException $e) {
            Response::json([
                'success' => false,
                'message' => 'Error al actualizar estado de postulación: ' . $e->getMessage()
            ], 500);
        }
    }

    public function agregarComentario($id)
    {
        try {
            $data = json_decode(file_get_contents("php://input"), true);

            if (!isset($data['comentario'])) {
                Response::json([
                    'success' => false,
                    'message' => 'Se requiere el comentario'
                ], 400);
                return;
            }

            $resultado = $this->model->actualizarComentario($id, $data['comentario']);

            if ($resultado) {
                Response::json([
                    'success' => true,
                    'message' => 'Comentario agregado a la postulación'
                ]);
            } else {
                Response::json([
                    'success' => false,
                    'message' => 'Error al agregar comentario a la postulación'
                ], 500);
            }
        } catch (PDOException $e) {
            Response::json([
                'success' => false,
                'message' => 'Error al agregar comentario a la postulación: ' . $e->getMessage()
            ], 500);
        }
    }
}
?>