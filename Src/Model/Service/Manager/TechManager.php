<?php

namespace DISEUMAT\Model\Service\Manager;
use DISEUMAT\Model\Entity\TechEntity;

class TechManager
{
    private $pdb;
    public function __construct(){
        $this->pdb = DBManager::getInstance();
    }

    public function read(int $id) : TechEntity
    {
        $Pk = $id;
        $query = <<< SQL
            SELECT Pk_Tech, Nom, Pren, Email, Actif
            FROM Tech WHERE Pk_Tech = $Pk;
        SQL;

        $retour = $this->pdb->query($query);
        if($record = $retour->fetch())
        {
            $tempTech = TechEntity::fromArray($record);
            return $tempTech;
        }

    }

    public function list() : array
    {
        $query = <<< SQL
            SELECT Pk_Tech, Nom, Pren, Email, Actif
            FROM tech;
        SQL;
        $retour = $this->pdb->query($query);
        $TabTech = array();
        while ($record = $retour->fetch())
        {
            $tempTech = TechEntity::fromArray($record);
            $TabTech[] = clone $tempTech;
        }
        return $TabTech;
    }
    public function create($entity) : bool{
        $query = "INSERT INTO Tech (Nom, Pren, Email, Actif) VALUES ("
            . "'" . $entity->getNom() . "', "
            . "'" . $entity->getPrenom() . "', "
            . "'" . $entity->getEmail() . "', "
            . "'" . ($entity->getActif() ?? 0) . "')";


        $retour = $this->pdb->query($query);

        if ($retour){
            return true;
        }
        return false;
    }
    public function update(int $pk){
    }
}