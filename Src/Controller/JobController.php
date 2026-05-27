<?php

namespace DISEUMAT\Controller;

use DISEUMAT\Controller\BaseController;
use DISEUMAT\Exception\DatabaseException;
use DISEUMAT\Exception\LinkExistBetween;
use DISEUMAT\Exception\MissingInformation;
use DISEUMAT\Exception\NotCreatedInDatabase;
use DISEUMAT\Exception\NotFoundException;
use DISEUMAT\Model\Entity\JobEntity;
use DISEUMAT\Model\Service\Manager\JobManager;
use DISEUMAT\Model\Service\Manager\TechManager;

class JobController extends BaseController
{
    private JobManager $JM;
    private TechManager $TM;

    public function __construct(){
        parent::__construct();
        $this->JM = new JobManager();
        $this->TM = new TechManager();
    }

    public function getJob() : void {
        $this->requireLogin();

        try {
            if (isset($_GET['Pk'])) {
                $pk = $_GET['Pk'];
                $JobEntity = $this->JM->read($pk);
                $listTech = $this->TM->listByJob($pk);
                $JobEntity->setTech($listTech);
                echo $this->TemplateEngine->render('/Job/InfoJob.twig', ['JobEntity' => $JobEntity]);
            } else {
                $TabJob = $this->JM->list();

                // Gestion des logs des actions
                if (isset($_GET['successMessage'])) {
                    $successMessageFromUrl = $_GET['successMessage'];
                } else {
                    $successMessageFromUrl = null;
                }
                $errorMessage  = $_GET['errorMessage'] ?? null;

                echo $this->TemplateEngine->render('/Job/ListJob.twig', [
                    'TabJob' => $TabJob,
                    'successMessage' => $successMessageFromUrl,
                    'errorMessage' => $errorMessage,
                ]);
            }
        } catch (DatabaseException|NotFoundException $e) {
            echo $this->TemplateEngine->render('/Job/ListJob.twig', [
                'TabJob'       => null,
                'errorMessage' => $e->getMessage(),
            ]);
        }
    }

    public function createJob() : void {
        $this->requireLogin();
        try {
            if (isset($_POST['Fk_Project'])) {
                $Jobs = JobEntity::fromArray($_POST);
                $techId = $this->JM->create($Jobs);

                if (isset($_POST['Tech']) && is_array($_POST['Tech'])) {
                    foreach ($_POST['Tech'] as $idTech) {
                        $this->JM->linkToTech($techId, $idTech);
                    }
                }
                echo $this->TemplateEngine->render('Job/CreateJob.twig', [
                    'success' => true,
                    'TabTech' => $TabTech,
                ]);
            } else {
                if (isset($_GET['Pk'])){
                    $PkProject = $_GET['Pk'];
                }
                $TabTech = $this->TM->list();
                echo $this->TemplateEngine->render('/Job/CreateJob.twig', [
                    'TabTech' => $TabTech,
                    'PkProject' => $PkProject ?? null,
                ]);
            }
        } catch (DatabaseException|NotCreatedInDatabase|NotFoundException|MissingInformation $e) {
            $TabTech = $this->TM->list();
            echo $this->TemplateEngine->render('/Job/CreateJob.twig', [
                'TabTech' => $TabTech,
                'errorMessage'   => $e->getMessage(),
            ]);
        }
    }

    public function updateJob() : void {
        $this->requireLogin();
        try {
            if (isset($_GET['Pk'])) {
                $pk = $_GET['Pk'];

                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $_POST['Pk_Job'] = $pk;

                    $this->JM->update(JobEntity::fromArray($_POST));
                    $this->JM->unlinkAllTech($pk);

                    if (isset($_POST['tech']) && is_array($_POST['tech'])) {
                        foreach ($_POST['tech'] as $idTech) {
                            $this->JM->linkToTech($pk, $idTech);
                        }
                    }

                    header('Location: getJob?successMessage=' . urlencode("La modifications du jobs à reussis"));
                } else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                    $tempJob = $this->JM->read($_GET['Pk']);
                    $jobTech = $this->TM->listByJob($_GET['Pk']);
                    $tempJob->setTech($jobTech);

                    echo $this->TemplateEngine->render('Job/UpdateJob.twig', [
                        'JobEntity' => $tempJob,
                        'TabTech'   => $this->TM->list(),
                    ]);
                }
            }
        } catch (DatabaseException|NotFoundException|MissingInformation $e) {
            header('Location: getJob?errorMessage=' . urlencode($e->getMessage()));
        }
    }

    public function deleteJob() : void {
        $this->requireLogin();
        try {
            if (isset($_GET['Pk'])) {
                $this->JM->delete($_GET['Pk']);
                header('Location: getJob?successMessage=' . urlencode("Le jobs à bien été supprimé"));
            }
        } catch (NotFoundException|DatabaseException|LinkExistBetween $e) {
            header('Location: getJob?errorMessage=' . urlencode($e->getMessage())) ;
        }
    }
}