<?php

namespace DISEUMAT\Model\Service\Manager;
class TechManager
{
    private $pdb;
    public function __construct(){
        $this->pdb = DBManager::getInstance();
    }

    public function read(bool $isCreate = false){
    }
    public function create($Pren, $Nom, $Email, $Actif) : bool{
        $query = "INSERT INTO Tech (Nom, Pren, Email, Actif) VALUES ('$Nom', '$Pren', '$Email', '$Actif')";
        $retour = $this->pdb->query($query);

        if ($retour){
            return true;
        }
        return false;
    }
    public function update(int $pk){
    }
    public function delete(int $pk){
    }


}