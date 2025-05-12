<?php
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

    // Métodos existentes
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
            $postulaciones = $this->model->getPorCandidato($candidatoId);
            Response::json([
                'success' => true,
                'data' => $postulaciones
            ]);
        } catch (Exception $e) {
            Response::json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        } catch (PDOException $e) {
            Response::json([
                'success' => false,
                'message' => 'Error al obtener postulaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    // Nuevos métodos a implementar
    public function crearPostulacion()
    {
        try {
            // Obtener datos del cuerpo de la solicitud
            $data = json_decode(file_get_contents("php://input"), true);

            // Validar datos mínimos requeridos
            if (!isset($data['candidato_id']) || !isset($data['oferta_laboral_id'])) {
                Response::json([
                    'success' => false,
                    'message' => 'Faltan datos requeridos para crear postulación'
                ], 400);
                return;
            }

            // Verificar si ya existe una postulación para este candidato y oferta
            $existente = $this->model->verificarExistente($data['candidato_id'], $data['oferta_laboral_id']);
            if ($existente) {
                Response::json([
                    'success' => false,
                    'message' => 'Ya existe una postulación para esta oferta laboral'
                ], 409);
                return;
            }

            // Guardar la postulación en la base de datos
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

    public function porOferta($ofertaId)
    {
        try {
            $postulaciones = $this->model->getPorOferta($ofertaId);
            Response::json([
                'success' => true,
                'data' => $postulaciones
            ]);
        } catch (Exception $e) {
            Response::json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        } catch (PDOException $e) {
            Response::json([
                'success' => false,
                'message' => 'Error al obtener postulaciones por oferta: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cambiarEstado($postulacionId)
    {
        try {
            // Obtener datos del cuerpo de la solicitud
            $data = json_decode(file_get_contents("php://input"), true);

            // Validar que se recibió el nuevo estado
            if (!isset($data['estado'])) {
                Response::json([
                    'success' => false,
                    'message' => 'Falta el estado para actualizar la postulación'
                ], 400);
                return;
            }

            // Verificar si la postulación existe
            $postulacion = $this->model->getById($postulacionId);
            if (!$postulacion) {
                Response::json([
                    'success' => false,
                    'message' => 'Postulación no encontrada'
                ], 404);
                return;
            }

            // Actualizar el estado
            $resultado = $this->model->actualizarEstado($postulacionId, $data['estado']);

            if ($resultado) {
                Response::json([
                    'success' => true,
                    'message' => 'Estado de postulación actualizado exitosamente'
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

    public function agregarComentario($postulacionId)
    {
        try {
            // Obtener datos del cuerpo de la solicitud
            $data = json_decode(file_get_contents("php://input"), true);

            // Validar que se recibió el comentario
            if (!isset($data['comentario'])) {
                Response::json([
                    'success' => false,
                    'message' => 'Falta el comentario para actualizar la postulación'
                ], 400);
                return;
            }

            // Verificar si la postulación existe
            $postulacion = $this->model->getById($postulacionId);
            if (!$postulacion) {
                Response::json([
                    'success' => false,
                    'message' => 'Postulación no encontrada'
                ], 404);
                return;
            }

            // Actualizar el comentario
            $resultado = $this->model->actualizarComentario($postulacionId, $data['comentario']);

            if ($resultado) {
                Response::json([
                    'success' => true,
                    'message' => 'Comentario de postulación actualizado exitosamente'
                ]);
            } else {
                Response::json([
                    'success' => false,
                    'message' => 'Error al actualizar comentario de postulación'
                ], 500);
            }
        } catch (PDOException $e) {
            Response::json([
                'success' => false,
                'message' => 'Error al actualizar comentario de postulación: ' . $e->getMessage()
            ], 500);
        }
    }
}