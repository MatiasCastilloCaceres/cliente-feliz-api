<?php
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../utils/Response.php';

use Utils\Response;

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


}
?>