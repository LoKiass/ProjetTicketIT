<?php

namespace DISEUMAT\Controller;

use DISEUMAT\Controller\BaseController;

class JobController extends BaseController
{
    public function __construct(){
        parent::__construct();
    }
    public function getJob() : void{
        echo $this->TemplateEngine->render('/Job/ListJob.twig');
    }
}