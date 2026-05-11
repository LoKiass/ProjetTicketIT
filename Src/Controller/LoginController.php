<?php

namespace DISEUMAT\Controller;

/*
 * La classe LoginController contient les méthodes nécesaires pour la gestion de la page de connexion
 */

use DISEUMAT\Controller\Model\Entity\UserEntity;
use DISEUMAT\Controller\Model\Service\Manager\UserManager;

class LoginController extends BaseController
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
        if(isset($_SESSION['user'])){
            unset($_SESSION['user']); // Dans le cas ou le user désire ce deconneter et revenir à la page de connexion
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
}