<?php

namespace DISEUMAT\Controller;

use DISEUMAT\Controller\Model\Entity\TechEntity;
use DISEUMAT\Controller\Model\Service\Manager\TechManager;

class TechController extends BaseController
{
    private TechManager $TM;

    public function __construct(){
        // Manager
        $this->TM = new TechManager();
        parent::__construct();
    }
    public function formTech(){
        $this->requireLogin();
        echo $this->TemplateEngine->render("Tech/FormTech.twig");
    }
    public function getTech()  {
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
        } catch (\Exception $e){
            echo $this->TemplateEngine->render("Tech/ListTech.twig", ['TabTech' => null]);
        }

    }
    public function createTech(){
        try{
            $this->requireLogin();

            $rowAffected = null;

            if (isset($_POST['Pren'])){
                $_POST['Pk_Tech'] ?? $_POST['Pk_Tech'] = 0; // Afin d'utilise l'entité statique sans problème de nul
                $_POST['Actif'] ?? $_POST['Actif'] = 0; // Cas de la checkbox

                $tempTech = TechEntity::fromArray($_POST);

                if(!$tempTech){
                    header("HTTP/1.0 404 Not Found");
                }

                $rowAffected = $this->TM->create($tempTech);
                echo $this->TemplateEngine->render("Tech/CreateTech.twig", ['rowAffected' => $rowAffected]);
            }
            else{
                echo $this->TemplateEngine->render("Tech/CreateTech.twig", ['rowAffected' => $rowAffected]);
            }
        } catch (\Exception $e){
            echo $this->TemplateEngine->render("Tech/CreateTech.twig", ['rowAffected' => false]); // Si erreur SQL
        }

    }
    public function updateTech(){
        $this->requireLogin();

        try{ // Bloc de test
            if (isset($_GET['Pk'])){
                $pk = $_GET['Pk'];

                if ($_SERVER['REQUEST_METHOD'] === 'POST'){
                    $_POST['Pk_Tech'] = $pk;
                        $_POST['Actif'] ?? $_POST['Actif'] = 0;

                    $isUpdate = $this->TM->update(TechEntity::fromArray($_POST));
                    if ($isUpdate){ // Si un row à été affecté
                        unset($_GET['Pk']); // Permet de passer directement à la liste et pas l'information de celui ci.
                        $this->getTech();
                    }
                    else {
                        throw new \Exception("Erreur lors de la mise à jour");
                    }
                }
                else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                    $tempTech = $this->TM->read($_GET['Pk']);
                    echo $this->TemplateEngine->render("Tech/UpdateTech.twig", ['TechEntity' => $tempTech]);
                }
            }
        } catch (\Exception $e){
            echo $this->TemplateEngine->render("Tech/Tech.twig", [
                'TechEntity' => $tempTech,
            ]);
        }
    }
}