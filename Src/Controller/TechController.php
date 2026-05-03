<?php

namespace DISEUMAT\Controller;

use DISEUMAT\Model\Entity\TechEntity;
use DISEUMAT\Model\Service\Manager\TechManager;

class TechController extends BaseController
{
    private TechManager $TM;

    public function __construct(){
        // Manager
        $this->TM = new TechManager();
        parent::__construct();
    }
    public function formTech(){
        echo $this->TemplateEngine->render("Tech/FormTech.twig");
    }
    public function readTech()  {
        echo "Hello";
        if (isset($_GET['Pk'])){
            $Pk = $_GET['Pk'];
            $Tech = $this->TM->read($Pk);
            echo $this->TemplateEngine->render("Tech/ListTech.twig", ['TechEntity' => $Tech]);
        }
        else{
            $TabTech = $this->TM->list();
            echo $this->TemplateEngine->render("Tech/ListTech.twig", ['TabTech' => $TabTech]);
        }
    }
    public function createTech(){
        $rowAffected = null;

        if (isset($_POST['Pren'])){
            $_POST['Pk_Tech'] ?? $_POST['Pk_Tech'] = 0; // Afin d'utilise l'entité statique sans problème de nul
            $_POST['Actif'] ?? $_POST['Actif'] = 0; // Cas de la checkbox

            $tempTech = TechEntity::fromArray($_POST);

            $rowAffected = $this->TM->create($tempTech);
            echo $this->TemplateEngine->render("Tech/CreateTech.twig", ['rowAffected' => $rowAffected]);
        }
        else{
            echo $this->TemplateEngine->render("Tech/CreateTech.twig", ['rowAffected' => $rowAffected]);
        }
    }
    public function updateTech(){
        echo $this->TemplateEngine->render("Tech/UpdateTech.twig");
    }
    public function deleteTech(){
        // Delete
    }
}