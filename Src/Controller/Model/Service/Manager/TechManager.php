<?php

namespace DISEUMAT\Controller\Model\Service\Manager;
use DISEUMAT\Controller\Model\Entity\TechEntity;

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
    public function update(TechEntity $entity){
        $query = <<< SQL
            UPDATE Tech set 
            Nom = '{$entity->getNom()}',
            Pren= '{$entity->getPrenom()}',
            Email = '{$entity->getEmail()}',
            Actif = '{$entity->getActif()}'
            WHERE Pk_Tech = '{$entity->getPk()}';
        SQL;
        $retour = $this->pdb->query($query);
        if ($retour->rowCount() > 0){
            return true;
        }
        return false;
    }
}