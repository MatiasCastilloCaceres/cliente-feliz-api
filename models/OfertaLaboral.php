<?php
// filepath: c:\xampp\htdocs\cliente-feliz-api\models\OfertaLaboral.php
require_once __DIR__ . '/../config/database.php';

class OfertaLaboral
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Obtener ofertas vigentes
    public function getVigentes()
    {
        $query = "SELECT o.*, u.nombre as nombre_reclutador 
                 FROM OfertaLaboral o
                 JOIN Usuario u ON o.reclutador_id = u.id
                 WHERE o.estado = 'Vigente'
                 ORDER BY o.fecha_publicacion DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $ofertas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ofertas[] = $row;
        }

        return $ofertas;
    }

    // Obtener ofertas por ubicación
    public function getByUbicacion($ubicacion)
    {
        $query = "SELECT o.*, u.nombre as nombre_reclutador 
                 FROM OfertaLaboral o
                 JOIN Usuario u ON o.reclutador_id = u.id
                 WHERE o.ubicacion LIKE :ubicacion AND o.estado = 'Vigente'
                 ORDER BY o.fecha_publicacion DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':ubicacion', "%$ubicacion%", PDO::PARAM_STR);
        $stmt->execute();

        $ofertas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ofertas[] = $row;
        }

        return $ofertas;
    }

    // Obtener ofertas por tipo de contrato
    public function getByTipoContrato($tipo)
    {
        $query = "SELECT o.*, u.nombre as nombre_reclutador 
                 FROM OfertaLaboral o
                 JOIN Usuario u ON o.reclutador_id = u.id
                 WHERE o.tipo_contrato = :tipo AND o.estado = 'Vigente'
                 ORDER BY o.fecha_publicacion DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':tipo', $tipo, PDO::PARAM_STR);
        $stmt->execute();

        $ofertas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ofertas[] = $row;
        }

        return $ofertas;
    }

    // Obtener oferta por ID
    public function getById($id)
    {
        $query = "SELECT o.*, u.nombre as nombre_reclutador, u.email as email_reclutador 
                 FROM OfertaLaboral o
                 JOIN Usuario u ON o.reclutador_id = u.id
                 WHERE o.id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Crear nueva oferta laboral
    public function crear($data)
    {
        $query = "INSERT INTO OfertaLaboral (titulo, descripcion, ubicacion, salario, tipo_contrato, 
                  reclutador_id, fecha_publicacion, fecha_cierre, estado) 
                  VALUES (:titulo, :descripcion, :ubicacion, :salario, :tipo_contrato, 
                  :reclutador_id, CURRENT_TIMESTAMP, :fecha_cierre, 'Activa')";

        // Valores predeterminados para campos opcionales
        $ubicacion = isset($data['ubicacion']) ? $data['ubicacion'] : '';
        $salario = isset($data['salario']) ? $data['salario'] : '';
        $tipoContrato = isset($data['tipo_contrato']) ? $data['tipo_contrato'] : '';
        $fechaCierre = isset($data['fecha_cierre']) ? $data['fecha_cierre'] : null;

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':titulo', $data['titulo'], PDO::PARAM_STR);
        $stmt->bindParam(':descripcion', $data['descripcion'], PDO::PARAM_STR);
        $stmt->bindParam(':ubicacion', $ubicacion, PDO::PARAM_STR);
        $stmt->bindParam(':salario', $salario, PDO::PARAM_STR);
        $stmt->bindParam(':tipo_contrato', $tipoContrato, PDO::PARAM_STR);
        $stmt->bindParam(':reclutador_id', $data['reclutador_id'], PDO::PARAM_INT);
        $stmt->bindParam(':fecha_cierre', $fechaCierre, PDO::PARAM_STR);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    // Actualizar oferta laboral
    public function actualizar($id, $data)
    {
        // Construir la consulta de actualización dinámicamente
        $query = "UPDATE OfertaLaboral SET ";
        $params = [];

        // Campos que se pueden actualizar
        $campos = [
            'titulo' => PDO::PARAM_STR,
            'descripcion' => PDO::PARAM_STR,
            'ubicacion' => PDO::PARAM_STR,
            'salario' => PDO::PARAM_STR,
            'tipo_contrato' => PDO::PARAM_STR,
            'fecha_cierre' => PDO::PARAM_STR,
            'estado' => PDO::PARAM_STR
        ];

        // Añadir solo los campos que vienen en la solicitud
        $actualizaciones = [];
        foreach ($campos as $campo => $tipo) {
            if (isset($data[$campo])) {
                $actualizaciones[] = "$campo = :$campo";
                $params[$campo] = [$data[$campo], $tipo];
            }
        }

        // Si no hay campos para actualizar, retornar true
        if (empty($actualizaciones)) {
            return true;
        }

        $query .= implode(', ', $actualizaciones);
        $query .= " WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Bind de los parámetros
        foreach ($params as $key => $valor) {
            $stmt->bindValue(":$key", $valor[0], $valor[1]);
        }

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Cambiar estado de oferta laboral
    public function cambiarEstado($id, $estado)
    {
        $query = "UPDATE OfertaLaboral SET estado = :estado WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Añadir este nuevo método
    public function actualizarParcial($id, $data)
    {
        // Construir la consulta SQL de forma dinámica
        $sql = "UPDATE OfertaLaboral SET ";
        $params = [];

        // Campos permitidos para actualización
        $camposPermitidos = [
            'titulo',
            'descripcion',
            'ubicacion',
            'salario',
            'tipo_contrato',
            'fecha_cierre',
            'estado'
        ];

        // Agregar cada campo a la consulta si está presente en los datos
        $actualizaciones = [];
        foreach ($camposPermitidos as $campo) {
            if (isset($data[$campo])) {
                $actualizaciones[] = "$campo = :$campo";
                $params[":$campo"] = $data[$campo];
            }
        }

        // Si no hay campos para actualizar, retornar falso
        if (empty($actualizaciones)) {
            return false;
        }

        // Completar la consulta
        $sql .= implode(", ", $actualizaciones);
        $sql .= " WHERE id = :id";
        $params[':id'] = $id;

        // Ejecutar la consulta
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute($params);
    }
}
?>