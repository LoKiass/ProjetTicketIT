<?php

namespace DISEUMAT\Controller;

/*
 * La classe UserController contient les méthodes nécesaires pour la gestion de la page de connexion
 */

use DISEUMAT\Exception\DatabaseException;
use DISEUMAT\Exception\InvalidCredentialException;
use DISEUMAT\Exception\MissingInformation;
use DISEUMAT\Exception\NotFoundException;
use DISEUMAT\Model\Entity\UserEntity as UserEntity;
use DISEUMAT\Model\Service\Manager\UserManager;
use DISEUMAT\Model\Service\Session\UserSession;

class UserController extends BaseController
{
    private UserManager $UM;
    private UserSession $US;

    public function __construct(){
        $this->UM = new UserManager();
        $this->US = new UserSession();
        parent::__construct();
    }

    /**
     * Gère l'affichage et la soumission du formulaire de connexion.
     * En GET : affiche la page de login, en indiquant si un utilisateur vient de changer ses identifiants.
     * En POST (si 'Login' est présent) : vérifie les credentials en base, initialise la session
     * avec les informations de l'utilisateur, puis redirige vers l'accueil.
     * En cas d'erreur (identifiants invalides, erreur base), réaffiche le formulaire avec un message d'erreur.
     */
    public function formLogin() : void {
        $accountSuccess = false;
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

                $this->US->create($userFromDb);

                header("Location: formAccueil");

            }
            else {
                $checkIfUserInDb = $this->UM->list();
                if (empty($checkIfUserInDb)){ // Si aucun utilisateurs trouver
                    header("Location: addFirstUser");
                }
                else {
                    if (isset($_GET['accountSuccess'])){
                        $accountSuccess = true;
                    }

                    echo $this->TemplateEngine->render('/Login/Login.twig', [
                        'userChanged' => $userChangedParam,
                        'accountSuccess' => $accountSuccess,
                    ]);
                }

            }
        } catch (DatabaseException|InvalidCredentialException $e){
            echo $this->TemplateEngine->render('/Login/Login.twig', [
                'errorMessage' => $e->getMessage(),
                'userChanged' => 0
            ]);
        }
    }

    /**
     * Affiche la liste de tous les utilisateurs enregistrés en base de données.
     * Nécessite d'être connecté. En cas d'erreur, réaffiche la liste vide
     * avec un message d'erreur.
     */
    public function getUser() : void{
        $this->requireLogin();
        try{
            $TabUser = $this->UM->list();
            echo $this->TemplateEngine->render('/User/ListUser.twig', ['TabUser' => $TabUser]);
        } catch (DatabaseException $e){
            echo $this->TemplateEngine->render('/User/ListUser.twig', [
                'errorMessage' => $e->getMessage(),
                'userChanged' => 0
            ]);
        }
    }

    /**
     * Gère la modification du mot de passe d'un utilisateur identifié par le paramètre GET 'Login'.
     * En GET : récupère l'utilisateur en base et affiche le formulaire pré-rempli.
     * En POST : vérifie l'ancien mot de passe, applique le nouveau, puis redirige.
     * Si l'utilisateur modifie son propre compte, la session est détruite et il est
     * redirigé vers le login pour se reconnecter avec ses nouveaux identifiants.
     * Sinon, redirige vers la liste des utilisateurs.
     */
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


                    $loginLogged = $_SESSION['userLogged'];
                    $tempLog = $this->US->read();
                    $loginLogged = $tempLog->getLogin();

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
        } catch (DatabaseException|InvalidCredentialException $e) {
            echo $this->TemplateEngine->render('/User/UpdateUser.twig', [
                'UserEntity' => $userToEdit,
                'errorMessage' => $e->getMessage(),
            ]);
        } catch (NotFoundException $e){
            header('Location: error404');
        }

    }
    public function addFirstUser() : void{
            try{
                if (isset($_SESSION['userLogged']) || !empty($this->UM->list()) ){
                    header("Location: page404");
                }

                if (isset($_POST['Login']) && isset($_POST['Pswd'])){
                    if ($_POST['Pswd'] != $_POST['PswdConfirm']){
                        throw new InvalidCredentialException("Les mots de passe ne correspondent pas");
                    }

                    $userTmp = new UserEntity();
                    $userTmp->setLogin($_POST['Login']);
                    $userTmp->setPswd($_POST['Pswd']);
                    $userTmp->setStatut($_POST['Statut']);
                    $userTmp->setActif(true);

                    $this->UM->create($userTmp);

                    header("Location: formLogin?accountSuccess=true");
                }
                else {
                    echo $this->TemplateEngine->render('/Login/AddFirstUser.twig');
                }
            } catch (DatabaseException|InvalidCredentialException $e){
                echo $this->TemplateEngine->render('/Login/AddFirstUser.twig', ['errorMessage' => $e->getMessage()]);
            }
    }
}