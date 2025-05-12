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

            // Cambiar la verificación para usar estado_postulacion en lugar de estado
            if (!isset($data['estado_postulacion'])) {
                Response::json([
                    'success' => false,
                    'message' => 'Se requiere el nuevo estado de postulación',
                    'valid_states' => ['Postulando', 'Revisando', 'Entrevista Psicológica', 'Entrevista Personal', 'Seleccionado', 'Descartado']
                ], 400);
                return;
            }

            $resultado = $this->model->actualizarEstado($id, $data['estado_postulacion']);

            if ($resultado) {
                Response::json([
                    'success' => true,
                    'message' => 'Estado de postulación actualizado'
                ]);
            } else {
                Response::json([
                    'success' => false,
                    'message' => 'Error al actualizar estado de postulación o estado no válido'
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

    public function actualizarPostulacion($id)
    {
        try {
            // Verificar si la postulación existe
            $postulacion = $this->model->getById($id);
            if (!$postulacion) {
                Response::json([
                    'success' => false,
                    'message' => 'Postulación no encontrada'
                ], 404);
                return;
            }

            // Obtener datos del cuerpo de la solicitud
            $rawData = file_get_contents("php://input");
            error_log("Datos recibidos para actualizar postulación ID $id: " . $rawData);
            
            $data = json_decode($rawData, true);

            // Validar que se recibió un JSON válido
            if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
                Response::json([
                    'success' => false,
                    'message' => 'Error en formato JSON: ' . json_last_error_msg(),
                    'raw_data' => $rawData
                ], 400);
                return;
            }

            // Eliminar el id del conjunto de datos a actualizar (si existe)
            if (isset($data['id'])) {
                unset($data['id']);
            }
            
            // No permitir la actualización de ciertos campos a través de la API
            // por ejemplo, nombre_candidato que mencionas en tu solicitud
            if (isset($data['nombre_candidato'])) {
                Response::json([
                    'success' => false,
                    'message' => 'No se puede actualizar el nombre del candidato directamente. Este campo se actualiza automáticamente al cambiar el candidato_id.'
                ], 400);
                return;
            }

            // Validar el estado_postulacion si se está actualizando
            if (isset($data['estado_postulacion'])) {
                $estadosValidos = ['Postulando', 'Revisando', 'Entrevista Psicológica', 'Entrevista Personal', 'Seleccionado', 'Descartado'];
                if (!in_array($data['estado_postulacion'], $estadosValidos)) {
                    Response::json([
                        'success' => false,
                        'message' => 'Estado de postulación no válido',
                        'valid_states' => $estadosValidos
                    ], 400);
                    return;
                }
            }

            // Actualizar la postulación
            $resultado = $this->model->actualizar($id, $data);

            if ($resultado) {
                Response::json([
                    'success' => true,
                    'message' => 'Postulación actualizada exitosamente'
                ]);
            } else {
                Response::json([
                    'success' => false,
                    'message' => 'Error al actualizar postulación'
                ], 500);
            }
        } catch (PDOException $e) {
            Response::json([
                'success' => false,
                'message' => 'Error al actualizar postulación: ' . $e->getMessage()
            ], 500);
        }
    }

    public function actualizarParcial($id)
    {
        try {
            // Verificar si la postulación existe
            $postulacion = $this->model->getById($id);
            if (!$postulacion) {
                Response::json([
                    'success' => false,
                    'message' => 'Postulación no encontrada'
                ], 404);
                return;
            }

            // Obtener datos del cuerpo de la solicitud
            $rawData = file_get_contents("php://input");
            error_log("Datos recibidos para actualización parcial ID $id: " . $rawData);
            
            $data = json_decode($rawData, true);

            // Validar que se recibió un JSON válido
            if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
                Response::json([
                    'success' => false,
                    'message' => 'Error en formato JSON: ' . json_last_error_msg(),
                    'raw_data' => $rawData
                ], 400);
                return;
            }

            // Validar que hay datos para actualizar
            if (empty($data) || count($data) <= 1) { // Ignorar el campo 'id' que ya usamos
                Response::json([
                    'success' => false,
                    'message' => 'No se proporcionaron datos para actualizar'
                ], 400);
                return;
            }

            // Eliminar el id del conjunto de datos a actualizar (si existe)
            if (isset($data['id'])) {
                unset($data['id']);
            }
            
            // No permitir la actualización de ciertos campos a través de la API
            if (isset($data['nombre_candidato'])) {
                Response::json([
                    'success' => false,
                    'message' => 'No se puede actualizar el nombre del candidato directamente'
                ], 400);
                return;
            }

            // Validar el estado_postulacion si se está actualizando
            if (isset($data['estado_postulacion'])) {
                $estadosValidos = ['Postulando', 'Revisando', 'Entrevista Psicológica', 'Entrevista Personal', 'Seleccionado', 'Descartado'];
                if (!in_array($data['estado_postulacion'], $estadosValidos)) {
                    Response::json([
                        'success' => false,
                        'message' => 'Estado de postulación no válido',
                        'valid_states' => $estadosValidos
                    ], 400);
                    return;
                }
            }

            // Actualizar la postulación
            $resultado = $this->model->actualizarParcial($id, $data);

            if ($resultado) {
                Response::json([
                    'success' => true,
                    'message' => 'Postulación actualizada parcialmente con éxito'
                ]);
            } else {
                Response::json([
                    'success' => false,
                    'message' => 'No se realizaron cambios en la postulación'
                ], 500);
            }
        } catch (PDOException $e) {
            Response::json([
                'success' => false,
                'message' => 'Error al actualizar parcialmente postulación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar una postulación
     */
    public function eliminarPostulacion($id)
    {
        try {
            // Verificar si la postulación existe
            $postulacion = $this->model->getById($id);
            if (!$postulacion) {
                Response::json([
                    'success' => false,
                    'message' => 'Postulación no encontrada'
                ], 404);
                return;
            }

            // Eliminar la postulación
            $resultado = $this->model->eliminar($id);

            if ($resultado) {
                Response::json([
                    'success' => true,
                    'message' => 'Postulación eliminada exitosamente'
                ]);
            } else {
                Response::json([
                    'success' => false,
                    'message' => 'Error al eliminar postulación'
                ], 500);
            }
        } catch (PDOException $e) {
            Response::json([
                'success' => false,
                'message' => 'Error al eliminar postulación: ' . $e->getMessage()
            ], 500);
        }
    }
}
?>