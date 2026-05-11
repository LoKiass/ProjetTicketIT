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
            FROM tech WHERE Pk_Tech = ?;
        SQL;

        $stmt = $this->pdb->prepare($query);
        $stmt->execute([$Pk]);

        if($record = $stmt->fetch())
        {
            $tempTech = TechEntity::fromArray($record);
            return $tempTech;
        }
        else {
            return new TechEntity(); // Dans le cas ou le read na pas trouve
        }

    }

    public function list() : array
    {
        $query = <<< SQL
            SELECT Pk_Tech, Nom, Pren, Email, Actif
            FROM tech;
        SQL;

        $stmt = $this->pdb->prepare($query);
        $stmt->execute();

        $TabTech = array();
        while ($record = $stmt->fetch())
        {
            $tempTech = TechEntity::fromArray($record);
            $TabTech[] = clone $tempTech;
        }
        return $TabTech;
    }

    public function create($entity) : bool {
        $query = "INSERT INTO tech (Nom, Pren, Email, Actif) VALUES (?, ?, ?, ?)";

        $stmt = $this->pdb->prepare($query);
        $stmt->execute([
            $entity->getNom(),
            $entity->getPrenom(),
            $entity->getEmail(),
            ($entity->getActif() ?? 0)
        ]);

        if ($stmt->rowCount() > 0){
            return true;
        }
        return false;
    }

    public function update(TechEntity $entity){
        $query = <<< SQL
            UPDATE tech set 
            Nom = ?,
            Pren= ?,
            Email = ?,
            Actif = ?
            WHERE Pk_Tech = ?;
        SQL;

        $stmt = $this->pdb->prepare($query);
        $stmt->execute([
            $entity->getNom(),
            $entity->getPrenom(),
            $entity->getEmail(),
            $entity->getActif(),
            $entity->getPk()
        ]);

        if ($stmt->rowCount() > 0){
            return true;
        }
        return false;
    }
}