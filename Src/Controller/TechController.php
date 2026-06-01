<?php

namespace DISEUMAT\Controller;

use DISEUMAT\Exception\MissingInformation;
use DISEUMAT\Model\Entity\TechEntity;
use DISEUMAT\Model\Service\Manager\FonctionManager;
use DISEUMAT\Model\Service\Manager\TechManager;
use DISEUMAT\Exception\NotFoundException;
use DISEUMAT\Exception\DatabaseException;
use DISEUMAT\Exception\NotCreatedInDatabase;

class TechController extends BaseController
{
    private TechManager $TM;
    private FonctionManager $FM;

    public function __construct(){
        $this->TM = new TechManager();
        $this->FM = new FonctionManager();
        parent::__construct();
    }

    /**
     * Affiche la liste de tous les techniciens ou les détails d'un technicien spécifique.
     */
    public function getTech() : void {
        try {
            $this->requireLogin();

            if (isset($_GET['Pk'])) { // Si on demande l'informations d'un utilisateur précis (READ + PK)

                $Tech = $this->TM->read($_GET['Pk']); // Récuperations de l'utilisateur et genere une entité
                $ListeFonctions = $this->FM->listByTech($_GET['Pk']); // Récuperations des fonctions de l'utilisateur
                $Tech->setFonctions($ListeFonctions); // Genere la liste des fonctions de l'utilisateur niveau entité

                echo $this->TemplateEngine->render("Tech/InfoTech.twig", [
                    'TechEntity' => $Tech
                ]);
            }
            else if($_SERVER['REQUEST_METHOD'] === 'GET'){
                $TabTech = $this->TM->list(); // Recuperation de la liste des techniciens

                $successMessageFromUrl = $_GET['successMessage'] ?? null; // Si message de succes de modification ou suppression
                $errorMessage  = $_GET['errorMessage'] ?? null; // Si erreur de modification ou suppression

                echo $this->TemplateEngine->render("Tech/ListTech.twig", [
                    'TabTech'        => $TabTech,
                    'successMessage' => $successMessageFromUrl,
                    'errorMessage'   => $errorMessage
                ]);
            }
        } catch (DatabaseException $e) {
            echo $this->TemplateEngine->render("Tech/ListTech.twig", [
                'TabTech'      => null,
                'errorMessage' => $e->getMessage()
            ]);
        } catch (NotFoundException $e){
            header('Location: error404');
        }
    }

    /**
     * Gère la création d'un nouveau technicien.
     */
    public function createTech() : void {
        $this->requireLogin();

        try {
            if (isset($_POST['Pren']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
                $_POST['Pk_Tech'] = $_POST['Pk_Tech'] ?? 0; // Recuperer la pk utilisateurs, si elle n'existe pas, NULL // Le formulaire envoie pas de PK
                $_POST['Actif'] = $_POST['Actif'] ?? 0; // Recuperer l'actif de l'utilisateur, si elle n'existe pas, NULL // Une case décoche en HTML ne generer pas null

                // Generaration de l'entité
                $tempTech = TechEntity::fromArray($_POST);

                if (!$tempTech) { // Si par malchance une erreur ce produit
                    header("Location: error404");
                }

                // Création de l'utilisateur niveau bdd + récuperation de la PK generé par la bdd
                $techId = $this->TM->create($tempTech);

                // Si on a selectionner des fonctions, on les lier au technicien
                if (isset($_POST['fonctions']) && is_array($_POST['fonctions'])) {
                    foreach ($_POST['fonctions'] as $idFonction) {
                        $this->TM->LinkToFunction($techId, $idFonction);
                    }
                }

                $TabFonction = $this->FM->list();
                echo $this->TemplateEngine->render("Tech/CreateTech.twig", ['success' => true, 'TabFonction' => $TabFonction]);
            } else { // Si on passe par la Navbar
                $TabFonction = $this->FM->list();
                echo $this->TemplateEngine->render("Tech/CreateTech.twig", ['TabFonction' => $TabFonction]);
            }
        } catch (DatabaseException|NotCreatedInDatabase|MissingInformation $e) {
            $TabFonction = $this->FM->list();
            echo $this->TemplateEngine->render("Tech/CreateTech.twig", [
                'TabFonction'  => $TabFonction,
                'errorMessage' => $e->getMessage() // Changé pour cohérence avec ListTech
            ]);
        }
    }

    /**
     * Gère la modification d'un technicien existant.
     */
    public function updateTech() : void {
        $this->requireLogin();
        try {
            if (isset($_GET['Pk'])) { // Si on demande la modification d'un technicien
                $pk = $_GET['Pk'];

                // Si on passe par le formulaire de modification
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $_POST['Pk_Tech'] = $pk; // On ajoute la PK du technicien pour la modifier
                    $_POST['Actif'] = $_POST['Actif'] ?? 0; // On ajoute l'activité du technicien pour la modifier

                    // Modification de l'utilisateur
                    $this->TM->update(TechEntity::fromArray($_POST));
                    $this->TM->unlinkAllFunctions($pk);

                    if (isset($_POST['fonctions']) && is_array($_POST['fonctions'])) {
                        foreach ($_POST['fonctions'] as $idFonction) {
                            $this->TM->LinkToFunction($pk, $idFonction);
                        }
                    }
                    header('Location: getTech?successMessage=' . urlencode("La modification du technicien a réussi"));
                }
                // Si on passe par la Navbar
                else if ($_SERVER['REQUEST_METHOD'] === 'GET') {

                    // Bloc de lecture qui permet de pré-remplir le formulaire
                    $tempTech = $this->TM->read($_GET['Pk']);
                    $userFonction = $this->FM->listByTech($_GET['Pk']);
                    $tempTech->setFonctions($userFonction);

                    echo $this->TemplateEngine->render("Tech/UpdateTech.twig", [
                        'TechEntity'  => $tempTech,
                        'TabFonction' => $this->FM->list()
                    ]);
                }
            }
        } catch (DatabaseException|MissingInformation $e) {
            header('Location: getTech?errorMessage=' . urlencode($e->getMessage()));
        } catch (NotFoundException $e){
            header('Location: error404');
        }
    }
}