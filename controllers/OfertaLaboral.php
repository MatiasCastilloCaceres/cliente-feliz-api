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

}