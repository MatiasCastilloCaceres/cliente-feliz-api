<?php
class Pedido {
    private $conn;
    private $table_name = "pedidos";

    public $id;
    public $cliente_id;
    public $producto_id;
    public $cantidad;
    public $fecha;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (cliente_id, producto_id, cantidad, fecha) VALUES (:cliente_id, :producto_id, :cantidad, :fecha)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':cliente_id', $this->cliente_id);
        $stmt->bindParam(':producto_id', $this->producto_id);
        $stmt->bindParam(':cantidad', $this->cantidad);
        $stmt->bindParam(':fecha', $this->fecha);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function get($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " SET cliente_id = :cliente_id, producto_id = :producto_id, cantidad = :cantidad, fecha = :fecha WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':cliente_id', $this->cliente_id);
        $stmt->bindParam(':producto_id', $this->producto_id);
        $stmt->bindParam(':cantidad', $this->cantidad);
        $stmt->bindParam(':fecha', $this->fecha);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>