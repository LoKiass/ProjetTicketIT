<?php

namespace DISEUMAT\Controller;

use DISEUMAT\Model\Entity\TechEntity;
use DISEUMAT\Model\Service\Manager\FonctionManager;
use DISEUMAT\Model\Service\Manager\TechManager;
use DISEUMAT\Exception\NotFoundException;
use DISEUMAT\Exception\DatabaseException;
use DISEUMAT\Exception\NotCreatedInDatabase;


class TechController extends BaseController
{
    private TechManager $TM;
    private FonctionManager $FM; // Pour lectutre fonction

    public function __construct(){
        $this->TM = new TechManager();
        $this->FM = new FonctionManager();
        parent::__construct();
    }

    /**
     * Affiche la liste de tous les techniciens ou les détails d'un technicien spécifique.
     * Si le paramètre GET 'Pk' est fourni, affiche la fiche du technicien correspondant.
     * Sinon, affiche la liste complète avec un éventuel message de succès suite à une mise à jour.
     */
    public function getTech() : void {
        try{
            $this->requireLogin();

            if (isset($_GET['Pk'])) {
                $Tech = $this->TM->read($_GET['Pk']);
                $ListeFonctions = $this->FM->listByTech($_GET['Pk']);
                $Tech->setFonctions($ListeFonctions);
                echo $this->TemplateEngine->render("Tech/InfoTech.twig", [
                    'TechEntity' => $Tech
                ]);
            }
            else{
                $TabTech = $this->TM->list();
                $success = isset($_GET['success']) && $_GET['success'] === 'true'; // Succes uniquement comme log au niveau de l'action update

                unset($_GET['success']);
                echo $this->TemplateEngine->render("Tech/ListTech.twig", ['TabTech' => $TabTech, 'success' => $success]);
            }
        } catch (NotFoundException $e){
            echo $this->TemplateEngine->render("Tech/ListTech.twig", ['TabTech' => null, 'errorMessage' => $e->getMessage()]);
        } catch (DatabaseException $e){
            echo $this->TemplateEngine->render("Tech/ListTech.twig", ['TabTech' => null, 'errorMessage' => $e->getMessage()]);
        }
    }

    /**
     * Gère la création d'un nouveau technicien.
     * En GET : affiche le formulaire de création vierge.
     * En POST (si 'Pren' est présent) : construit un TechEntity depuis les données du formulaire
     * et l'insère en base de données, puis affiche le résultat (succès ou erreur).
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
        } catch (DatabaseException|NotCreatedInDatabase $e) {
            echo $this->TemplateEngine->render("Tech/CreateTech.twig", [
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Gère la modification d'un technicien existant identifié par le paramètre GET 'Pk'.
     * En GET : récupère le technicien en base et affiche le formulaire pré-rempli.
     * En POST : met à jour le technicien avec les nouvelles données du formulaire,
     * puis redirige vers la liste avec un indicateur de succès.
     */
    public function updateTech() : void {
        $this->requireLogin();
        try{
            if (isset($_GET['Pk'])){
                $pk = $_GET['Pk'];

                if ($_SERVER['REQUEST_METHOD'] === 'POST'){
                    $_POST['Pk_Tech'] = $pk;
                    $_POST['Actif'] = $_POST['Actif'] ?? 0;

                    $this->TM->update(TechEntity::fromArray($_POST));
                    $this->TM->unlinkAllFunctions($pk);


                    if (isset($_POST['fonctions']) && is_array($_POST['fonctions'])) {
                        foreach ($_POST['fonctions'] as $idFonction) {
                            $this->TM->LinkToFunction($pk, $idFonction);
                        }
                    }

                    header('Location: getTech?success=true');
                }
                else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                    $tempTech = $this->TM->read($_GET['Pk']);
                    $userFonction = $this->FM->listByTech($_GET['Pk']);
                    $tempTech->setFonctions($userFonction);
                    echo $this->TemplateEngine->render("Tech/UpdateTech.twig",
                        ['TechEntity' => $tempTech
                        , 'TabFonction' => $this->FM->list()]
                    );
                }
            }
        } catch (NotFoundException|DatabaseException $e){
            echo $this->TemplateEngine->render("Tech/UpdateTech.twig", [
                'TechEntity' => null,
                'errorMessage' => $e->getMessage()
            ]);
        }
    }
}