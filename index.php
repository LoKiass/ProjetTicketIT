<?php
session_start();
require_once 'vendor/autoload.php'; // Auto-loader php
require_once 'config.php'; // Fichier de configuration production et hors production

use Routing\Router; // Router

try {
    $router = new Router();
    $Ctrl = $router->get_Ctrl();
    $Action = $router->get_Action();

    $controllerClassName = $rootDir . "\\Controller\\" . ucfirst($Ctrl) . "Controller";

    if ($Ctrl !== 'notfound' && class_exists($controllerClassName)) {
        $controller = new $controllerClassName();

        if (method_exists($controller, $Action)) {
            $controller->$Action();
        } else {
            echo "Erreur : La méthode $Action n'existe pas dans la classe $controllerClassName.";
        }
    } else { // Dans le cas ou l'utilisateur fournit un controller qui n'existe pas
        header("HTTP/1.0 404 Not Found");
    }
} catch (Exception $e) { // Gestion des exceptions
    echo "Erreur système : " . $e->getMessage();
}