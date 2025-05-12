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

// Limpiar la URI - eliminar index.php si está presente
$uri = str_replace('/index.php', '', $uri);

// Detectar rutas de ofertas laborales (flexible)
if (
    (strpos($uri, '/cliente-feliz-api/ofertalabor') !== false ||
        strpos($uri, '/cliente-feliz-api/api/ofertalabor') !== false) &&
    $method == 'GET' &&
    !preg_match('#/[0-9]+$#', $uri) && // Excluir patrones que terminen en ID numérico
    !strpos($uri, '/ubicacion/') &&
    !strpos($uri, '/contrato/')
) {

    $controller = new OfertaLaboralController();
    if (method_exists($controller, 'verVigentes')) {
        $controller->verVigentes();
    } else {
        echo json_encode(['success' => false, 'message' => 'Método verVigentes no implementado']);
    }
    return;
}

// Detectar si es una ruta de usuario (singular)
if (($uri == '/cliente-feliz-api/usuario' || $uri == '/cliente-feliz-api/api/usuario') && $method == 'GET') {
    $controller = new UsuarioController();
    if (method_exists($controller, 'getUsuarios')) {
        $controller->getUsuarios();
    } else {
        echo json_encode(['success' => false, 'message' => 'Método getUsuarios no implementado']);
    }
    return;
}

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

// 1. RUTAS DE OFERTAS LABORALES

// Crear nueva oferta laboral (POST /ofertas)
if (($uri == '/cliente-feliz-api/api/ofertas' || $uri == '/cliente-feliz-api/ofertas') && $method == 'POST') {
    $controller = new OfertaLaboralController();
    if (method_exists($controller, 'crearOferta')) {
        $controller->crearOferta();
    } else {
        echo json_encode(['success' => false, 'message' => 'Método crearOferta no implementado']);
    }
    return;
}

// Editar oferta laboral existente (PUT /ofertas/{id})
if (preg_match('#^/cliente-feliz-api/api/ofertas/([0-9]+)$#', $uri, $matches) && $method == 'PUT') {
    $controller = new OfertaLaboralController();
    if (method_exists($controller, 'editarOferta')) {
        $controller->editarOferta($matches[1]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Método editarOferta no implementado']);
    }
    return;
}

// Desactivar oferta laboral (PATCH /ofertas/{id}/desactivar)
if (preg_match('#^/cliente-feliz-api/api/ofertas/([0-9]+)/desactivar$#', $uri, $matches) && $method == 'PATCH') {
    $controller = new OfertaLaboralController();
    if (method_exists($controller, 'desactivarOferta')) {
        $controller->desactivarOferta($matches[1]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Método desactivarOferta no implementado']);
    }
    return;
}

// 2. RUTAS DE POSTULACIONES

// Postularse a una oferta (POST /postulaciones)
if (($uri == '/cliente-feliz-api/api/postulaciones' || $uri == '/cliente-feliz-api/postulaciones') && $method == 'POST') {
    $controller = new PostulacionController();
    if (method_exists($controller, 'crearPostulacion')) {
        $controller->crearPostulacion();
    } else {
        echo json_encode(['success' => false, 'message' => 'Método crearPostulacion no implementado']);
    }
    return;
}

// Ver postulantes de una oferta (GET /postulaciones/oferta/{id})
if (preg_match('#^/cliente-feliz-api/api/postulaciones/oferta/([0-9]+)$#', $uri, $matches) && $method == 'GET') {
    $controller = new PostulacionController();
    if (method_exists($controller, 'porOferta')) {
        $controller->porOferta($matches[1]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Método porOferta no implementado']);
    }
    return;
}

// Cambiar estado de la postulación (PATCH /postulaciones/{id}/estado)
if (preg_match('#^/cliente-feliz-api/api/postulaciones/([0-9]+)/estado$#', $uri, $matches) && $method == 'PATCH') {
    $controller = new PostulacionController();
    if (method_exists($controller, 'cambiarEstado')) {
        $controller->cambiarEstado($matches[1]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Método cambiarEstado no implementado']);
    }
    return;
}

// Agregar comentario al estado actual (PATCH /postulaciones/{id}/comentario)
if (preg_match('#^/cliente-feliz-api/api/postulaciones/([0-9]+)/comentario$#', $uri, $matches) && $method == 'PATCH') {
    $controller = new PostulacionController();
    if (method_exists($controller, 'agregarComentario')) {
        $controller->agregarComentario($matches[1]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Método agregarComentario no implementado']);
    }
    return;
}

// Si ninguna ruta coincide
echo json_encode(['success' => false, 'message' => 'Ruta no encontrada']);
