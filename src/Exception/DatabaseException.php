<?php

namespace App\Exception;

class DatabaseException extends \RuntimeException
{
    public function __construct($message = 'Erreur de base de données', $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}