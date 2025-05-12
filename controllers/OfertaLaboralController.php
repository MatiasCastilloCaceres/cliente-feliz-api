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

    // Métodos existentes (verVigentes, porUbicacion, porTipoContrato, detalle)
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

    // Nuevos métodos a implementar
    public function crearOferta()
    {
        try {
            // Obtener datos del cuerpo de la solicitud
            $data = json_decode(file_get_contents("php://input"), true);

            // Validar datos mínimos requeridos
            if (!isset($data['titulo']) || !isset($data['reclutador_id'])) {
                Response::json([
                    'success' => false,
                    'message' => 'Faltan datos requeridos para crear oferta'
                ], 400);
                return;
            }

            // Guardar la oferta en la base de datos
            $resultado = $this->model->crear($data);

            if ($resultado) {
                Response::json([
                    'success' => true,
                    'message' => 'Oferta laboral creada exitosamente',
                    'data' => ['id' => $resultado]
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
            $data = json_decode(file_get_contents("php://input"), true);

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

            // Desactivar la oferta (cambiar estado a 'Baja')
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
}
?>