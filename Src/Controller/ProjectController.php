<?php

namespace DISEUMAT\Controller;

use DISEUMAT\Controller\BaseController;
use DISEUMAT\Exception\DatabaseException;
use DISEUMAT\Exception\MissingInformation;
use DISEUMAT\Exception\NotCreatedInDatabase;
use DISEUMAT\Exception\NotFoundException;
use DISEUMAT\Model\Entity\ProjectEntity;
use DISEUMAT\Model\Service\Manager\ProjectManager;
use mysql_xdevapi\Exception;

class ProjectController extends BaseController
{
    private ProjectManager $PM;
    public function __construct(){
        parent::__construct();
        $this->PM = new ProjectManager();
    }

    public function getProject() : void {
        try {
            $this->requireLogin();

            if (isset($_GET['Pk'])) {
                $Project = $this->PM->read($_GET['Pk']);
                echo $this->TemplateEngine->render("Project/InfoProject.twig", [
                    'ProjectEntity' => $Project
                ]);
            }
            else {
                $TabProject = $this->PM->list();

                $successMessageFromUrl = $_GET['successMessage'] ?? null;
                $errorMessage  = $_GET['errorMessage'] ?? null;

                echo $this->TemplateEngine->render("Project/ListProject.twig", [
                    'TabProject'        => $TabProject,
                    'successMessage' => $successMessageFromUrl,
                    'errorMessage'   => $errorMessage
                ]);
            }
        } catch (NotFoundException|DatabaseException $e) {
            echo $this->TemplateEngine->render("Project/ListProject.twig", [
                'TabProject'      => null,
                'errorMessage' => $e->getMessage()
            ]);
        }
    }
    public function createProject() : void{
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
    public function updateProject() : void{
        echo "test";
    }
    public function deleteProject() : void{
        echo "test";
    }
}