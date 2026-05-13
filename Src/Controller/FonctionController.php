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