<?php

namespace DISEUMAT\Controller;

use DISEUMAT\Model\Entity\TechEntity;
use DISEUMAT\Model\Service\Manager\TechManager;
use DISEUMAT\Exception\NotFoundException;
use DISEUMAT\Exception\DatabaseException;
use DISEUMAT\Exception\NotCreatedInDatabase;


class TechController extends BaseController
{
    private TechManager $TM;

    public function __construct(){
        $this->TM = new TechManager();
        parent::__construct();
    }
    public function getTech() : void {
        try{
            $this->requireLogin();

            if (isset($_GET['Pk'])){
                $Tech = $this->TM->read($_GET['Pk']);
                echo $this->TemplateEngine->render("Tech/InfoTech.twig", ['TechEntity' => $Tech]);
            }
            else{
                $TabTech = $this->TM->list();
                echo $this->TemplateEngine->render("Tech/ListTech.twig", ['TabTech' => $TabTech]);
            }
        } catch (NotFoundException $e){
            echo $this->TemplateEngine->render("Tech/ListTech.twig", ['TabTech' => null, 'errorMessage' => $e->getMessage()]);
        } catch (DatabaseException $e){
            echo $this->TemplateEngine->render("Tech/ListTech.twig", ['TabTech' => null, 'errorMessage' => $e->getMessage()]);
        }
    }

    public function createTech() : void {
        $this->requireLogin();
        try{
            if (isset($_POST['Pren'])){
                $_POST['Pk_Tech'] = $_POST['Pk_Tech'] ?? 0;
                $_POST['Actif'] = $_POST['Actif'] ?? 0;

                $tempTech = TechEntity::fromArray($_POST);

                if(!$tempTech){
                    header("HTTP/1.0 404 Not Found");
                }

                $this->TM->create($tempTech);
                echo $this->TemplateEngine->render("Tech/CreateTech.twig", ['success' => true]);
            }
            else{
                echo $this->TemplateEngine->render("Tech/CreateTech.twig", ['success' => null]);
            }
        } catch (DatabaseException|NotCreatedInDatabase $e){
            echo $this->TemplateEngine->render("Tech/CreateTech.twig", ['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function updateTech() : void {
        $this->requireLogin();
        try{
            var_dump($_POST);
            if (isset($_GET['Pk'])){
                $pk = $_GET['Pk'];

                if ($_SERVER['REQUEST_METHOD'] === 'POST'){
                    $_POST['Pk_Tech'] = $pk;
                    $_POST['Actif'] = $_POST['Actif'] ?? 0;

                    $this->TM->update(TechEntity::fromArray($_POST));
                    header('Location: getTech');
                }
                else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                    $tempTech = $this->TM->read($_GET['Pk']);
                    echo $this->TemplateEngine->render("Tech/UpdateTech.twig", ['TechEntity' => $tempTech]);
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