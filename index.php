<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
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

// Detectar si es una ruta de postulaciones (singular o plural)
if (
    (strpos($uri, '/cliente-feliz-api/postulacion') !== false ||
        strpos($uri, '/cliente-feliz-api/api/postulacion') !== false) &&
    $method == 'GET'
) {

    $controller = new PostulacionController();
    if (method_exists($controller, 'getPostulaciones')) {
        $controller->getPostulaciones();
    } else {
        echo json_encode(['success' => false, 'message' => 'Método getPostulaciones no implementado']);
    }
    return;
}

// Nueva ruta para /usuario
if (($uri == '/cliente-feliz-api/api/usuario' || $uri == '/cliente-feliz-api/usuario') && $method == 'GET') {
    $controller = new UsuarioController();
    // Asumiendo que tienes un método para obtener usuarios
    if (method_exists($controller, 'getUsuarios')) {
        $controller->getUsuarios();
    } else {
        echo json_encode(['success' => false, 'message' => 'Método no implementado']);
    }
    return;
}

// Nueva ruta para obtener todas las postulaciones
if ($uri == '/cliente-feliz-api/api/postulaciones' && $method == 'GET') {
    $controller = new PostulacionController();
    if (method_exists($controller, 'getPostulaciones')) {
        $controller->getPostulaciones();
    } else {
        echo json_encode(['success' => false, 'message' => 'Método getPostulaciones no implementado']);
    }
    return;
}

// Rutas existentes
if ($uri == '/cliente-feliz-api/api/ofertas-laborales/vigentes' && $method == 'GET') {
    $controller = new OfertaLaboralController();
    $controller->verVigentes();
    return;
} elseif (preg_match('#^/cliente-feliz-api/api/ofertas-laborales/ubicacion/([^/]+)$#', $uri, $matches) && $method == 'GET') {
    $controller = new OfertaLaboralController();
    $controller->porUbicacion($matches[1]);
    return;
} elseif (preg_match('#^/cliente-feliz-api/api/ofertas-laborales/contrato/([^/]+)$#', $uri, $matches) && $method == 'GET') {
    $controller = new OfertaLaboralController();
    $controller->porTipoContrato($matches[1]);
    return;
} elseif (preg_match('#^/cliente-feliz-api/api/ofertas-laborales/([0-9]+)$#', $uri, $matches) && $method == 'GET') {
    $controller = new OfertaLaboralController();
    $controller->detalle($matches[1]);
    return;
} elseif ($uri == '/cliente-feliz-api/api/usuarios' && $method == 'POST') {
    $controller = new UsuarioController();
    $controller->crearUsuario();
    return;
} elseif (preg_match('#^/cliente-feliz-api/api/postulaciones/candidato/([0-9]+)$#', $uri, $matches) && $method == 'GET') {
    $controller = new PostulacionController();
    $controller->porCandidato($matches[1]);
    return;
}

// Si ninguna ruta coincide
echo json_encode(['success' => false, 'message' => 'Ruta no encontrada']);
