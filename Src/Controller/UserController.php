<?php

namespace DISEUMAT\Controller;

/*
 * La classe UserController contient les méthodes nécesaires pour la gestion de la page de connexion
 */

use DISEUMAT\Controller\Model\Entity\UserEntity as UserEntity;
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
        $userChangedParam = isset($_GET['userChanged']) ? 1 : 0;

        if(isset($_SESSION['userLogged'])){
            unset($_SESSION['userLogged']);
        }

        if(isset($_POST['Login'])){
            $userTmp = new UserEntity();
            $userTmp->setLogin($_POST['Login']);
            $userTmp->setPswd($_POST['Pswd']);

            $userFromDb = $this->UM->checkUser($userTmp);

            if ($userFromDb){
                $_SESSION['userLogged'] = serialize($userFromDb);
                echo $this->TemplateEngine->render('/Accueil/Accueil.twig');
            }
            else{
                echo $this->TemplateEngine->render('/Login/Login.twig', [
                    'errorFlag' => true,
                    'userChanged' => 0
                ]);
            }
        }
        else {
            echo $this->TemplateEngine->render('/Login/Login.twig', [
                'errorFlag' => false,
                'userChanged' => $userChangedParam
            ]);
        }
    }
    public function listUser(){
        $this->requireLogin();

        $TabUser = $this->UM->list();
        if (!$TabUser){
            header("HTTP/1.0 404 Not Found");
        }
        echo $this->TemplateEngine->render('/User/ListUser.twig', ['TabUser' => $TabUser]);
    }

    public function updateUser(): void
    {
        $this->requireLogin();

        try {
            $loginTarget = $_GET['Login'] ?? null;

            if ($loginTarget !== null) {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                    $verifUser = new UserEntity();
                    $verifUser->setLogin($loginTarget);
                    $verifUser->setPswd($_POST['oldPswd'] ?? '');

                    // Verification de l'existence de l'ancien mot de passe lier au user
                    if (!$this->UM->checkUser($verifUser)) {
                        $userToEdit = $this->UM->read($loginTarget);
                        echo $this->TemplateEngine->render('/User/UpdateUser.twig', [
                            'UserEntity' => $userToEdit,
                            'error' => "L'ancien mot de passe est incorrect."
                        ]);
                        return;
                    }

                    // Mise en place du nouveau mot de passe
                    $newPswd = $_POST['newPswd'];
                    $this->UM->updatePassword($loginTarget, $newPswd);


                    $userSerial = $_SESSION['userLogged'];
                    $userActuallyLogged = unserialize($userSerial);
                    if ($userActuallyLogged->getLogin() === $loginTarget) { // Si le user qui modifie ce modifie lui même, alors on le déconnecte pour eviter
                        // d'avoir la variable sessions valide avec les credentiales de l'ancien user'
                        session_abort();
                        header("Location: formLogin?userChanged=1");
                    }
                    else {
                        header("Location: listUser");
                    }

                }

                if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                    $userToEdit = $this->UM->read($loginTarget);
                    echo $this->TemplateEngine->render('/User/UpdateUser.twig', ['UserEntity' => $userToEdit]);
                }
            }
        } catch (\Exception $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }
}