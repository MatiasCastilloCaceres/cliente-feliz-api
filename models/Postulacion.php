<?php
// filepath: c:\xampp\htdocs\cliente-feliz-api\models\Postulacion.php
require_once __DIR__ . '/../config/database.php';

class Postulacion
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Obtener todas las postulaciones
    public function getAll()
    {
        $query = "SELECT p.*, o.titulo as titulo_oferta, u.nombre as nombre_candidato 
                 FROM Postulacion p
                 JOIN OfertaLaboral o ON p.oferta_laboral_id = o.id
                 JOIN Usuario u ON p.candidato_id = u.id
                 ORDER BY p.fecha_postulacion DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $postulaciones = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $postulaciones[] = $row;
        }

        return $postulaciones;
    }

    // Obtener postulaciones por candidato
    public function getByCandidato($candidatoId)
    {
        $query = "SELECT p.*, o.titulo as titulo_oferta, o.descripcion as descripcion_oferta, 
                 o.ubicacion, o.salario, o.tipo_contrato, o.fecha_publicacion, o.fecha_cierre,
                 u.nombre as nombre_reclutador, u.email as email_reclutador
                 FROM Postulacion p
                 JOIN OfertaLaboral o ON p.oferta_laboral_id = o.id
                 JOIN Usuario u ON o.reclutador_id = u.id
                 WHERE p.candidato_id = :candidato_id
                 ORDER BY p.fecha_postulacion DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':candidato_id', $candidatoId, PDO::PARAM_INT);
        $stmt->execute();

        $postulaciones = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $postulaciones[] = $row;
        }

        return $postulaciones;
    }

    // Obtener postulaciones por oferta
    public function getByOferta($ofertaId)
    {
        $query = "SELECT p.*, u.nombre as nombre_candidato, u.email as email_candidato, 
                 u.telefono, u.curriculum_url, u.linkedIn_url
                 FROM Postulacion p
                 JOIN Usuario u ON p.candidato_id = u.id
                 WHERE p.oferta_laboral_id = :oferta_id
                 ORDER BY p.fecha_postulacion DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':oferta_id', $ofertaId, PDO::PARAM_INT);
        $stmt->execute();

        $postulaciones = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $postulaciones[] = $row;
        }

        return $postulaciones;
    }

    // Obtener postulación por ID
    public function getById($id)
    {
        $query = "SELECT p.*, u.nombre as nombre_candidato, o.titulo as titulo_oferta
                 FROM Postulacion p
                 JOIN Usuario u ON p.candidato_id = u.id
                 JOIN OfertaLaboral o ON p.oferta_laboral_id = o.id
                 WHERE p.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Crear una nueva postulación
    public function crear($data)
    {
        $query = "INSERT INTO Postulacion 
                 (candidato_id, oferta_laboral_id, comentario, estado_postulacion) 
                 VALUES 
                 (:candidato_id, :oferta_laboral_id, :comentario, :estado_postulacion)";

        // Valores predeterminados y manejo de datos opcionales
        $comentario = isset($data['comentario']) ? $data['comentario'] : '';
        $estadoPostulacion = isset($data['estado_postulacion']) ? $data['estado_postulacion'] : 'Postulando';

        // Validar que el estado_postulacion esté entre los valores permitidos
        $estadosValidos = ['Postulando', 'Revisando', 'Entrevista Psicológica', 'Entrevista Personal', 'Seleccionado', 'Descartado'];
        if (!in_array($estadoPostulacion, $estadosValidos)) {
            $estadoPostulacion = 'Postulando'; // Valor por defecto si no es válido
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':candidato_id', $data['candidato_id'], PDO::PARAM_INT);
        $stmt->bindParam(':oferta_laboral_id', $data['oferta_laboral_id'], PDO::PARAM_INT);
        $stmt->bindParam(':comentario', $comentario, PDO::PARAM_STR);
        $stmt->bindParam(':estado_postulacion', $estadoPostulacion, PDO::PARAM_STR);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    // Actualizar estado de postulación
    public function actualizarEstado($id, $estado)
    {
        // Validar que el estado esté entre los valores permitidos
        $estadosValidos = ['Postulando', 'Revisando', 'Entrevista Psicológica', 'Entrevista Personal', 'Seleccionado', 'Descartado'];
        if (!in_array($estado, $estadosValidos)) {
            return false;
        }

        $query = "UPDATE Postulacion SET estado_postulacion = :estado WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Actualizar comentario de postulación
    public function actualizarComentario($id, $comentario)
    {
        $query = "UPDATE Postulacion SET comentario = :comentario WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':comentario', $comentario, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Actualización completa
    public function actualizar($id, $data)
    {
        // Verificar que los campos requeridos estén presentes
        if (!isset($data['candidato_id']) || !isset($data['oferta_laboral_id'])) {
            return false;
        }
        
        $query = "UPDATE Postulacion SET 
                  candidato_id = :candidato_id,
                  oferta_laboral_id = :oferta_laboral_id,
                  comentario = :comentario,
                  estado_postulacion = :estado_postulacion
                  WHERE id = :id";
        
        // Valores predeterminados y opcionales
        $comentario = isset($data['comentario']) ? $data['comentario'] : '';
        $estadoPostulacion = isset($data['estado_postulacion']) ? $data['estado_postulacion'] : 'Postulando';
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':candidato_id', $data['candidato_id'], PDO::PARAM_INT);
        $stmt->bindParam(':oferta_laboral_id', $data['oferta_laboral_id'], PDO::PARAM_INT);
        $stmt->bindParam(':comentario', $comentario, PDO::PARAM_STR);
        $stmt->bindParam(':estado_postulacion', $estadoPostulacion, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    // Actualización parcial
    public function actualizarParcial($id, $data)
    {
        // Construir la consulta SQL de forma dinámica
        $sql = "UPDATE Postulacion SET ";
        $params = [];
        
        // Campos permitidos para actualización
        $camposPermitidos = [
            'candidato_id', 'oferta_laboral_id', 'comentario', 'estado_postulacion'
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

    /**
     * Eliminar una postulación
     */
    public function eliminar($id)
    {
        $query = "DELETE FROM Postulacion WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Contar postulaciones por oferta
     */
    public function contarPorOferta($ofertaId)
    {
        $query = "SELECT COUNT(*) FROM Postulacion WHERE oferta_laboral_id = :ofertaId";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':ofertaId', $ofertaId, PDO::PARAM_INT);
        $stmt->execute();
        
        return (int)$stmt->fetchColumn();
    }
}
?>