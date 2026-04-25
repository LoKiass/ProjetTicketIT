<?php

namespace ProjectTicketIT\Controller;

/*
 * La classe LoginController contient les méthodes nécesaires pour la gestion de la page de connexion
 */
class LoginController extends BaseController
{
    public function __construct(){
        parent::__construct();
    }

    /*
     * La méthode index permet d'afficher la page de connexion'
     */
    public function formLogin(){
        echo $this->TemplateEngine->render('/Login/Login.twig');
    }
}