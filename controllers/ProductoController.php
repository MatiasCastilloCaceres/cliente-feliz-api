<?php
namespace Controllers;

use Models\Producto;
use Utils\Response;
use Utils\Validator;

class ProductoController
{
    private $db;

    public function __construct()
    {
        $database = new \Database();
        $this->db = $database->getConnection();
    }

    public function getProductos()
    {
        // Implementa la lógica para obtener todos los productos
        Response::json([
            'success' => true,
            'message' => 'Método getProductos llamado correctamente'
        ]);
    }

    public function getProducto($id)
    {
        $producto = Producto::find($id);

        if ($producto) {
            Response::json(['success' => true, 'data' => $producto], 200);
        } else {
            Response::json(['success' => false, 'message' => 'Producto no encontrado.'], 404);
        }
    }

    public function createProducto($data)
    {
        $producto = new Producto();
        $producto->setNombre($data['nombre']);
        $producto->setPrecio($data['precio']);
        $producto->setDescripcion($data['descripcion']);

        if ($producto->save()) {
            Response::json(['success' => true, 'message' => 'Producto creado exitosamente.'], 201);
        } else {
            Response::json(['success' => false, 'message' => 'Error al crear el producto.'], 500);
        }
    }

    public function updateProducto($id, $data)
    {
        $producto = Producto::find($id);

        if ($producto) {
            $producto->setNombre($data['nombre']);
            $producto->setPrecio($data['precio']);
            $producto->setDescripcion($data['descripcion']);

            if ($producto->save()) {
                Response::json(['success' => true, 'message' => 'Producto actualizado exitosamente.'], 200);
            } else {
                Response::json(['success' => false, 'message' => 'Error al actualizar el producto.'], 500);
            }
        } else {
            Response::json(['success' => false, 'message' => 'Producto no encontrado.'], 404);
        }
    }

    public function deleteProducto($id)
    {
        $producto = Producto::find($id);

        if ($producto) {
            if ($producto->delete()) {
                Response::json(['success' => true, 'message' => 'Producto eliminado exitosamente.'], 200);
            } else {
                Response::json(['success' => false, 'message' => 'Error al eliminar el producto.'], 500);
            }
        } else {
            Response::json(['success' => false, 'message' => 'Producto no encontrado.'], 404);
        }
    }
}

namespace Models;

class Producto
{
    private $id;
    private $nombre;
    private $precio;
    private $descripcion;
    private $db;

    public function __construct()
    {
        $database = new \Database();
        $this->db = $database->getConnection();
    }

    public static function find($id)
    {
        $database = new \Database();
        $db = $database->getConnection();

        $query = "SELECT * FROM productos WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$id]);

        if ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $producto = new self();
            $producto->id = $row['id'];
            $producto->nombre = $row['nombre'];
            $producto->precio = $row['precio'];
            $producto->descripcion = $row['descripcion'];
            return $producto;
        }

        return null;
    }

    public function save()
    {
        if (isset($this->id)) {
            // Update existing record
            $query = "UPDATE productos SET nombre = ?, precio = ?, descripcion = ? WHERE id = ?";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([$this->nombre, $this->precio, $this->descripcion, $this->id]);
        } else {
            // Insert new record
            $query = "INSERT INTO productos (nombre, precio, descripcion) VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([$this->nombre, $this->precio, $this->descripcion]);
        }
    }

    public function delete()
    {
        $query = "DELETE FROM productos WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$this->id]);
    }

    // Getters and Setters
    public function getId()
    {
        return $this->id;
    }

    public function getNombre()
    {
        return $this->nombre;
    }

    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    }

    public function getPrecio()
    {
        return $this->precio;
    }

    public function setPrecio($precio)
    {
        $this->precio = $precio;
    }

    public function getDescripcion()
    {
        return $this->descripcion;
    }

    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;
    }
}
?>