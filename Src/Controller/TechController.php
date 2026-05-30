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

            if (isset($_GET['Pk'])) {
                $Tech = $this->TM->read($_GET['Pk']);
                $ListeFonctions = $this->FM->listByTech($_GET['Pk']);
                $Tech->setFonctions($ListeFonctions);
                echo $this->TemplateEngine->render("Tech/InfoTech.twig", [
                    'TechEntity' => $Tech
                ]);
            }
            else {
                $TabTech = $this->TM->list();

                $successMessageFromUrl = $_GET['successMessage'] ?? null;
                $errorMessage  = $_GET['errorMessage'] ?? null;

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
            if (isset($_POST['Pren'])) {
                $_POST['Pk_Tech'] = $_POST['Pk_Tech'] ?? 0;
                $_POST['Actif'] = $_POST['Actif'] ?? 0;

                $tempTech = TechEntity::fromArray($_POST);

                if (!$tempTech) {
                    header("HTTP/1.0 404 Not Found");
                    exit;
                }

                $techId = $this->TM->create($tempTech);

                if (isset($_POST['fonctions']) && is_array($_POST['fonctions'])) {
                    foreach ($_POST['fonctions'] as $idFonction) {
                        $this->TM->LinkToFunction($techId, $idFonction);
                    }
                }

                $TabFonction = $this->FM->list();
                echo $this->TemplateEngine->render("Tech/CreateTech.twig", ['success' => true, 'TabFonction' => $TabFonction]);
            } else {
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
            if (isset($_GET['Pk'])) {
                $pk = $_GET['Pk'];

                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $_POST['Pk_Tech'] = $pk;
                    $_POST['Actif'] = $_POST['Actif'] ?? 0;

                    $this->TM->update(TechEntity::fromArray($_POST));
                    $this->TM->unlinkAllFunctions($pk);

                    if (isset($_POST['fonctions']) && is_array($_POST['fonctions'])) {
                        foreach ($_POST['fonctions'] as $idFonction) {
                            $this->TM->LinkToFunction($pk, $idFonction);
                        }
                    }
                    header('Location: getTech?successMessage=' . urlencode("La modification du technicien a réussi"));
                }
                else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
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