<?php

namespace Controllers;

use Response;

class OfertaLaboralController {

    public function getOfertasLaborales() {
        Response::json(['message' => 'Lista de ofertas laborales']);
    }

    public function getOfertaLaboral($id) {
        Response::json(['message' => 'Oferta laboral ' . $id]);
    }

    public function createOfertaLaboral() {
        Response::json(['message' => 'Oferta laboral creada']);
    }

    public function updateOfertaLaboral($id) {
        Response::json(['message' => 'Oferta laboral ' . $id . ' actualizada']);
    }

    public function deleteOfertaLaboral($id) {
        Response::json(['message' => 'Oferta laboral ' . $id . ' eliminada']);
    }

    /**
     * Eliminar una oferta laboral
     */
    public function eliminar($id) {
        $query = "DELETE FROM OfertaLaboral WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
}