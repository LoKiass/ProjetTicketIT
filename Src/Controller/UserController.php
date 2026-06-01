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
use http\Exception\InvalidArgumentException;

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
        $accountSuccess = false; // Valeur de base
        $userChangedParam = isset($_GET['userChanged']) ? 1 : 0; // Valeur de base de si l'utilisateurs à modifier ces identifiants

        if(isset($_SESSION['userLogged'])){ // Si un utilisateur arrive ici avec une sessions, la kill directement, évite de potentielle attaque via URL
            unset($_SESSION['userLogged']);
        }

        try{
            if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Login'])){ // Si on passe par le formulaire de connexion
                // Créations d'un utilisateurs temporaire
                $userTmp = new UserEntity();
                $userTmp->setLogin($_POST['Login']);
                $userTmp->setPswd($_POST['Pswd']);

                // Verifier si cette utilisateurs existe bien en DB
                $userFromDb = $this->UM->checkUser($userTmp);

                $this->US->create($userFromDb);

                // Redirection vers la page d'accueil
                header("Location: formAccueil");
            }
            else {
                // Verification si un utilisateur existe au niveau de la DB
                $checkIfUserInDb = $this->UM->list();
                if (empty($checkIfUserInDb)){ // Si aucun utilisateurs trouver -> redirection vers la page d'ajout de premiers utilisateurs
                    header("Location: addFirstUser");
                }
                else {
                    if (isset($_GET['accountSuccess'])){ // Dans le cas ou nous avons crée un compte utilisateurs, sinon valeurs reste à false
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
            // Recupération de l'utilisateur à modifier au niveau de la page
            $loginTarget = $_GET['Login'] ?? null;

            // Récuperation de cette utilisateur au niveau de la bdd
            $userToEdit = $this->UM->read($loginTarget);

            // Si l'utilisateur existe, alors on peut modifier son mot de passe
            if ($loginTarget !== null) {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Si on passe par le formulaire de modification

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
                    else { // Sinon, on redirige vers la liste des utilisateurs
                        header("Location: ListUser");
                    }

                }

                // Sinon, on affiche le formulaire de modification avec les informations de l'utilisateur
                if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                    echo $this->TemplateEngine->render('/User/UpdateUser.twig', [
                        'UserEntity' => $userToEdit
                    ]);
                }
            }
        } catch (DatabaseException|InvalidCredentialException|MissingInformation $e) { // Erreur lors de la modification du mot de passe
            echo $this->TemplateEngine->render('/User/UpdateUser.twig', [
                'UserEntity' => $userToEdit,
                'errorMessage' => $e->getMessage(),
            ]);
        } catch (NotFoundException $e){ // Dans le cas d'un user inexistant
            header('Location: error404');
        }

    }
    /*
     * Cette méthode ce déclenche dans le cas de la première connexion utilisateurs
     * Cela vas permettre à l'administrateur à ce crée un compte, et de pouvoir s'y connecter
     */
    public function addFirstUser() : void{
            try{
                if (isset($_SESSION['userLogged']) || !empty($this->UM->list()) ){  // Si un utilisateur existe déjà en DB ou une variable sessions existe => ANORMALE ! Erreur 404 sans indice
                    header("Location: page404");
                }

                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Login'])){
                    if ($_POST['Pswd'] != $_POST['PswdConfirm']){ // Si les mots de passe ne correspondent pas
                        throw new InvalidCredentialException("Les mots de passe ne correspondent pas");
                    }

                    $_POST['Actif'] = true; // Si on crée un utilisateur, on le met actif directement
                    $userTmp = UserEntity::fromArray($_POST);

                    $this->UM->create($userTmp); // Création de l'utilisateur au niveau de la DB

                    header("Location: formLogin?accountSuccess=true");
                }
                else { // dès le lancement
                    echo $this->TemplateEngine->render('/Login/AddFirstUser.twig');
                }
            } catch (DatabaseException|InvalidCredentialException $e){
                echo $this->TemplateEngine->render('/Login/AddFirstUser.twig', ['errorMessage' => $e->getMessage()]);
            }
    }
}