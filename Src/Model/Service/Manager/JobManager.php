<?php

namespace DISEUMAT\Model\Service\Manager;

use DISEUMAT\Model\Entity\JobEntity;
use PDO;

class JobManager
{
    private $pdb;
    public function __construct(){
        $this->pdb = DBManager::getInstance();
    }
    public function list() : array {
        $query = $this->pdb->prepare("SELECT * FROM Job");
        $query->execute();

        $TabJob = array();
        while ($record = $query->fetch(PDO::FETCH_ASSOC)){
            $TabJob[] = JobEntity::fromArray($record);
        }
        return $TabJob;
    }
    public function read(int $pk){
        $query = $this->pdb->prepare("SELECT * FROM Job WHERE Pk_Job = ?");
        $query->execute([$pk]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

}