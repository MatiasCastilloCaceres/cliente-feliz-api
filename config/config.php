<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'consultoria');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');

define('API_VERSION', '1.0');
define('DEFAULT_LANGUAGE', 'es');

require_once __DIR__ . '/../config/database.php';

class NombreModelo
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Métodos del modelo...
}

class Producto
{
    private $db;
    public function __construct()
    {
        $this->db = new PDO("mysql:host=localhost;dbname=consultoria", "root", "");
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // Resto del código...
}

class Pedido
{
    private $db;
    public function __construct()
    {
        $this->db = new PDO("mysql:host=localhost;dbname=consultoria", "root", "");
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // Resto del código...
}

class Cliente
{
    private $db;
    public function __construct()
    {
        $this->db = new PDO("mysql:host=localhost;dbname=consultoria", "root", "");
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // Resto del código...
}
?>