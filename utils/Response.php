<?php
// filepath: c:\xampp\htdocs\cliente-feliz-api\utils\Response.php
namespace Utils;

class Response
{
    /**
     * Envía una respuesta JSON
     *
     * @param array $data Los datos a convertir a JSON
     * @param int $statusCode Código de estado HTTP (default: 200)
     */
    public static function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
?>