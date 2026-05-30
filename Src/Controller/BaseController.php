<?php

namespace DISEUMAT\Controller;

use DISEUMAT\Model\Service\Session\UserSession;

class BaseController
{
    protected $TemplateEngine;
    protected $userLogged;

    public function __construct(){
        $loader = new \Twig\Loader\FilesystemLoader('Src/View'); // Chemin vers le dossier des templates

        $this->TemplateEngine = new \Twig\Environment($loader);
        $this->TemplateEngine->addGlobal('session', $_SESSION);
        $this->TemplateEngine->enableStrictVariables();
        $this->TemplateEngine->addExtension(new \Twig\Extension\DebugExtension());

        if (!empty($_SESSION['userLogged'])) {
            $this->userLogged = unserialize($_SESSION['userLogged']);
            $this->TemplateEngine->addGlobal('userLogged', $this->userLogged);
        }
    }
    protected function requireLogin(): void {
        if (empty($_SESSION['userLogged'])) {
            header('Location: error404');
            exit;
        }
    }

    public function error404() : void{
        echo $this->TemplateEngine->render("/Error/error404.twig");
    }

}