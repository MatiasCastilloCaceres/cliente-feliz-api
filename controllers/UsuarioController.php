<?php
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../utils/Response.php';

use Utils\Response;

// Función simple de respuesta JSON (sin usar la clase Response)
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

class UsuarioController
{
    private $model;
    
    public function __construct()
    {
        $this->model = new Usuario();
    }

    public function crearUsuario()
    {
        try {
            // Obtener datos del cuerpo de la solicitud
            $rawData = file_get_contents("php://input");
            error_log("Datos recibidos para crear usuario: " . $rawData);

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

            // Definir y validar campos requeridos con mensajes detallados
            $camposRequeridos = ['nombre', 'apellido', 'email', 'contraseña', 'rol'];
            $camposFaltantes = [];
            
            foreach ($camposRequeridos as $campo) {
                if (empty($data[$campo])) {
                    $camposFaltantes[] = $campo;
                }
            }
            
            if (!empty($camposFaltantes)) {
                Response::json([
                    'success' => false,
                    'message' => 'Faltan campos requeridos',
                    'missing_fields' => $camposFaltantes,
                    'required_fields' => $camposRequeridos,
                    'received_data' => $data
                ], 400);
                return;
            }

            // Validar que el rol sea válido
            $rolesValidos = ['Reclutador', 'Candidato'];
            if (!in_array($data['rol'], $rolesValidos)) {
                Response::json([
                    'success' => false,
                    'message' => 'Rol no válido. Debe ser "Reclutador" o "Candidato"',
                    'valid_roles' => $rolesValidos
                ], 400);
                return;
            }

            // Validar que el email no esté ya registrado
            if ($this->model->emailExiste($data['email'])) {
                Response::json([
                    'success' => false,
                    'message' => 'El email ya está registrado'
                ], 400);
                return;
            }

            // Crear usuario en la base de datos
            $resultado = $this->model->crear($data);

            if ($resultado) {
                Response::json([
                    'success' => true,
                    'message' => 'Usuario creado exitosamente',
                    'id' => $resultado
                ], 201);
            } else {
                Response::json([
                    'success' => false,
                    'message' => 'Error al crear usuario'
                ], 500);
            }
        } catch (PDOException $e) {
            Response::json([
                'success' => false,
                'message' => 'Error al crear usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getUsuarios()
    {
        try {
            $usuarios = $this->model->getAll();
            
            if (!empty($usuarios)) {
                Response::json([
                    'success' => true,
                    'usuarios' => $usuarios
                ]);
            } else {
                Response::json([
                    'success' => true,
                    'message' => 'No hay usuarios registrados',
                    'usuarios' => []
                ]);
            }
        } catch (PDOException $e) {
            Response::json([
                'success' => false,
                'message' => 'Error al obtener usuarios: ' . $e->getMessage()
            ], 500);
        }
    }

    // Método para obtener un usuario específico
    public function getUsuario($id)
    {
        try {
            $usuario = $this->model->getById($id);
            
            if ($usuario) {
                sendJsonResponse([
                    'success' => true,
                    'usuario' => $usuario
                ]);
            } else {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }
        } catch (PDOException $e) {
            sendJsonResponse([
                'success' => false,
                'message' => 'Error al obtener usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    // Método para actualizar un usuario completo (PUT)
    public function actualizarUsuario($id)
    {
        try {
            // Verificar si el usuario existe
            $usuario = $this->model->getById($id);
            if (!$usuario) {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
                return;
            }

            // Obtener datos del cuerpo de la solicitud
            $rawData = file_get_contents("php://input");
            error_log("Datos recibidos para actualizar usuario ID $id: " . $rawData);
            
            $data = json_decode($rawData, true);

            // Validar que se recibió un JSON válido
            if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'Error en formato JSON: ' . json_last_error_msg(),
                    'raw_data' => $rawData
                ], 400);
                return;
            }

            // Validar campos requeridos
            $camposRequeridos = ['nombre', 'apellido', 'email', 'rol'];
            $camposFaltantes = [];
            
            foreach ($camposRequeridos as $campo) {
                if (empty($data[$campo])) {
                    $camposFaltantes[] = $campo;
                }
            }
            
            if (!empty($camposFaltantes)) {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'Faltan campos requeridos',
                    'missing_fields' => $camposFaltantes,
                    'required_fields' => $camposRequeridos
                ], 400);
                return;
            }

            // Validar que el rol sea válido
            $rolesValidos = ['administrador', 'reclutador', 'candidato']; // Ajusta según tus roles válidos
            if (!in_array(strtolower($data['rol']), array_map('strtolower', $rolesValidos))) {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'Rol no válido',
                    'valid_roles' => $rolesValidos
                ], 400);
                return;
            }

            // Validar que el email no esté ya registrado por otro usuario
            if ($data['email'] !== $usuario['email'] && $this->model->emailExiste($data['email'])) {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'El email ya está registrado por otro usuario'
                ], 400);
                return;
            }

            // Actualizar usuario
            $resultado = $this->model->actualizar($id, $data);

            if ($resultado) {
                sendJsonResponse([
                    'success' => true,
                    'message' => 'Usuario actualizado exitosamente'
                ]);
            } else {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'No se realizaron cambios en el usuario'
                ], 500);
            }
        } catch (PDOException $e) {
            sendJsonResponse([
                'success' => false,
                'message' => 'Error al actualizar usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    // Método para actualizar parcialmente un usuario (PATCH)
    public function actualizarParcial($id)
    {
        try {
            // Verificar si el usuario existe
            $usuario = $this->model->getById($id);
            if (!$usuario) {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
                return;
            }

            // Obtener datos del cuerpo de la solicitud
            $rawData = file_get_contents("php://input");
            error_log("Datos recibidos para actualización parcial de usuario ID $id: " . $rawData);
            
            $data = json_decode($rawData, true);

            // Validar que se recibió un JSON válido
            if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'Error en formato JSON: ' . json_last_error_msg(),
                    'raw_data' => $rawData
                ], 400);
                return;
            }

            // Validar que hay datos para actualizar
            if (empty($data) || count($data) <= 1) { // Ignorar el campo 'id' que ya usamos
                sendJsonResponse([
                    'success' => false,
                    'message' => 'No se proporcionaron datos para actualizar'
                ], 400);
                return;
            }

            // Validar que el rol sea válido si se está actualizando
            if (isset($data['rol'])) {
                $rolesValidos = ['administrador', 'reclutador', 'candidato']; // Ajusta según tus roles válidos
                if (!in_array(strtolower($data['rol']), array_map('strtolower', $rolesValidos))) {
                    sendJsonResponse([
                        'success' => false,
                        'message' => 'Rol no válido',
                        'valid_roles' => $rolesValidos
                    ], 400);
                    return;
                }
            }

            // Validar que el email no esté ya registrado por otro usuario si se está actualizando
            if (isset($data['email']) && $data['email'] !== $usuario['email'] && $this->model->emailExiste($data['email'])) {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'El email ya está registrado por otro usuario'
                ], 400);
                return;
            }

            // Actualizar usuario
            $resultado = $this->model->actualizar($id, $data);

            if ($resultado) {
                sendJsonResponse([
                    'success' => true,
                    'message' => 'Usuario actualizado parcialmente con éxito'
                ]);
            } else {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'No se realizaron cambios en el usuario'
                ], 500);
            }
        } catch (PDOException $e) {
            sendJsonResponse([
                'success' => false,
                'message' => 'Error al actualizar usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    // Método para eliminar un usuario
    public function eliminarUsuario($id)
    {
        try {
            // Verificar si el usuario existe
            $usuario = $this->model->getById($id);
            if (!$usuario) {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
                return;
            }

            // Eliminar usuario
            $resultado = $this->model->eliminar($id);

            if ($resultado) {
                sendJsonResponse([
                    'success' => true,
                    'message' => 'Usuario eliminado exitosamente'
                ]);
            } else {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'Error al eliminar usuario'
                ], 500);
            }
        } catch (PDOException $e) {
            sendJsonResponse([
                'success' => false,
                'message' => 'Error al eliminar usuario: ' . $e->getMessage()
            ], 500);
        }
    }
}
?>