<?php

namespace ProjectTicketIT\Controller;

class BaseController
{
    protected $TemplateEngine;

    public function __construct(){
        $loader = new \Twig\Loader\FilesystemLoader('Src/View'); // Chemin vers le dossier des templates

        $this->TemplateEngine = new \Twig\Environment($loader);
        $this->TemplateEngine->addGlobal('session', $_SESSION);
        $this->TemplateEngine->enableStrictVariables();
        $this->TemplateEngine->addExtension(new \Twig\Extension\DebugExtension());
    }

}