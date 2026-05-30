<?php

namespace Routing;

class Router
{
    private array $routes;
    private string $uri = '';

    /*
     * La classe Router permet d'établir la relation entre l'URI et le controller et l'action'
     * Elle lie aussi les Controller et les actions à l'aide d'une table des routes'
     */
    public function __construct()
    {
        $this->routes = require_once 'Routes.php';
        $requestUri = $_SERVER['REQUEST_URI'];
        $regexp = "/.*\/DISEUMAT\/([^?]*)[?]?.*/";

        if (preg_match($regexp, $requestUri, $matches)) {
            $this->uri = $matches[1] ?? '';
        }
    }

    /*
     * Cette méthode permet de récuper le nom du controller dans la table des routes
     * Dans le cas ou le controller n'existe pas, elle retourne notfound
     */
    public function get_Ctrl(): string
    {
        if (array_key_exists($this->uri, $this->routes)) {
            $CtrlAct = $this->routes[$this->uri];
            return key($CtrlAct);
        }
        return 'notfound';
    }

    /*
     * Cette méthode permet de récuper l'action du controller dans la table des routes
     * Dans le cas ou l'action n'existe pas, elle retourne notfound
     */
    public function get_Action(): string
    {
        if (array_key_exists($this->uri, $this->routes)) {
            $CtrlAct = $this->routes[$this->uri];
            return current($CtrlAct);
        }
        return 'notfound';
    }
}