<?php

namespace ProjectTicketIT\Controller;

/*
 * La classe LoginController contient les méthodes nécesaires pour la gestion de la page de connexion
 */

use ProjectTicketIT\Model\Session\UserManager;

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
        if( isset($_POST['Login'])){
            $Login = $_POST['Login'];
            $Pswd = $_POST['Pswd'];
            $checkLogin = $this->UM->checkUser($Login, $Pswd);
            if ($checkLogin){
                $_SESSION['Login'] = $Login;
                echo "LOGIN REUSSI";
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