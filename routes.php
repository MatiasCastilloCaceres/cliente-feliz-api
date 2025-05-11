
<?php
require_once 'controllers/OfertaLaboralController.php';

$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

$controller = new OfertaLaboralController();

if ($uri == '/cliente-feliz-api/api/ofertas-laborales/vigentes' && $method == 'GET') {
    $controller->verVigentes();
    return;
} elseif (preg_match('#^/cliente-feliz-api/api/ofertas-laborales/ubicacion/([^/]+)$#', $uri, $matches) && $method == 'GET') {
    $controller->porUbicacion($matches[1]);
    return;
} elseif (preg_match('#^/cliente-feliz-api/api/ofertas-laborales/contrato/([^/]+)$#', $uri, $matches) && $method == 'GET') {
    $controller->porTipoContrato($matches[1]);
    return;
} elseif (preg_match('#^/cliente-feliz-api/api/ofertas-laborales/([0-9]+)$#', $uri, $matches) && $method == 'GET') {
    $controller->detalle($matches[1]);
    return;
}

echo json_encode(['success' => false, 'message' => 'Ruta no encontrada']);
?>


require_once 'controllers/UsuarioController.php';
require_once 'controllers/PostulacionController.php';

if ($uri == '/cliente-feliz-api/api/usuarios' && $method == 'POST') {
    $controller = new UsuarioController();
    $controller->crearUsuario();
    return;
} elseif (preg_match('#^/cliente-feliz-api/api/postulaciones/candidato/([0-9]+)$#', $uri, $matches) && $method == 'GET') {
    $controller = new PostulacionController();
    $controller->porCandidato($matches[1]);
    return;
}
