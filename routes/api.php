<?php

use Controllers\ProductoController;
use Controllers\UsuarioController;
use Controllers\ClienteController;
use Controllers\PedidoController;
use Controllers\PostulacionController;
use Controllers\OfertaLaboralController;
use Controllers\AntecedenteAcademicoController;
use Controllers\AntecedenteLaboralController;

$router->get('/productos', ProductoController::class . '@getProductos');
$router->get('/productos/1', ProductoController::class . '@getProducto');
$router->post('/productos', ProductoController::class . '@createProducto');
$router->put('/productos/1', ProductoController::class . '@updateProducto');
$router->delete('/productos/1', ProductoController::class . '@deleteProducto');

$router->get('/usuarios', UsuarioController::class . '@getUsuarios');
$router->get('/usuarios/1', UsuarioController::class . '@getUsuario');
$router->post('/usuarios', UsuarioController::class . '@createUsuario');

$router->get('/clientes', ClienteController::class . '@getClientes');
$router->post('/clientes', ClienteController::class . '@createCliente');
$router->put('/clientes/1', ClienteController::class . '@updateCliente');
$router->delete('/clientes/1', ClienteController::class . '@deleteCliente');

$router->get('/pedidos', PedidoController::class . '@getPedidos');
$router->get('/pedidos/1', PedidoController::class . '@getPedido');
$router->post('/pedidos', PedidoController::class . '@createPedido');
$router->put('/pedidos/1', PedidoController::class . '@updatePedido');
$router->delete('/pedidos/1', PedidoController::class . '@deletePedido');

$router->get('/postulaciones', PostulacionController::class . '@getPostulaciones');
$router->get('/postulaciones/1', PostulacionController::class . '@getPostulacion');
$router->post('/postulaciones', PostulacionController::class . '@createPostulacion');
$router->put('/postulaciones/1', PostulacionController::class . '@updatePostulacion');
$router->delete('/postulaciones/1', PostulacionController::class . '@deletePostulacion');

$router->get('/ofertas-laborales', OfertaLaboralController::class . '@getOfertasLaborales');
$router->get('/ofertas-laborales/1', OfertaLaboralController::class . '@getOfertaLaboral');
$router->post('/ofertas-laborales', OfertaLaboralController::class . '@createOfertaLaboral');
$router->put('/ofertas-laborales/1', OfertaLaboralController::class . '@updateOfertaLaboral');
$router->delete('/ofertas-laborales/1', OfertaLaboralController::class . '@deleteOfertaLaboral');

$router->get('/antecedentes-academicos', AntecedenteAcademicoController::class . '@getAntecedentesAcademicos');
$router->post('/antecedentes-academicos', AntecedenteAcademicoController::class . '@createAntecedenteAcademico');

$router->get('/antecedentes-laborales', AntecedenteLaboralController::class . '@getAntecedentesLaborales');
$router->post('/antecedentes-laborales', AntecedenteLaboralController::class . '@createAntecedenteLaboral');

?>