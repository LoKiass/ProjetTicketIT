<?php

namespace DISEUMAT\Model\Service\Manager;

use DISEUMAT\Exception\DatabaseException;
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
        $query->execute();

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

}