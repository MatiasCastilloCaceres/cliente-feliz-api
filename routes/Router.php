<?php
namespace Routes;

use Controllers\ClienteController;
use Controllers\PedidoController;
use Controllers\ProductoController;
use Utils\Response;

class Router
{
    private $routes = [];

    public function __construct()
    {
        // Elimina el método initializeRoutes() que crea la estructura incorrecta
        // Ahora solo inicializamos un array vacío
        $this->routes = [];
    }

    public function addRoute($method, $path, $handler)
    {
        // Añadir la ruta a la estructura correcta
        if (!isset($this->routes[$method])) {
            $this->routes[$method] = [];
        }

        $this->routes[$method][$path] = $handler;
    }

    public function get($path, $handler)
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post($path, $handler)
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function put($path, $handler)
    {
        $this->addRoute('PUT', $path, $handler);
    }

    public function delete($path, $handler)
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    public function route($uri, $method)
    {
        $path = parse_url($uri, PHP_URL_PATH);

        $basePath = '/cliente-feliz-api';
        if (strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
        }

        if (empty($path)) {
            $path = '/';
        }

        // Verificar si hay rutas definidas para este método HTTP
        if (!isset($this->routes[$method])) {
            $this->sendNotFound();
            return false;
        }

        // Buscar rutas exactas primero
        if (isset($this->routes[$method][$path])) {
            $handler = $this->routes[$method][$path];
            return $this->executeHandler($handler, []);
        }

        // Si no hay coincidencia exacta, buscar rutas con parámetros
        foreach ($this->routes[$method] as $routePath => $handler) {
            $pattern = $this->convertRouteToRegex($routePath);

            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches); // Eliminar la coincidencia completa
                return $this->executeHandler($handler, $matches);
            }
        }

        // No se encontró ninguna ruta coincidente
        $this->sendNotFound();
        return false;
    }

    private function executeHandler($handler, $params)
    {
        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
            return true;
        } else if (is_array($handler) && count($handler) == 2) {
            // Si es un controlador en forma de array [Clase, método]
            $controllerClass = $handler[0];
            $method = $handler[1];

            // Si es una referencia de clase estática (ClassName::class)
            if (is_string($controllerClass)) {
                $controller = new $controllerClass();
            } else {
                $controller = $controllerClass;
            }

            if (method_exists($controller, $method)) {
                call_user_func_array([$controller, $method], $params);
                return true;
            }
        }

        $this->sendNotFound();
        return false;
    }

    private function sendNotFound()
    {
        // Verifica si la clase Response existe
        if (class_exists('\Utils\Response')) {
            \Utils\Response::json([
                'success' => false,
                'message' => 'Ruta no encontrada'
            ], 404);
        } else {
            // Si no existe, usa una respuesta estándar
            header('HTTP/1.1 404 Not Found');
            echo json_encode([
                'success' => false,
                'message' => 'Ruta no encontrada'
            ]);
        }
    }

    private function convertRouteToRegex($route)
    {
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $route);
        $pattern = str_replace('/', '\/', $pattern);
        return '/^' . $pattern . '$/';
    }

    public function resolve($method, $uri)
    {
        $parsedUrl = parse_url($uri);
        $path = $parsedUrl['path'];

        // Eliminar el base path (como /cliente-feliz-api)
        $basePath = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
        $path = '/' . trim(str_replace($basePath, '', $path), '/');

        if (!isset($this->routes[$method][$path])) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Ruta no encontrada']);
            return;
        }

        $handler = $this->routes[$method][$path];
        [$controllerName, $methodName] = explode('@', $handler);
        $controller = new $controllerName();
        $controller->$methodName();
    }
}
