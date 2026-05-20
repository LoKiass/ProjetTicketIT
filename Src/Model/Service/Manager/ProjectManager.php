<?php

namespace DISEUMAT\Model\Service\Manager;

use DISEUMAT\Exception\DatabaseException;
use DISEUMAT\Exception\LinkExistBetween;
use DISEUMAT\Exception\NotFoundException;
use DISEUMAT\Model\Entity\ProjectEntity;
use PDO;
use PDOException;

class ProjectManager
{
    private $pdb;
    public function __construct(){
        $this->pdb = DBManager::getInstance();
    }

    public function create(ProjectEntity $entity) : int{
        try{
            $query = $this->pdb->prepare("INSERT INTO projet (Ident, Descr, Dstart, DClotEst, Budget) VALUES (?, ?, ?, ?, ?)");
            $query->execute([
                $entity->getIdent(),
                $entity->getDescr(),
                $entity->getDstart(),
                $entity->getDClotEst(),
            ]);

            return (int)$this->pdb->lastInsertId();
        } catch (PDOException $e){
            throw new DatabaseException("Erreur lors de l'authentification", 0);
        }
    }
    public function list() : array{
        try{
            $query = $this->pdb->prepare("SELECT * FROM projet");
            $query->execute();

            $TabProject = array();

            while ($record = $query->fetch(PDO::FETCH_ASSOC)){
                $TabProject[] = ProjectEntity::fromArray($record);
            }

            if(empty($TabProject)){
                return [];
            }

            return $TabProject;

        } catch (PDOException $e){
            throw new DatabaseException("Erreur lors de l'authentification",0);
        }
    }
    public function read(int $pk) : ProjectEntity{
        try{
            $query = $this->pdb->prepare("SELECT * FROM projet WHERE Pk_Projet = ?");
            $query->execute([$pk]);

            if ($query->rowCount() === 0){
                throw new NotFoundException("Le projet n'existe pas");
            }

            return ProjectEntity::fromArray($query->fetch());
        } catch (PDOException $e){
            throw new DatabaseException("Erreur lors de l'authentifications", 0);
        }
    }
    public function update(ProjectEntity $entity) : void {
        try{
            $query = $this->pdb->prepare("UPDATE projet SET Ident = ?, Descr = ?, Dstart = ?, DClotEst = ?, Budget = ? WHERE Pk_Projet = ?");
            $query->execute([
                $entity->getIdent(),
                $entity->getDescr(),
                $entity->getDstart(),
                $entity->getDClotEst(),
                $entity->getBudget(),
                $entity->getPk(),
            ]);
        } catch (PDOException $e){
            throw new DatabaseException("Erreur lors de l'authentifications");
        }
    }
    public function delete(int $pk) : void {
        try{
            $this->isLinkedToJob($pk);

            $query = $this->pdb->prepare("DELETE FROM projet WHERE Pk_Projet = ?");
            $query->execute([$pk]);

            if ($query->rowCount() === 0) {
                throw new NotFoundException("Le projet n'existe pas");
            }
        } catch (PDOException $e){

        }
    }

    public function isLinkedToJob(int $pk) : void{
        try{
            $query = $this->pdb->prepare("SELECT COUNT(*) FROM job WHERE Fk_Project = ?");
            $query->execute([$pk]);

            if ($query->fetchColumn() > 0) {
                throw new LinkExistBetween("Un lien existe entre le projet et un job");
            }

        } catch (PDOException $e){
            throw new DatabaseException("Erreur lors de l'authentifications");
        }
    }
}