<?php

namespace DISEUMAT\Controller;

use DISEUMAT\Controller\BaseController;
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
        echo "test";
    }
    public function updateProj() : void{
        echo "test";
    }
    public function deleteProj() : void{
        echo "test";
    }
}