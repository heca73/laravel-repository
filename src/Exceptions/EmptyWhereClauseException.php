<?php

namespace Heca73\LaravelRepository\Exceptions;

use RuntimeException;

class EmptyWhereClauseException extends RuntimeException
{
    /**
     * Exception error code
     *
     * @var int $code
     */
    protected $code = 500;

    public function setMessage(mixed $message): void
    {
        $this->message = $message ?? "Fatal Error ! Can't proceed repository action without any where clause";
    }
}