<?php

namespace YezBot\Exceptions;

use Exception;
use Throwable;

class AppException extends Exception
{
    protected $error;

    public function __construct(string $error = "", string $message = "", int $code = 0, Throwable $previous = null)
    {
        $this->setError($error);

        parent::__construct($message, $code, $previous);
    }

    public function setError($error)
    {
        $this->error = $error;
    }

    public function getError()
    {
        return $this->error;
    }
}
