<?php

namespace DISEUMAT\Controller;

use DISEUMAT\Controller\BaseController;
use DISEUMAT\Exception\DatabaseException;
use DISEUMAT\Exception\LinkExistBetween;
use DISEUMAT\Exception\NotCreatedInDatabase;
use DISEUMAT\Exception\NotFoundException;
use DISEUMAT\Model\Entity\FonctionEntity;
use DISEUMAT\Model\Service\Manager\FonctionManager;

class FonctionController extends BaseController
{
    private FonctionManager $FM;

    public function __construct(){
        parent::__construct();
        $this->FM = new FonctionManager();
    }

    /**
     * Affiche la liste de toutes les fonctions ou le détail d'une fonction spécifique.
     * Si le paramètre GET 'Pk' est fourni, affiche la fiche de la fonction correspondante.
     * Sinon, affiche la liste complète en transmettant les éventuels messages de succès
     * (mise à jour, suppression) ou d'erreur reçus depuis l'URL.
     */
    public function getFonction() : void {
        $this->requireLogin();
        try {
            if (isset($_GET['Pk'])) {
                $Fonction = $this->FM->read($_GET['Pk']);
                echo $this->TemplateEngine->render("Fonction/InfoFonction.twig", ['FonctionEntity' => $Fonction]);
            } else {
                $TabFonction = $this->FM->list();

                $successUpdate = isset($_GET['successUpdate']);
                $succesDelete  = isset($_GET['successDelete']);
                $errorFromUrl  = $_GET['errorMessage'] ?? null; // Si erreur recu de update ou delete depuis l'url0

                echo $this->TemplateEngine->render("Fonction/ListFonction.twig", [
                    'TabFonction'   => $TabFonction,
                    'successUpdate' => $successUpdate,
                    'succesDelete'  => $succesDelete,
                    'errorMessage'  => $errorFromUrl,
                ]);
            }
        } catch (NotFoundException|DatabaseException $e) {
            echo $this->TemplateEngine->render("Fonction/ListFonction.twig", [
                'TabFonction'  => null,
                'errorMessage' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Gère la création d'une nouvelle fonction.
     * En GET : affiche le formulaire de création vierge.
     * En POST (si 'Descr' et 'Niveau' sont présents) : construit un FonctionEntity
     * depuis les données du formulaire et l'insère en base de données,
     * puis affiche le résultat (succès ou erreur).
     */
    public function createFonction() : void {
        try {
            $this->requireLogin();
            if (isset($_POST['Descr']) && isset($_POST['Niveau'])) {
                $_POST['Pk_Fonction'] = 0;
                $Fonction = FonctionEntity::fromArray($_POST);
                $this->FM->create($Fonction);

                echo $this->TemplateEngine->render("Fonction/CreateFonction.twig", ['success' => true]);
            } else {
                echo $this->TemplateEngine->render("Fonction/CreateFonction.twig");
            }
        } catch (DatabaseException|NotCreatedInDatabase $e) {
            echo $this->TemplateEngine->render("Fonction/CreateFonction.twig", [
                'success' => false,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    /**
     * Gère la modification d'une fonction existante identifiée par le paramètre GET 'Pk'.
     * En GET : récupère la fonction en base et affiche le formulaire pré-rempli.
     * En POST : met à jour la fonction avec les nouvelles données du formulaire,
     * puis redirige vers la liste avec un indicateur de succès.
     * Si 'Pk' est absent, redirige simplement vers la liste.
     */
    public function updateFonction() : void {
        $this->requireLogin();
        try {
            if (isset($_GET['Pk'])) {
                $pk = $_GET['Pk'];

                if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Si validation de formulaire
                    $_POST['Pk_Fonction'] = $pk;
                    $this->FM->update(FonctionEntity::fromArray($_POST));

                    header('Location: getFonction?successUpdate=true');
                    exit;
                } else { // Si on passe par la Navbar
                    $tempFonction = $this->FM->read($pk);
                    echo $this->TemplateEngine->render("Fonction/UpdateFonction.twig", [
                        'FonctionEntity' => $tempFonction
                    ]);
                }
            } else {
                header('Location: getFonction');
                exit;
            }
        } catch (NotFoundException|DatabaseException $e) {
            header("Location: getFonction?errorMessage=" . $e->getMessage());
            exit;
        }
    }

    /**
     * Gère la suppression d'une fonction identifiée par le paramètre GET 'Pk'.
     * Si 'Pk' est présent, supprime la fonction en base puis redirige vers la liste
     * avec un indicateur de succès. Si la fonction est liée à d'autres entités
     * ou introuvable, redirige vers la liste avec un message d'erreur.
     * Si 'Pk' est absent, redirige simplement vers la liste.
     */
    public function deleteFonction() : void {
        $this->requireLogin();
        try {
            if (isset($_GET['Pk'])) {
                $this->FM->delete($_GET['Pk']);
                header('Location: getFonction?successDelete=true');
                exit;
            }
            header('Location: getFonction');
            exit;

        } catch (NotFoundException|DatabaseException|LinkExistBetween $e) {
            header('Location: getFonction?errorMessage=' . $e->getMessage());
        }
    }
}