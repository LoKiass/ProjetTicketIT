<?php

namespace DISEUMAT\Controller;

use DISEUMAT\Controller\BaseController;
use DISEUMAT\Exception\DatabaseException;
use DISEUMAT\Exception\MissingInformation;
use DISEUMAT\Exception\NotCreatedInDatabase;
use DISEUMAT\Exception\NotFoundException;
use DISEUMAT\Model\Entity\ProjectEntity;
use DISEUMAT\Model\Service\Manager\ProjectManager;

class ProjectController extends BaseController
{
    private ProjectManager $PM;
    public function __construct(){
        parent::__construct();
        $this->PM = new ProjectManager();
    }

    public function getProj() : void{
        echo "test";
    }
    public function createProj() : void{
        $this->requireLogin();
        try{
            if( isset($_POST['Ident'])){
                $tempProject = ProjectEntity::FromArray($_POST);
                $this->PM->create($tempProject);

                echo $this->TemplateEngine->render("Project/CreateProject.twig", ['success' => true]);
            } else {
                echo $this->TemplateEngine->render('Project/CreateProject.twig');
            }
        } catch (MissingInformation|DatabaseException $e) {
            echo $this->TemplateEngine->render("Project/CreateProject.twig", [
                'errorMessage' => $e->getMessage()
            ]);
        }
    }
    public function updateProj() : void{
        echo "test";
    }
    public function deleteProj() : void{
        echo "test";
    }
}