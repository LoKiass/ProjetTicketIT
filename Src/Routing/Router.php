<?php

namespace Routing;

class Router
{
    private string $routes;
    private string $uri;
    public function __construct()
    {
        $routes = require_once 'Routes.php';

        $requestUri = $_SERVER['REQUEST_URI'];

        $regexp = "/^\/ProjectTicketIT\/([^?]*)[?]?.*/";
        if (preg_match($regexp, $_SERVER['REQUEST_URI'], $matches)) {
            $uri = $matches[1];
            echo $uri;
        }
        else {
            $uri = '';
        }
    }

}