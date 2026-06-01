<?php

namespace DISEUMAT\Model\Service\Manager;

use DISEUMAT\Exception\DatabaseException;
use DISEUMAT\Exception\LinkExistBetween;
use DISEUMAT\Exception\MissingInformation;
use DISEUMAT\Exception\NotFoundException;
use DISEUMAT\Model\Entity\JobEntity;
use DISEUMAT\Model\Entity\ProjectEntity;
use PDO;
use PDOException;

/**
 *
 */
class ProjectManager
{
    /**
     * @var PDO
     */
    private PDO $pdb;

    /**
     *
     */
    public function __construct(){
        $this->pdb = DBManager::getInstance();
    }

    /**
     * @param ProjectEntity $entity
     * @return int
     * @throws DatabaseException
     */
    public function create(ProjectEntity $entity) : int{
        try{
            $query = $this->pdb->prepare("INSERT INTO project (Ident, Descr, Dstart, DClotEst, Budget) VALUES (?, ?, ?, ?, ?)");
            $query->execute([
                $entity->getIdent(),
                $entity->getDescr(),
                $entity->getDstart(),
                $entity->getDClotEst(),
                $entity->getBudget(),
            ]);

            return (int)$this->pdb->lastInsertId();
        } catch (PDOException){
            throw new DatabaseException("Erreur lors de l'authentification");
        }
    }

    /**
     * @return array
     * @throws DatabaseException
     * @throws MissingInformation
     */
    public function list() : array{
        try{
            $query = $this->pdb->prepare("SELECT * FROM project");
            $query->execute();

            $TabProject = array();

            while ($record = $query->fetch(PDO::FETCH_ASSOC)){
                $TabProject[] = ProjectEntity::fromArray($record);
            }

            if(empty($TabProject)){
                return [];
            }

            return $TabProject;

        } catch (PDOException){
            throw new DatabaseException("Erreur lors de l'authentification");
        }
    }

    /**
     * @param int $pk
     * @return ProjectEntity
     * @throws DatabaseException
     * @throws MissingInformation
     * @throws NotFoundException
     */
    public function read(int $pk) : ProjectEntity{
        try{
            $query = $this->pdb->prepare("SELECT * FROM project WHERE Pk_Project = ?");
            $query->execute([$pk]);

            if ($query->rowCount() === 0){
                throw new NotFoundException("Le projet n'existe pas");
            }

            return ProjectEntity::fromArray($query->fetch());
        } catch (PDOException){
            throw new DatabaseException("Erreur lors de l'authentifications");
        } catch (MissingInformation) {
            throw new MissingInformation("Des informations sont manquantes");
        }

    }

    /**
     * @param ProjectEntity $entity
     * @return void
     * @throws DatabaseException
     */
    public function update(ProjectEntity $entity) : void {
        try{
            $query = $this->pdb->prepare("UPDATE project SET Ident = ?, Descr = ?, Dstart = ?, DClotEst = ?, Budget = ? WHERE Pk_Project = ?");
            $query->execute([
                $entity->getIdent(),
                $entity->getDescr(),
                $entity->getDstart(),
                $entity->getDClotEst(),
                $entity->getBudget(),
                $entity->getPk(),
            ]);
        } catch (PDOException){
            throw new DatabaseException("Erreur lors de l'authentifications");
        }
    }

    /**
     * @param int $pk
     * @return void
     * @throws DatabaseException
     * @throws LinkExistBetween
     * @throws NotFoundException
     */
    public function delete(int $pk) : void {
        try{
            $this->isLinkedToJob($pk);

            $query = $this->pdb->prepare("DELETE FROM project WHERE Pk_Project = ?");
            $query->execute([$pk]);

            if ($query->rowCount() === 0) {
                throw new NotFoundException("Le projet n'existe pas");
            }
        } catch (PDOException){
            throw new DatabaseException("Erreur lors de l'authentifications");
        } catch (LinkExistBetween){
            throw new LinkExistBetween("Un lien existe entre le projet et un job");
        }
    }

    /**
     * @param int $pk
     * @return void
     * @throws DatabaseException
     * @throws LinkExistBetween
     */
    public function isLinkedToJob(int $pk) : void{
        try{
            $query = $this->pdb->prepare("SELECT COUNT(*) FROM job WHERE Fk_Project = ?");
            $query->execute([$pk]);

            if ($query->fetchColumn() > 0) {
                throw new LinkExistBetween("Un lien existe entre le projet et un job");
            }

        } catch (PDOException){
            throw new DatabaseException("Erreur lors de l'authentifications");
        }
    }

    /**
     * @param int $Pk
     * @return array
     * @throws DatabaseException
     * @throws MissingInformation
     */
    public function listByJobs(int $Pk): array
    {
        try{
            $query = $this->pdb->prepare("SELECT * FROM job WHERE Fk_Project = ?");
            $query->execute([$Pk]);

            $TabJob = array();

            while ($record = $query->fetch(PDO::FETCH_ASSOC)){
                $TabJob[] = JobEntity::fromArray($record);
            }

            return $TabJob;
        }catch (PDOException){
            throw new DatabaseException("Erreur lors de l'authentification");
        }
    }
}