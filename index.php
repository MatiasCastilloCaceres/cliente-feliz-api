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

// Detectar ruta de ofertalaboral específica
if (($uri == '/ofertalaboral' || $uri == '/api/ofertalaboral') && $method == 'GET') {
    $controller = new OfertaLaboralController();
    $controller->verVigentes();
    return;
}

// Rutas de ofertas laborales (flexible)
if (
    strpos($uri, '/ofertas') !== false || strpos($uri, '/ofertalabor') !== false ||
    strpos($uri, '/ofertalaboral') !== false || strpos($uri, '/ofertaslaborales') !== false
) {
    $controller = new OfertaLaboralController();

    if ($uri == '/ofertas-laborales/vigentes' && $method == 'GET') {
        $controller->verVigentes();
        return;
    } elseif (preg_match('#^/ofertas-laborales/ubicacion/([^/]+)$#', $uri, $matches) && $method == 'GET') {
        $controller->porUbicacion($matches[1]);
        return;
    } elseif (preg_match('#^/ofertas-laborales/contrato/([^/]+)$#', $uri, $matches) && $method == 'GET') {
        $controller->porTipoContrato($matches[1]);
        return;
    } elseif (preg_match('#^/ofertas-laborales/([0-9]+)$#', $uri, $matches) && $method == 'GET') {
        $controller->detalle($matches[1]);
        return;
    } elseif ($uri == '/ofertas' && $method == 'POST') {
        $controller->crearOferta();
        return;
    } elseif (preg_match('#^/ofertas/([0-9]+)$#', $uri, $matches) && $method == 'PUT') {
        $controller->editarOferta($matches[1]);
        return;
    } elseif (preg_match('#^/ofertas/([0-9]+)/desactivar$#', $uri, $matches) && $method == 'PATCH') {
        $controller->desactivarOferta($matches[1]);
        return;
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