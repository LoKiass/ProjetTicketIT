<?php
session_start();

include_once 'config.php';
require_once 'vendor/autoload.php';

use Routing\Router;

try{
    $router = new Router(); // Création de l'instance Router
} catch (Exception $e) {
    echo $e->getMessage();
    die();
}


