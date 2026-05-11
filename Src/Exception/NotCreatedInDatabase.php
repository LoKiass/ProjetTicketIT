<?php

namespace DISEUMAT\Exception;

class NotCreatedInDatabase extends \Exception
{
    public function __construct(string $message = ""){
        parent::__construct($message, 0);
    }
}