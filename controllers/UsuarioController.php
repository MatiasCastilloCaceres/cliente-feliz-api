<?php
require_once __DIR__ . '/../models/Usuario.php';

class UsuarioController
{
    public function crearUsuario()
    {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['nombre'], $input['apellido'], $input['email'], $input['contraseña'], $input['rol'])) {
            echo json_encode(['success' => false, 'message' => 'Faltan campos requeridos']);
            return;
        }

        try {
            $usuario = new Usuario();
            $result = $usuario->crear($input);
            echo json_encode(['success' => $result]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function getUsuarios()
    {
        try {
            $db = new Database();
            $conn = $db->getConnection();

            $query = "SELECT id, nombre, email, rol FROM usuarios";
            $stmt = $conn->prepare($query);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $usuarios = [];
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    // Eliminar datos sensibles como contraseñas
                    $usuarios[] = [
                        'id' => $row['id'],
                        'nombre' => $row['nombre'],
                        'email' => $row['email'],
                        'rol' => $row['rol']
                    ];
                }

                echo json_encode([
                    'success' => true,
                    'usuarios' => $usuarios
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'message' => 'No hay usuarios registrados',
                    'usuarios' => []
                ]);
            }
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener usuarios: ' . $e->getMessage()
            ]);
        }
    }
}

?>