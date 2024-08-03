<?php

namespace Heca73\LaravelRepository\Exceptions;

use RuntimeException;

class QueryNotFoundException extends RuntimeException
{
    /**
     * Exception error code
     *
     * @var int $code
     */
    protected $code = 404;

    public function setMessage(mixed $message): void
    {
        $this->message = $message ?? "Data not found !";
    }
}