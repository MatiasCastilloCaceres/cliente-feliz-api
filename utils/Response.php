<?php
// filepath: c:\xampp\htdocs\cliente-feliz-api\utils\Response.php
namespace Utils;

class Response
{
    public static function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
}
?>