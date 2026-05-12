<?php

namespace DISEUMAT\Model\Service\Manager;
use DISEUMAT\Exception\DatabaseException;
use DISEUMAT\Exception\NotCreatedInDatabase;
use DISEUMAT\Exception\NotFoundException;
use DISEUMAT\Model\Entity\TechEntity;

class TechManager
{
    private $pdb;
    public function __construct(){
        $this->pdb = DBManager::getInstance();
    }

    public function read(int $id) : TechEntity {
        try{
            $Pk = $id;
            $query = <<< SQL
            SELECT Pk_Tech, Nom, Pren, Email, Actif
            FROM tech WHERE Pk_Tech = ?;
        SQL;

            $query = $this->pdb->prepare($query);
            $query->execute([$Pk]);

            if($query->rowCount() === 0){
                throw new NotFoundException("Le technicien n'existe pas");
            }

            return TechEntity::fromArray($query->fetch());

        } catch (\PDOException $e){
            throw new DatabaseException($e->getMessage(), 0);
        }

    }

    public function list() : array {
        try{
            $query = <<< SQL
            SELECT Pk_Tech, Nom, Pren, Email, Actif
            FROM tech;
        SQL;

            $query = $this->pdb->prepare($query);
            $query->execute();

            $TabTech = array();
            while ($record = $query->fetch())
            {
                $tempTech = TechEntity::fromArray($record);
                $TabTech[] = clone $tempTech;
            }

            if (empty($TabTech)){
                throw new NotFoundException("La liste est vide ou aucun technicien n'existe");
            }

            return $TabTech;
        }catch (\PDOException $e){
            throw new DatabaseException($e->getMessage(), 0);
        }
    }

    public function create($entity) : void {
       try{
           $query = "INSERT INTO tech (Nom, Pren, Email, Actif) VALUES (?, ?, ?, ?)";

           $query = $this->pdb->prepare($query);
           $query->execute([
               $entity->getNom(),
               $entity->getPrenom(),
               $entity->getEmail(),
               ((int)$entity->getActif() ?? 0)
           ]);

           if ($query->rowCount() === 0){
                throw new NotCreatedInDatabase("Le techniciens n'a pas été crée", 0);
           }
       } catch (\PDOException $e){
            throw new DatabaseException($e->getMessage(), 0);
       }
    }

    public function update(TechEntity $entity) : bool {
        try {
            $check = $this->pdb->prepare("SELECT 1 FROM tech WHERE Pk_Tech = ?");
            $check->execute([$entity->getPk()]);

            if ($check->rowCount() === 0) {
                throw new NotFoundException("Tech introuvable", 0);
            }

            $query = <<<SQL
                UPDATE tech SET 
                    Nom   = ?,
                    Pren  = ?,
                    Email = ?,
                    Actif = ?
                WHERE Pk_Tech = ?
            SQL;

            $query = $this->pdb->prepare($query);
            $query->execute([
                $entity->getNom(),
                $entity->getPrenom(),
                $entity->getEmail(),
                (int)$entity->getActif(),
                $entity->getPk()
            ]);

            return true;

        } catch (\PDOException $e) {
            throw new DatabaseException($e->getMessage(), 0);
        }
    }
}