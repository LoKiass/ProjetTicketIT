<?php

namespace DISEUMAT\Controller;

use DISEUMAT\Controller\BaseController;
use DISEUMAT\Model\Service\Manager\JobManager;

class JobController extends BaseController
{
    private JobManager $JM;
    public function __construct(){
        parent::__construct();
        $this->JM = new JobManager();
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
}