<?php

namespace DISEUMAT\Exception;

class JobsDateFurtherThanProject extends \Exception
{
    public function __construct(string $message = "")
    {
        parent::__construct($message, 0);
    }
}