<?php

namespace DISEUMAT\Controller;

use DISEUMAT\Controller\BaseController;
use DISEUMAT\Exception\DatabaseException;
use DISEUMAT\Exception\LinkExistBetween;
use DISEUMAT\Exception\MissingInformation;
use DISEUMAT\Exception\NotCreatedInDatabase;
use DISEUMAT\Exception\NotFoundException;
use DISEUMAT\Model\Entity\JobEntity;
use DISEUMAT\Model\Entity\ProjectEntity;
use DISEUMAT\Model\Service\Manager\JobManager;
use DISEUMAT\Model\Service\Manager\ProjectManager;
use mysql_xdevapi\Exception;

class ProjectController extends BaseController
{
    private ProjectManager $PM;
    private JobManager $JM;
    public function __construct(){
        parent::__construct();
        $this->JM = new JobManager();
        $this->PM = new ProjectManager();
    }

    public function getProject() : void {
        $this->requireLogin();
        try {
            if (isset($_GET['Pk'])) {
                $Project = $this->PM->read($_GET['Pk']);
                $jobLinkToProject = $this->PM->listByJobs($_GET['Pk']);
                echo $this->TemplateEngine->render("Project/InfoProject.twig", [
                    'ProjectEntity' => $Project,
                    'JobsList' => $jobLinkToProject,
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
        } catch (DatabaseException $e) {
            echo $this->TemplateEngine->render("Project/ListProject.twig", [
                'TabProject'      => null,
                'errorMessage' => $e->getMessage()
            ]);
        } catch (NotFoundException $e){
            header('Location: error404');
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
        $this->requireLogin();
        try {
            if (isset($_GET['Pk'])) {
                $pk = $_GET['Pk'];

                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $_POST['Pk_Project'] = $pk;

                    $this->PM->update(ProjectEntity::fromArray($_POST));

                    $projectJobs = $this->PM->listByJobs($pk);

                    foreach ($projectJobs as $job) {
                        $pkJob = $job->getPk();
                        $newStatus = isset($_POST['status_' . $pkJob]) ? 'Terminer' : 'En cours';
                        if ($job->getStatus() !== $newStatus) {
                            $job->setStatus($newStatus);
                            $this->JM->update($job);
                        }
                        header('Location: getProject?successMessage=' . urlencode("La modifications du projet à reussis"));
                    }

                } else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                    $tempProject = $this->PM->read($_GET['Pk']);
                    $projectJobs = $this->PM->listByJobs($_GET['Pk']);
                    $tempProject->setJobs($projectJobs);

                    echo $this->TemplateEngine->render('Project/UpdateProject.twig', [
                        'ProjectEntity' => $tempProject,
                    ]);
                }
            }
        } catch (DatabaseException|MissingInformation $e) {
            header('Location: getProject?errorMessage=' . urlencode($e->getMessage()));
        } catch (NotFoundException $e){
            header('Location: error404');
        }
    }
    public function deleteProject() : void{
        $this->requireLogin();
        try {
            if (isset($_GET['Pk'])) {
                $this->PM->delete($_GET['Pk']);
                header('Location: getProject?successMessage=' . urlencode("Le project à bien été supprimé"));
            }
        } catch (NotFoundException $e){
            header('Location: error404');
        } catch (DatabaseException $e) {
            header('Location: getProject?errorMessage=' . urlencode($e->getMessage()));
        }
    }
}