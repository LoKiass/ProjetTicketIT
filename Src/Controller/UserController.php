<?php

namespace DISEUMAT\Controller;

/*
 * La classe UserController contient les méthodes nécesaires pour la gestion de la page de connexion
 */

use DISEUMAT\Exception\DatabaseException;
use DISEUMAT\Exception\InvalidCredentialException;
use DISEUMAT\Exception\NotFoundException;
use DISEUMAT\Model\Entity\UserEntity as UserEntity;
use DISEUMAT\Model\Service\Manager\UserManager;

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
    public function formLogin() : void {
        $userChangedParam = isset($_GET['userChanged']) ? 1 : 0;

        if(isset($_SESSION['userLogged'])){
            unset($_SESSION['userLogged']);
        }
        try{
            if(isset($_POST['Login'])){
                $userTmp = new UserEntity();
                $userTmp->setLogin($_POST['Login']);
                $userTmp->setPswd($_POST['Pswd']);

                $userFromDb = $this->UM->checkUser($userTmp);

                $_SESSION['userLogged'] = [
                    'Login'  => $userFromDb->getLogin(),
                    'Statut' => $userFromDb->getStatut(),
                    'Actif'  => $userFromDb->getActif(),
                ];

                header("Location: formAccueil");

            }
            else {
                echo $this->TemplateEngine->render('/Login/Login.twig', [
                    'userChanged' => $userChangedParam
                ]);
            }
        } catch (DatabaseException|InvalidCredentialException $e){
            echo $this->TemplateEngine->render('/Login/Login.twig', [
                'errorMessage' => $e->getMessage(),
                'userChanged' => 0
            ]);
        }
    }
    public function getUser() : void{
        $this->requireLogin();
        try{
            $TabUser = $this->UM->list();
            echo $this->TemplateEngine->render('/User/ListUser.twig', ['TabUser' => $TabUser]);
        } catch (DatabaseException|NotFoundException $e){
            echo $this->TemplateEngine->render('/User/ListUser.twig', [
                'errorMessage' => $e->getMessage(),
                'userChanged' => 0
            ]);
        }
    }

    public function updateUser(): void {
        $this->requireLogin();

        try {
            $loginTarget = $_GET['Login'] ?? null;
            $userToEdit = $this->UM->read($loginTarget);

            if ($loginTarget !== null) {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                    $verifUser = new UserEntity();
                    $verifUser->setLogin($loginTarget);
                    $verifUser->setPswd($_POST['oldPswd'] ?? '');

                    // Verification de l'existence de l'ancien mot de passe lier au user
                    $this->UM->checkUser($verifUser);


                    // Mise en place du nouveau mot de passe
                    $newPswd = $_POST['newPswd'];
                    $this->UM->updatePassword($loginTarget, $newPswd);


                    $loginLogged = $_SESSION['userLogged']['Login'];
                    if ($loginLogged === $loginTarget) { // Si le user qui modifie ce modifie lui même, alors on le déconnecte pour eviter
                        // d'avoir la variable sessions valide avec les credentiales de l'ancien user'
                        session_abort();
                        header("Location: formLogin?userChanged=1");
                    }
                    else {
                        header("Location: ListUser");
                    }

                }

                if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                    echo $this->TemplateEngine->render('/User/UpdateUser.twig', [
                        'UserEntity' => $userToEdit
                    ]);
                }
            }
        } catch (DatabaseException|NotFoundException|InvalidCredentialException $e) {
            echo $this->TemplateEngine->render('/User/UpdateUser.twig', [
                'UserEntity' => $userToEdit,
                'errorMessage' => $e->getMessage(),
            ]);
        }

    }
}