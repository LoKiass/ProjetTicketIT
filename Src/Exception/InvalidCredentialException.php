<?php

namespace DISEUMAT\Exception;

class InvalidCredentialException extends \Exception
{
    public function __construct(string $message = "")
    {
        parent::__construct($message, 0);
    }
}