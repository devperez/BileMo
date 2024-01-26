<?php

namespace App\Exception;

/**
 * Custom exception class for database-related errors.
 *
 * This exception can be thrown when there is an error related to the database operations.
 *
 */
class DatabaseException extends \RuntimeException
{
    /**
     * DatabaseException constructor.
     *
     * @param string          $message  The error message.
     * @param int             $code     The error code.
     * @param \Throwable|null $previous The previous exception if any.
     */

    public function __construct($message = 'Erreur relative à la base de données', $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}