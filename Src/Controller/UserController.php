<?php

namespace DISEUMAT\Controller;

/*
 * La classe UserController contient les méthodes nécesaires pour la gestion de la page de connexion
 */

use DISEUMAT\Controller\Model\Entity\UserEntity;
use DISEUMAT\Controller\Model\Service\Manager\UserManager;

class UserController extends BaseController
{
    private UserManager $UM;

    public function __construct(){
        $this->UM = new UserManager();
        parent::__construct();
    }

    /*
     * La méthode index permet d'afficher la page de connexion'
     */
    public function formLogin(){
        if(isset($_SESSION['userLogged'])){
            unset($_SESSION['userLogged']); // Dans le cas ou le user désire ce deconneter et revenir à la page de connexion
        }

        if( isset($_POST['Login'])){
            $userTmp = new UserEntity();
            $userTmp->setLogin($_POST['Login']);
            $userTmp->setPswd($_POST['Pswd']);

            $userFromDb = $this->UM->checkUser($userTmp);

            if ($userFromDb){
                $_SESSION['userLogged'] = $userFromDb; // Serialisation de l'objet
                echo $this->TemplateEngine->render('/Accueil/Accueil.twig');
            }
            else{
                echo $this->TemplateEngine->render('/Login/Login.twig', ['errorFlag' => true]);
            }

        }
        else {
            echo $this->TemplateEngine->render('/Login/Login.twig', ['errorFlag' => false]);
        }

    }
    public function listUser(){
        $TabUser = $this->UM->list();
        if (!$TabUser){
            header("HTTP/1.0 404 Not Found");
        }
        echo $this->TemplateEngine->render('/User/ListUser.twig', ['TabUser' => $TabUser]);
    }
}