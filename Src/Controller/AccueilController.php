<?php

namespace DISEUMAT\Controller;

use DISEUMAT\Model\Service\Manager\JobManager;
use DISEUMAT\Model\Service\Manager\TechManager;

class AccueilController extends BaseController
{
    private TechManager $TM;
    private JobManager $JB;

    public function __construct(){
        parent::__construct();
        $this->TM = new TechManager();
        $this->JB = new JobManager();
    }

    public function formAccueil() : void{
        $this->requireLogin();

        $nbrTech = count($this->TM->list());
        $nbrJobs = count($this->JB->list());

        echo $this->TemplateEngine->render('/Accueil/Accueil.twig',
            ['nbrTech' => $nbrTech,
                'nbrJobs' => $nbrJobs]);
    }
}