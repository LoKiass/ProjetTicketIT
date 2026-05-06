<?php

namespace DISEUMAT\Controller;

class AccueilController extends BaseController
{
    public function __construct(){
        parent::__construct();
    }

    public function formAccueil(){
        echo $this->TemplateEngine->render('/Accueil/Accueil.twig');
    }
}