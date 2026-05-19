<?php

namespace DISEUMAT\Model\Service\Manager;

use DISEUMAT\Exception\DatabaseException;
use DISEUMAT\Exception\LinkExistBetween;
use DISEUMAT\Exception\NotFoundException;
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

        return JobEntity::fromArray($query->fetch(PDO::FETCH_ASSOC));
    }

    public function create(JobEntity $jobEntity) : int {
        $query = $this->pdb->prepare("INSERT INTO Job (Fk_Project, Titre, Status, Prior, Dstart, Dech, Dclot, Dscr) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $query->execute([$jobEntity->getFk_project(), $jobEntity->getTitre(), $jobEntity->getStatus(), $jobEntity->getPrior(), $jobEntity->getDstart(), $jobEntity->getDech(), $jobEntity->getDclot(), $jobEntity->getDscr()]);

        return (int)$this->pdb->lastInsertId();
    }

    public function linkToTech(int $Pk_Job, int $Pk_Tech) : void {
        $query = $this->pdb->prepare("INSERT INTO tech_jobs (Fk_Job, Fk_Tech) VALUES (?, ?)");
        $query->execute([$Pk_Job, $Pk_Tech]);
    }
    public function unlinkAllTech(int $Pk_Jobs) : void {
        try{
            $query = "DELETE FROM tech_jobs WHERE Fk_Job = ?";
            $query = $this->pdb->prepare($query);
            $query->execute([$Pk_Jobs]);
        } catch (\PDOException $e){
            throw new DatabaseException("Erreur lors de la suppression des fonctions du technicien");
        }
    }
    /*
 * Cette méthode permet de modifier un téchnciens déjà existant en BDD
 */
    public function update(JobEntity $entity) : void {
        try {
            $check = $this->pdb->prepare("SELECT 1 FROM job WHERE Pk_Job = ?");
            $check->execute([$entity->getPk()]);

            if ($check->rowCount() === 0) {
                throw new NotFoundException("Tech introuvable", 0);
            }

            $query = <<<SQL
                UPDATE job SET 
                   Fk_Project = ?,
                   Titre = ?,
                   Status = ?,
                   Prior = ?,
                   Dstart = ?,
                   Dech = ?,
                   Dclot = ?,
                   Dscr = ?
                WHERE Pk_Job = ?
            SQL;

            $query = $this->pdb->prepare($query);
            $query->execute([
                $entity->getFk_project(),
                $entity->getTitre(),
                $entity->getStatus(),
                $entity->getPrior(),
                $entity->getDstart(),
                $entity->getDech(),
                $entity->getDclot(),
                $entity->getDscr(),
                $entity->getPk()
            ]);
        } catch (\PDOException $e) {
            throw new DatabaseException($e->getMessage(), 0);
        }
    }
    /*
 * Cette méthode permet de supprimer une fonction de la BD si aucun liens n'existe entre elle et un technicien (Via checklink)
 */
    public function delete(int $pk) : void {
        try {
            $this->checkLink($pk);

            $query = $this->pdb->prepare("DELETE FROM job WHERE Pk_Job = ?");
            $query->execute([$pk]);

            if ($query->rowCount() === 0) {
                throw new NotFoundException("Le job n'existe pas");
            }
        } catch (PDOException $e) {
            throw new DatabaseException("Erreur lors de la suppression", 0, $e);
        }
    }

    /*
     * Cette méthode permet de verifier si un liens existe au niveau BD entre un téchniciens & une fonction
     */
    public function checkLink(int $pk) : void {
        try {
            $query = $this->pdb->prepare("SELECT COUNT(*) FROM tech_jobs WHERE Fk_Job = ?");
            $query->execute([$pk]);

            if ($query->fetchColumn() > 0) {
                throw new LinkExistBetween("Un lien existe entre le jobs et un technicien");
            }
        } catch (PDOException $e) {
            throw new DatabaseException("Le lien n'a pas pu être vérifié", 0, $e);
        }
    }

}