<?php
class Validator
{
    public static function validateClienteData($data)
    {
        $errors = [];

        if (empty($data['nombre'])) {
            $errors[] = 'El nombre es obligatorio.';
        }

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El email es obligatorio y debe ser válido.';
        }

        if (empty($data['telefono'])) {
            $errors[] = 'El teléfono es obligatorio.';
        }

        return $errors;
    }

    public static function validatePedidoData($data)
    {
        $errors = [];

        if (empty($data['cliente_id'])) {
            $errors[] = 'El ID del cliente es obligatorio.';
        }

        if (empty($data['producto_id'])) {
            $errors[] = 'El ID del producto es obligatorio.';
        }

        if (empty($data['cantidad']) || !is_numeric($data['cantidad'])) {
            $errors[] = 'La cantidad es obligatoria y debe ser un número.';
        }

        return $errors;
    }

    public static function validateProductoData($data)
    {
        $errors = [];

        if (empty($data['nombre'])) {
            $errors[] = 'El nombre del producto es obligatorio.';
        }

        if (empty($data['precio']) || !is_numeric($data['precio'])) {
            $errors[] = 'El precio es obligatorio y debe ser un número.';
        }

        return $errors;
    }
}
