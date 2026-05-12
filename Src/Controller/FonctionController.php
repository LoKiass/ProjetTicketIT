<?php

namespace DISEUMAT\Controller;

use DISEUMAT\Controller\BaseController;
use DISEUMAT\Exception\DatabaseException;
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
        try{
            if(isset($_GET['Pk'])){ // si on veut voir une fonction
                $Fonction = $this->FM->read($_GET['Pk']);
                echo $this->TemplateEngine->render("Fonction/InfoFonction.twig", ['FonctionEntity' => $Fonction]);
            }
            else{
                $TabFonction = $this->FM->list();
                $success = isset($_GET['success']) && $_GET['success'] === 'true';
                unset($_GET['success']); // Succes uniquement comme log au niveau de l'action update

                echo $this->TemplateEngine->render("Fonction/ListFonction.twig", ['TabFonction' => $TabFonction, 'success' => $success]);
            }
        } catch (NotFoundException|DatabaseException $e){
            echo $this->TemplateEngine->render("Fonction/ListFonction.twig", [ 'TabFonction' => null, 'errorMessage' => $e->getMessage()]);
        }
    }
    public function createFonction() : void {
        try{
            $this->requireLogin();
            if (isset($_POST['Descr']) && isset($_POST['Niveau'])){
                $_POST['Pk_Fonction'] = 0;
                $Fonction = FonctionEntity::fromArray($_POST);
                $this->FM->create($Fonction);

                echo $this->TemplateEngine->render("Fonction/CreateFonction.twig", ['success' => true]);
            }
            else{
                echo $this->TemplateEngine->render("Fonction/CreateFonction.twig");
            }
        } catch (DatabaseException|NotCreatedInDatabase $e){
            echo $this->TemplateEngine->render("Fonction/CreateFonction.twig", ['success' => false, 'error' => $e->getMessage()]);
        }

    }

    public function updateFonction() : void {
        $this->requireLogin();
        try{
            if (isset($_GET['Pk'])){
                $pk = $_GET['Pk'];

                if ($_SERVER['REQUEST_METHOD'] === 'POST'){
                    $_POST['Pk_Fonction'] = $pk;
                    $this->FM->update(FonctionEntity::fromArray($_POST));

                    header('Location: getFonction?success=true');
                }
                else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                    $tempFonction = $this->FM->read($_GET['Pk']);
                    echo $this->TemplateEngine->render("Fonction/UpdateFonction.twig", ['FonctionEntity' => $tempFonction]);
                }
            }
        } catch (NotFoundException|DatabaseException $e){
            echo $this->TemplateEngine->render("Fonction/UpdateFonction.twig", [
                'FonctionEntity' => null,
                'errorMessage' => $e->getMessage()
            ]);
        }
    }
}