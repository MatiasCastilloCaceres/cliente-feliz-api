<?php
namespace Controllers;

use Utils\Response;
use Utils\Validator;

class ClienteController
{
    private $db;

    public function __construct()
    {
        $database = new \Database();
        $this->db = $database->getConnection();
    }

    public function getClientes()
    {
        try {
            $query = "SELECT * FROM clientes";
            $stmt = $this->db->prepare($query);
            $stmt->execute();

            $clientes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            Response::json([
                'success' => true,
                'data' => $clientes
            ]);
        } catch (\Exception $e) {
            Response::json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getCliente($id)
    {
        try {
            $query = "SELECT * FROM clientes WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $cliente = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$cliente) {
                Response::json([
                    'success' => false,
                    'message' => 'Cliente no encontrado'
                ], 404);
                return;
            }

            Response::json([
                'success' => true,
                'data' => $cliente
            ]);
        } catch (\Exception $e) {
            Response::json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function createCliente()
    {
        // Aquí implementa la lógica para crear un cliente
        Response::json([
            'success' => true,
            'message' => 'Método createCliente llamado correctamente'
        ]);
    }

    public function updateCliente($id)
    {
        // Aquí implementa la lógica para actualizar un cliente
        Response::json([
            'success' => true,
            'message' => 'Método updateCliente llamado correctamente para el ID: ' . $id
        ]);
    }

    public function deleteCliente($id)
    {
        // Aquí implementa la lógica para eliminar un cliente
        Response::json([
            'success' => true,
            'message' => 'Método deleteCliente llamado correctamente para el ID: ' . $id
        ]);
    }
}