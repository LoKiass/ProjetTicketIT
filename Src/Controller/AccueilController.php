<?php

namespace DISEUMAT\Controller;

use DISEUMAT\Model\Service\Manager\JobManager;
use DISEUMAT\Model\Service\Manager\ProjectManager;
use DISEUMAT\Model\Service\Manager\TechManager;

class AccueilController extends BaseController
{
    private TechManager $TM;
    private JobManager $JB;
    private ProjectManager $PM;

    public function __construct(){
        parent::__construct();
        $this->TM = new TechManager();
        $this->JB = new JobManager();
        $this->PM = new ProjectManager();
    }

    public function formAccueil() : void{
        $this->requireLogin();

        $nbrTech = count($this->TM->list());
        $nbrJobs = count($this->JB->list());
        $nbrProject = count($this->PM->list());

        echo $this->TemplateEngine->render('/Accueil/Accueil.twig',
            ['nbrTech' => $nbrTech,
                'nbrJobs' => $nbrJobs,
                'nbrProject' => $nbrProject]);
    }
}