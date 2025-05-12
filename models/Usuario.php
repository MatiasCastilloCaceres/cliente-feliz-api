<?php
// filepath: c:\xampp\htdocs\cliente-feliz-api\models\Usuario.php
require_once __DIR__ . '/../config/database.php';

class Usuario
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAll()
    {
        // Actualizado para usar solo las columnas que existen en la tabla
        $query = "SELECT id, nombre, apellido, email, rol, fecha_creacion 
                  FROM usuarios 
                  ORDER BY fecha_creacion DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $usuarios = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Eliminar datos sensibles como contraseñas
            $usuarios[] = $row;
        }

        return $usuarios;
    }

    public function emailExiste($email)
    {
        $query = "SELECT COUNT(*) FROM usuarios WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    public function crear($data)
    {
        // Hashear la contraseña
        $passwordHash = password_hash($data['contraseña'], PASSWORD_DEFAULT);

        // Actualizado para usar solo las columnas que existen en la tabla
        $query = "INSERT INTO usuarios (nombre, apellido, email, contraseña, rol) 
                  VALUES (:nombre, :apellido, :email, :password, :rol)";
                  
        $stmt = $this->conn->prepare($query);
        
        // Campos obligatorios
        $stmt->bindParam(':nombre', $data['nombre'], PDO::PARAM_STR);
        $stmt->bindParam(':apellido', $data['apellido'], PDO::PARAM_STR);
        $stmt->bindParam(':email', $data['email'], PDO::PARAM_STR);
        $stmt->bindParam(':password', $passwordHash, PDO::PARAM_STR);
        $stmt->bindParam(':rol', $data['rol'], PDO::PARAM_STR);
        
        // La fecha_creacion se establece automáticamente en la base de datos

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }
    
    // Método para obtener un usuario por su ID
    public function getById($id)
    {
        $query = "SELECT id, nombre, apellido, email, rol, fecha_creacion 
                  FROM usuarios 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Método para actualizar un usuario
    public function actualizar($id, $data)
    {
        // Iniciar con la parte básica de la consulta
        $query = "UPDATE usuarios SET ";
        $updateFields = [];
        $params = [];
        
        // Agregar campos a actualizar si están presentes
        if (!empty($data['nombre'])) {
            $updateFields[] = "nombre = :nombre";
            $params[':nombre'] = $data['nombre'];
        }
        
        if (!empty($data['apellido'])) {
            $updateFields[] = "apellido = :apellido";
            $params[':apellido'] = $data['apellido'];
        }
        
        if (!empty($data['email'])) {
            $updateFields[] = "email = :email";
            $params[':email'] = $data['email'];
        }
        
        if (!empty($data['contraseña'])) {
            $updateFields[] = "contraseña = :password";
            $params[':password'] = password_hash($data['contraseña'], PASSWORD_DEFAULT);
        }
        
        if (!empty($data['rol'])) {
            $updateFields[] = "rol = :rol";
            $params[':rol'] = $data['rol'];
        }
        
        // Si no hay campos para actualizar, retornar falso
        if (empty($updateFields)) {
            return false;
        }
        
        // Completar la consulta
        $query .= implode(", ", $updateFields);
        $query .= " WHERE id = :id";
        $params[':id'] = $id;
        
        // Ejecutar la consulta
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute($params);
    }
    
    // Método para eliminar un usuario
    public function eliminar($id)
    {
        $query = "DELETE FROM usuarios WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
}
?>