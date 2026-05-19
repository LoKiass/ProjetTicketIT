<?php

namespace DISEUMAT\Controller;

use DISEUMAT\Controller\BaseController;
use DISEUMAT\Exception\DatabaseException;
use DISEUMAT\Exception\NotFoundException;
use DISEUMAT\Model\Entity\JobEntity;
use DISEUMAT\Model\Entity\TechEntity;
use DISEUMAT\Model\Service\Manager\JobManager;
use DISEUMAT\Model\Service\Manager\TechManager;
use JetBrains\PhpStorm\ObjectShape;

class JobController extends BaseController
{
    private JobManager $JM;
    private TechManager $TM;
    public function __construct(){
        parent::__construct();
        $this->JM = new JobManager();
        $this->TM = new TechManager();
    }
    public function getJob() : void{
        $this->requireLogin();
        if (isset($_GET['Pk'])){
            $pk = $_GET['Pk'];
            $JobEntity = $this->JM->read($pk);
            // $techRelated =
            echo $this->TemplateEngine->render('/Job/Job.twig', ['Pk' => $pk]);
        }
        else{
            $TabJob = $this->JM->list();
            echo $this->TemplateEngine->render('/Job/ListJob.twig', ['TabJob' => $TabJob]);
        }
    }

    public function createJob() : void{
        $this->requireLogin();
        if (isset($_POST['Fk_Project'])){
            $Jobs = new JobEntity();
            $Jobs->setFk_project($_POST['Fk_Project']);
            $Jobs->setTitre($_POST['Titre']);
            $Jobs->setDscr($_POST['Dscr']);
            $Jobs->setStatus($_POST['Status']);
            $Jobs->setPrior($_POST['Prior']);
            $Jobs->setDstart($_POST['Dstart']);
            $Jobs->setDech($_POST['Dech']);
            $Jobs->setDclot($_POST['Dclot']);

            $techId = $this->JM->create($Jobs);

            if (isset($_POST['Tech']) && is_array($_POST['Tech'])) {
                foreach ($_POST['Tech'] as $idTech) {
                    $this->JM->linkToTech($techId, $idTech);
                }
            }

        }
        else{
            $TabTech = $this->TM->list();
            echo $this->TemplateEngine->render('/Job/CreateJob.twig',[
                'TabTech' => $TabTech,
            ]);
        }
    }
    public function updateJob() : void {
        $this->requireLogin();
            if (isset($_GET['Pk'])){
                $pk = $_GET['Pk'];

                if ($_SERVER['REQUEST_METHOD'] === 'POST'){
                    $_POST['Pk_Job'] = $pk;

                    $this->JM->update(JobEntity::fromArray($_POST));
                    $this->JM->unlinkAllTech($pk);


                    if (isset($_POST['tech']) && is_array($_POST['tech'])) {
                        foreach ($_POST['tech'] as $idTech) {
                            $this->JM->linkToTech($pk, $idTech);
                        }
                    }

                    header('Location: getJob?success=true');
                }
                else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                    $tempJob = $this->JM->read($_GET['Pk']);
                    $jobTech = $this->TM->listByJob($_GET['Pk']);
                    $tempJob->setTech($jobTech);

                    echo $this->TemplateEngine->render("Job/UpdateJob.twig",
                        ['JobEntity' => $tempJob
                            , 'TabTech' => $this->TM->list()]
                    );
                }
            }
    }
}