<?php
require_once __DIR__ . '/../models/OfertaLaboral.php';

class OfertaLaboralController
{
    public function verVigentes()
    {
        try {
            $db = new Database();
            $conn = $db->getConnection();

            // Consulta para ofertas vigentes - verifica el nombre de la tabla y el campo estado
            $query = "SELECT * FROM OfertaLaboral WHERE estado = 'Vigente'";
            // Si tu tabla está en minúsculas, usa:
            // $query = "SELECT * FROM ofertalaboral WHERE estado = 'Vigente'";

            $stmt = $conn->prepare($query);
            $stmt->execute();

            $ofertas = [];

            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $ofertas[] = [
                        'id' => $row['id'],
                        'titulo' => $row['titulo'],
                        'descripcion' => $row['descripcion'],
                        'ubicacion' => $row['ubicacion'],
                        'salario' => $row['salario'],
                        'tipo_contrato' => $row['tipo_contrato'],
                        'fecha_publicacion' => $row['fecha_publicacion'],
                        'fecha_cierre' => $row['fecha_cierre']
                    ];
                }
            }

            echo json_encode([
                'success' => true,
                'data' => $ofertas
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener ofertas laborales vigentes: ' . $e->getMessage()
            ]);
        }
    }

    public function porUbicacion($ciudad)
    {
        header('Content-Type: application/json');
        $oferta = new OfertaLaboral();
        echo json_encode(['success' => true, 'data' => $oferta->getByUbicacion($ciudad)]);
    }

    public function porTipoContrato($tipo)
    {
        header('Content-Type: application/json');
        $oferta = new OfertaLaboral();
        echo json_encode(['success' => true, 'data' => $oferta->getByTipoContrato($tipo)]);
    }

    public function detalle($id)
    {
        header('Content-Type: application/json');
        $oferta = new OfertaLaboral();
        echo json_encode(['success' => true, 'data' => $oferta->getById($id)]);
    }
}
?>