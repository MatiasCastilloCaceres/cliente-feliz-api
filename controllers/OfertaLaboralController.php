<?php
require_once __DIR__ . '/../models/OfertaLaboral.php';
require_once __DIR__ . '/../utils/Response.php';

use Utils\Response;

class OfertaLaboralController
{
    private $model;

    public function __construct()
    {
        $this->model = new OfertaLaboral();
    }

    // Métodos existentes
    public function verVigentes()
    {
        try {
            $ofertas = $this->model->getVigentes();
            Response::json([
                'success' => true,
                'data' => $ofertas
            ]);
        } catch (PDOException $e) {
            Response::json([
                'success' => false,
                'message' => 'Error al obtener ofertas vigentes: ' . $e->getMessage()
            ], 500);
        }
    }

    public function porUbicacion($ubicacion)
    {
        try {
            $ofertas = $this->model->getByUbicacion($ubicacion);
            Response::json([
                'success' => true,
                'data' => $ofertas
            ]);
        } catch (PDOException $e) {
            Response::json([
                'success' => false,
                'message' => 'Error al obtener ofertas por ubicación: ' . $e->getMessage()
            ], 500);
        }
    }

    public function porTipoContrato($tipo)
    {
        try {
            $ofertas = $this->model->getByTipoContrato($tipo);
            Response::json([
                'success' => true,
                'data' => $ofertas
            ]);
        } catch (PDOException $e) {
            Response::json([
                'success' => false,
                'message' => 'Error al obtener ofertas por tipo de contrato: ' . $e->getMessage()
            ], 500);
        }
    }

    public function detalle($id)
    {
        try {
            $oferta = $this->model->getById($id);
            if ($oferta) {
                Response::json([
                    'success' => true,
                    'data' => $oferta
                ]);
            } else {
                Response::json([
                    'success' => false,
                    'message' => 'Oferta laboral no encontrada'
                ], 404);
            }
        } catch (PDOException $e) {
            Response::json([
                'success' => false,
                'message' => 'Error al obtener detalle de oferta: ' . $e->getMessage()
            ], 500);
        }
    }

    public function crearOferta()
    {
        try {
            // Obtener datos del cuerpo de la solicitud
            $rawData = file_get_contents("php://input");
            error_log("Datos recibidos: " . $rawData);

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

            // Validar datos mínimos requeridos
            if (empty($data['titulo']) || empty($data['descripcion']) || empty($data['reclutador_id'])) {
                Response::json([
                    'success' => false,
                    'message' => 'Faltan datos requeridos para crear oferta',
                    'required_fields' => ['titulo', 'descripcion', 'reclutador_id'],
                    'received_data' => $data
                ], 400);
                return;
            }

            // Crear oferta en la base de datos
            $resultado = $this->model->crear($data);

            if ($resultado) {
                Response::json([
                    'success' => true,
                    'message' => 'Oferta laboral creada exitosamente',
                    'id' => $resultado
                ], 201);
            } else {
                Response::json([
                    'success' => false,
                    'message' => 'Error al crear oferta laboral'
                ], 500);
            }
        } catch (PDOException $e) {
            Response::json([
                'success' => false,
                'message' => 'Error al crear oferta laboral: ' . $e->getMessage()
            ], 500);
        } catch (Exception $e) {
            Response::json([
                'success' => false,
                'message' => 'Error general: ' . $e->getMessage()
            ], 500);
        }
    }

    public function editarOferta($id)
    {
        try {
            // Verificar si la oferta existe
            $oferta = $this->model->getById($id);
            if (!$oferta) {
                Response::json([
                    'success' => false,
                    'message' => 'Oferta laboral no encontrada'
                ], 404);
                return;
            }

            // Obtener datos del cuerpo de la solicitud
            $rawData = file_get_contents("php://input");
            error_log("Datos recibidos para editar oferta ID $id: " . $rawData);

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

            // Actualizar la oferta
            $resultado = $this->model->actualizar($id, $data);

            if ($resultado) {
                Response::json([
                    'success' => true,
                    'message' => 'Oferta laboral actualizada exitosamente'
                ]);
            } else {
                Response::json([
                    'success' => false,
                    'message' => 'Error al actualizar oferta laboral'
                ], 500);
            }
        } catch (PDOException $e) {
            Response::json([
                'success' => false,
                'message' => 'Error al actualizar oferta laboral: ' . $e->getMessage()
            ], 500);
        }
    }

    public function desactivarOferta($id)
    {
        try {
            // Verificar si la oferta existe
            $oferta = $this->model->getById($id);
            if (!$oferta) {
                Response::json([
                    'success' => false,
                    'message' => 'Oferta laboral no encontrada'
                ], 404);
                return;
            }

            // Cambiar el estado a "Baja" (desactivada)
            $resultado = $this->model->cambiarEstado($id, 'Baja');

            if ($resultado) {
                Response::json([
                    'success' => true,
                    'message' => 'Oferta laboral desactivada exitosamente'
                ]);
            } else {
                Response::json([
                    'success' => false,
                    'message' => 'Error al desactivar oferta laboral'
                ], 500);
            }
        } catch (PDOException $e) {
            Response::json([
                'success' => false,
                'message' => 'Error al desactivar oferta laboral: ' . $e->getMessage()
            ], 500);
        }
    }

    public function actualizarParcial($id)
    {
        try {
            // Verificar si la oferta existe
            $oferta = $this->model->getById($id);
            if (!$oferta) {
                Response::json([
                    'success' => false,
                    'message' => 'Oferta laboral no encontrada'
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

            // Actualizar la oferta
            $resultado = $this->model->actualizarParcial($id, $data);

            if ($resultado) {
                Response::json([
                    'success' => true,
                    'message' => 'Oferta laboral actualizada exitosamente'
                ]);
            } else {
                Response::json([
                    'success' => false,
                    'message' => 'No se realizaron cambios en la oferta laboral'
                ], 500);
            }
        } catch (PDOException $e) {
            Response::json([
                'success' => false,
                'message' => 'Error al actualizar oferta laboral: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar una oferta laboral
     */
    public function eliminarOferta($id)
    {
        try {
            // Verificar si la oferta existe
            $oferta = $this->model->getById($id);
            if (!$oferta) {
                Response::json([
                    'success' => false,
                    'message' => 'Oferta laboral no encontrada'
                ], 404);
                return;
            }

            // Verificar si hay postulaciones asociadas
            $postulaciones = $this->verificarPostulacionesAsociadas($id);
            if ($postulaciones > 0) {
                Response::json([
                    'success' => false,
                    'message' => 'No se puede eliminar la oferta porque tiene postulaciones asociadas',
                    'postulaciones_asociadas' => $postulaciones,
                    'suggestion' => 'Considere desactivar la oferta en lugar de eliminarla'
                ], 400);
                return;
            }

            // Eliminar la oferta
            $resultado = $this->model->eliminar($id);

            if ($resultado) {
                Response::json([
                    'success' => true,
                    'message' => 'Oferta laboral eliminada exitosamente'
                ]);
            } else {
                Response::json([
                    'success' => false,
                    'message' => 'Error al eliminar oferta laboral'
                ], 500);
            }
        } catch (PDOException $e) {
            Response::json([
                'success' => false,
                'message' => 'Error al eliminar oferta laboral: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar si hay postulaciones asociadas a una oferta
     */
    private function verificarPostulacionesAsociadas($ofertaId)
    {
        // Crear una instancia del modelo de postulación para la consulta
        require_once __DIR__ . '/../models/Postulacion.php';
        $postulacionModel = new Postulacion();
        
        return $postulacionModel->contarPorOferta($ofertaId);
    }
}
?>