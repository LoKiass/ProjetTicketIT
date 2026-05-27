<?php

namespace DISEUMAT\Model\Service\Manager;

use DISEUMAT\Exception\DatabaseException;
use DISEUMAT\Exception\LinkExistBetween;
use DISEUMAT\Exception\NotCreatedInDatabase;
use DISEUMAT\Exception\NotFoundException;
use DISEUMAT\Model\Entity\JobEntity;
use PDO;
use PDOException;

class JobManager
{
    private $pdb;

    public function __construct(){
        $this->pdb = DBManager::getInstance();
    }
    /*
     * Permet de de lire la liste des jobs en BDD, pour les afficher dans le menu prévue à cette effet
     */
    public function list() : array {
        try{
            $query = $this->pdb->prepare("SELECT * FROM Job");
            $query->execute();

            $TabJob = array();
            while ($record = $query->fetch(PDO::FETCH_ASSOC)){
                $TabJob[] = JobEntity::fromArray($record);
            }

            if(empty($TabJob)){
                return [];
            }

            return $TabJob;
        } catch (\PDOException $e){
            throw new DatabaseException("Erreur lors de la lecture de la liste des jobs");
        }
    }
    /*
     * Permet de lire un jobs, avec la pk fournite
     */
    public function read(int $pk){
        try{
            $query = $this->pdb->prepare("SELECT * FROM Job WHERE Pk_Job = ?");
            $query->execute([$pk]);

            if ($query->rowCount() === 0){
                throw new NotFoundException("Le job n'existe pas");
            }

            return JobEntity::fromArray($query->fetch(PDO::FETCH_ASSOC));
        } catch (\PDOException $e){
            throw new DatabaseException("Erreur lors de la lecture du job");
        }
    }
    /*
     * Permet de crée un job en BDD
     */
    public function create(JobEntity $jobEntity) : int {
       try{
           $query = $this->pdb->prepare("INSERT INTO Job (Fk_Project, Titre, Status, Prior, Dstart, Dech, Dclot, Dscr) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
           $query->execute([$jobEntity->getFk_project(), $jobEntity->getTitre(), $jobEntity->getStatus(), $jobEntity->getPrior(), $jobEntity->getDstart(), $jobEntity->getDech(), $jobEntity->getDclot(), $jobEntity->getDscr()]);

           if ($query->rowCount() === 0){
               throw new NotCreatedInDatabase("Le jobs n'a pas été crée en base de donné");
           }

           return (int)$this->pdb->lastInsertId();
       } catch (\PDOException $e){
           throw new DatabaseException("Erreur lors de l'authentification");
       }
    }

    /*
     * Permet de lier un téchniciens à un jobs en BDD, via la table de jointure tech_jobs
     */
    public function linkToTech(int $Pk_Job, int $Pk_Tech) : void {
        try{
            $query = $this->pdb->prepare("INSERT INTO tech_jobs (Fk_Job, Fk_Tech) VALUES (?, ?)");
            $query->execute([$Pk_Job, $Pk_Tech]);
        } catch (\PDOException $e){
            throw new DatabaseException("Erreur lors de l'insertion dans la table tech_fonction");
        }
    }

    /*
     * Permet de supprimer tous les liens entre un technicien et un job
     */
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
     * Cette méthode permet de modifier un jobs déjà existant en BDD
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
            throw new DatabaseException("Erreur lors de l'update", 0);
        }
    }
    /*
    * Cette méthode permet de supprimer une jobs de la BD si aucun liens n'existe entre elle et un technicien (Via checklink)
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
     * Cette méthode permet de verifier si un liens existe au niveau BD entre un téchniciens & un jobs
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