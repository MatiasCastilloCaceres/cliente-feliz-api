<?php
// filepath: c:\xampp\htdocs\cliente-feliz-api\index.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Configuración inicial
require_once 'config/database.php';
require_once 'controllers/OfertaLaboralController.php';
require_once 'controllers/UsuarioController.php';
require_once 'controllers/PostulacionController.php';

// Normalizar la URI para mayor flexibilidad
$uri = strtolower($_SERVER['REQUEST_URI']); // Convertir a minúsculas
$method = $_SERVER['REQUEST_METHOD'];

// Eliminar referencias a index.php y cliente-feliz-api
$uri = str_replace('/index.php', '', $uri);
$uri = str_replace('/cliente-feliz-api', '', $uri);
$uri = rtrim($uri, '/');

// Para debug
error_log("URI recibida: " . $uri);
error_log("Método: " . $method);

// Detectar ruta de ofertalaboral específica 
if ($uri == '/ofertalaboral' || $uri == '/api/ofertalaboral') {
    $controller = new OfertaLaboralController();

    if ($method == 'GET') {
        $controller->verVigentes();
        return;
    } elseif ($method == 'POST') {
        error_log("Procesando POST a /ofertalaboral");
        $controller->crearOferta();
        return;
    }
}

// Rutas de ofertas laborales (flexible)
if (
    strpos($uri, '/ofertas') !== false || strpos($uri, '/ofertalabor') !== false ||
    strpos($uri, '/ofertalaboral') !== false || strpos($uri, '/ofertaslaborales') !== false
) {
    $controller = new OfertaLaboralController();

    // GET para obtener ofertas vigentes
    if (($uri == '/ofertalaboral' || $uri == '/api/ofertalaboral' || $uri == '/ofertas-laborales/vigentes') && $method == 'GET') {
        $controller->verVigentes();
        return;
    }
    // POST para crear nueva oferta
    elseif (($uri == '/ofertalaboral' || $uri == '/api/ofertalaboral' || $uri == '/ofertas') && $method == 'POST') {
        $controller->crearOferta();
        return;
    }
    // PUT para editar oferta (con ID en el cuerpo)
    elseif (($uri == '/ofertalaboral' || $uri == '/api/ofertalaboral') && $method == 'PUT') {
        // Obtener el ID del cuerpo de la solicitud
        $data = json_decode(file_get_contents("php://input"), true);
        if (isset($data['id'])) {
            $controller->editarOferta($data['id']);
            return;
        } else {
            Response::json([
                'success' => false,
                'message' => 'Se requiere el ID de la oferta en el cuerpo de la solicitud'
            ], 400);
            return;
        }
    }
    // PATCH para actualización parcial (con ID en el cuerpo)
    elseif (($uri == '/ofertalaboral' || $uri == '/api/ofertalaboral') && $method == 'PATCH') {
        // Obtener el ID del cuerpo de la solicitud
        $data = json_decode(file_get_contents("php://input"), true);
        if (isset($data['id'])) {
            $controller->actualizarParcial($data['id']);
            return;
        } else {
            Response::json([
                'success' => false,
                'message' => 'Se requiere el ID de la oferta en el cuerpo de la solicitud'
            ], 400);
            return;
        }
    }
    // Resto de las rutas existentes...
    elseif (preg_match('#^/ofertas-laborales/ubicacion/([^/]+)$#', $uri, $matches) && $method == 'GET') {
        $controller->porUbicacion($matches[1]);
        return;
    } elseif (preg_match('#^/ofertas-laborales/contrato/([^/]+)$#', $uri, $matches) && $method == 'GET') {
        $controller->porTipoContrato($matches[1]);
        return;
    } elseif (preg_match('#^/ofertas-laborales/([0-9]+)$#', $uri, $matches) && $method == 'GET') {
        $controller->detalle($matches[1]);
        return;
    } elseif (preg_match('#^/ofertas/([0-9]+)$#', $uri, $matches) && $method == 'PUT') {
        $controller->editarOferta($matches[1]);
        return;
    } elseif (preg_match('#^/ofertas/([0-9]+)$#', $uri, $matches) && $method == 'PATCH') {
        $controller->actualizarParcial($matches[1]);
        return;
    } elseif (preg_match('#^/ofertas/([0-9]+)/desactivar$#', $uri, $matches) && $method == 'PATCH') {
        $controller->desactivarOferta($matches[1]);
        return;
    }
    // AGREGAR SOPORTE PARA DELETE - eliminación de oferta
    elseif (preg_match('#^/(?:api/)?(?:ofertas|ofertalabor(?:al)?)/([0-9]+)$#', $uri, $matches) && $method == 'DELETE') {
        $controller->eliminarOferta($matches[1]);
        return;
    }
    // AGREGAR SOPORTE PARA DELETE con ID en el cuerpo
    elseif (($uri == '/ofertalaboral' || $uri == '/api/ofertalaboral' || $uri == '/ofertas' || $uri == '/api/ofertas') && $method == 'DELETE') {
        $data = json_decode(file_get_contents("php://input"), true);
        if (isset($data['id'])) {
            $controller->eliminarOferta($data['id']);
            return;
        } else {
            Response::json([
                'success' => false,
                'message' => 'Se requiere el ID de la oferta en el cuerpo de la solicitud'
            ], 400);
            return;
        }
    }
}

// Rutas de usuarios
if ($uri == '/usuario' || $uri == '/api/usuario') {
    $controller = new UsuarioController();
    if ($method == 'GET') {
        $controller->getUsuarios();
        return;
    } elseif ($method == 'POST') {
        $controller->crearUsuario();
        return;
    }
}

// Rutas de postulaciones (más flexibles)
if (strpos($uri, '/postulacion') !== false || strpos($uri, '/postulaciones') !== false) {
    $controller = new PostulacionController();

    // Patrones de ruta
    if (($uri == '/postulaciones' || $uri == '/postulacion' || $uri == '/api/postulacion' || $uri == '/api/postulaciones') && $method == 'GET') {
        $controller->getPostulaciones();
        return;
    }

    // Detectar rutas con /candidato/ en ellas
    elseif (preg_match('#^/(?:api/)?(?:postulacion|postulaciones)/candidato/([0-9]+)$#', $uri, $matches) && $method == 'GET') {
        $controller->porCandidato($matches[1]);
        return;
    }

    // Detectar rutas con /oferta/ en ellas
    elseif (preg_match('#^/(?:api/)?(?:postulacion|postulaciones)/oferta/([0-9]+)$#', $uri, $matches) && $method == 'GET') {
        $controller->porOferta($matches[1]);
        return;
    }

    // Detectar rutas con /estado en ellas
    elseif (preg_match('#^/(?:api/)?(?:postulacion|postulaciones)/([0-9]+)/estado$#', $uri, $matches) && $method == 'PATCH') {
        $controller->cambiarEstado($matches[1]);
        return;
    }

    // Detectar rutas con /comentario en ellas
    elseif (preg_match('#^/(?:api/)?(?:postulacion|postulaciones)/([0-9]+)/comentario$#', $uri, $matches) && $method == 'PATCH') {
        $controller->agregarComentario($matches[1]);
        return;
    }

    // Rutas POST
    elseif (($uri == '/postulaciones' || $uri == '/postulacion' || $uri == '/api/postulacion' || $uri == '/api/postulaciones') && $method == 'POST') {
        $controller->crearPostulacion();
        return;
    }

    // AÑADIR SOPORTE PARA PUT - actualización completa
    elseif (($uri == '/postulaciones' || $uri == '/postulacion' || $uri == '/api/postulacion' || $uri == '/api/postulaciones') && $method == 'PUT') {
        // Obtener el ID del cuerpo de la solicitud
        $data = json_decode(file_get_contents("php://input"), true);
        if (isset($data['id'])) {
            $controller->actualizarPostulacion($data['id']);
            return;
        } else {
            Response::json([
                'success' => false,
                'message' => 'Se requiere el ID de la postulación en el cuerpo de la solicitud'
            ], 400);
            return;
        }
    }

    // AÑADIR SOPORTE PARA PATCH - actualización parcial
    elseif (($uri == '/postulaciones' || $uri == '/postulacion' || $uri == '/api/postulacion' || $uri == '/api/postulaciones') && $method == 'PATCH') {
        // Obtener el ID del cuerpo de la solicitud
        $data = json_decode(file_get_contents("php://input"), true);
        if (isset($data['id'])) {
            $controller->actualizarParcial($data['id']);
            return;
        } else {
            Response::json([
                'success' => false,
                'message' => 'Se requiere el ID de la postulación en el cuerpo de la solicitud'
            ], 400);
            return;
        }
    }
    // AGREGAR SOPORTE PARA DELETE - eliminación de postulación
    elseif (preg_match('#^/(?:api/)?(?:postulacion|postulaciones)/([0-9]+)$#', $uri, $matches) && $method == 'DELETE') {
        $controller->eliminarPostulacion($matches[1]);
        return;
    }
    // AGREGAR SOPORTE PARA DELETE con ID en el cuerpo
    elseif (($uri == '/postulaciones' || $uri == '/postulacion' || $uri == '/api/postulacion' || $uri == '/api/postulaciones') && $method == 'DELETE') {
        $data = json_decode(file_get_contents("php://input"), true);
        if (isset($data['id'])) {
            $controller->eliminarPostulacion($data['id']);
            return;
        } else {
            Response::json([
                'success' => false,
                'message' => 'Se requiere el ID de la postulación en el cuerpo de la solicitud'
            ], 400);
            return;
        }
    }
}

// Si ninguna ruta coincide
header('Content-Type: application/json');
echo json_encode([
    'success' => false,
    'message' => 'Ruta no encontrada',
    'debug' => [
        'uri' => $uri,
        'method' => $method,
        'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'No definido',
        'post_data' => $_POST,
        'raw_input' => json_decode(file_get_contents("php://input"), true)
    ]
]);
exit;