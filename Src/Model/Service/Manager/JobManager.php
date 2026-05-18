<?php

namespace DISEUMAT\Model\Service\Manager;

class JobManager
{
    private $pdb;
    public function __construct(){
        $this->pdb = DBManager::getInstance();
    }
    public function list(){
        $query = $this->pdb->prepare("SELECT * FROM Job");
        $query->execute();
        return $query->fetchAll();
    }
    public function read(int $pk){
        $query = $this->pdb->prepare("SELECT * FROM Job WHERE Pk_Job = ?");
        $query->execute([$pk]);
        return $query->fetch();
    }

}