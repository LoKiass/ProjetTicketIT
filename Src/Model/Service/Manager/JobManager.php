<?php

namespace DISEUMAT\Model\Service\Manager;

use DISEUMAT\Exception\DatabaseException;
use DISEUMAT\Exception\LinkExistBetween;
use DISEUMAT\Exception\MissingInformation;
use DISEUMAT\Exception\NotCreatedInDatabase;
use DISEUMAT\Exception\NotFoundException;
use DISEUMAT\Model\Entity\JobEntity;
use PDO;
use PDOException;

/**
 * Manager responsable de toutes les opérations en base de données
 * liées aux jobs (tickets/tâches) du système.
 */
class JobManager
{
    /**
     * @var PDO
     */
    private PDO $pdb;

    /**
     * Initialise la connexion à la base de données via le singleton DBManager.
     */
    public function __construct(){
        $this->pdb = DBManager::getInstance();
    }

    /**
     * Récupère la liste complète de tous les jobs présents en base de données.
     * Retourne un tableau vide si aucun job n'existe.
     * Lance une DatabaseException en cas d'erreur PDO.
     *
     * @return array
     * @throws DatabaseException
     * @throws MissingInformation
     */
    public function list() : array {
        try{
            $query = $this->pdb->prepare("SELECT * FROM job");
            $query->execute();

            $TabJob = array();
            while ($record = $query->fetch(PDO::FETCH_ASSOC)){
                $TabJob[] = JobEntity::fromArray($record);
            }

            if(empty($TabJob)){
                return [];
            }

            return $TabJob;
        } catch (PDOException){
            throw new DatabaseException("Erreur lors de la lecture de la liste des jobs");
        } catch (MissingInformation) {
            throw new MissingInformation("Des informations sont manquantes");
        }
    }

    /**
     * Récupère un job spécifique depuis la base de données via sa clé primaire.
     * Lance une NotFoundException si aucun job ne correspond à la PK fournie.
     *
     * @param int $pk
     * @return JobEntity
     * @throws DatabaseException
     * @throws MissingInformation
     * @throws NotFoundException
     */
    public function read(int $pk) : JobEntity{
        try{
            $query = $this->pdb->prepare("SELECT * FROM job WHERE Pk_Job = ?");
            $query->execute([$pk]);

            if ($query->rowCount() === 0){
                throw new NotFoundException("Le job n'existe pas");
            }

            return JobEntity::fromArray($query->fetch(PDO::FETCH_ASSOC));
        } catch (PDOException){
            throw new DatabaseException("Erreur lors de la lecture du job");
        } catch (MissingInformation) {
            throw new MissingInformation("Des informations sont manquantes");
        }

    }

    /**
     * Insère un nouveau job en base de données à partir d'un JobEntity.
     * Retourne la clé primaire (PK) du job nouvellement créé.
     * Lance une NotCreatedInDatabase si aucune ligne n'a été insérée.
     *
     * @param JobEntity $jobEntity
     * @return int
     * @throws DatabaseException
     * @throws NotCreatedInDatabase
     */
    public function create(JobEntity $jobEntity) : int {
        try{
            $query = $this->pdb->prepare("INSERT INTO job (Fk_Project, Titre, Status, Prior, Dstart, Dech, Dclot, Dscr) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $query->execute([$jobEntity->getFk_project(), $jobEntity->getTitre(), $jobEntity->getStatus(), $jobEntity->getPrior(), $jobEntity->getDstart(), $jobEntity->getDech(), $jobEntity->getDclot(), $jobEntity->getDscr()]);

            if ($query->rowCount() === 0){
                throw new NotCreatedInDatabase("Le jobs n'a pas été crée en base de donné");
            }

            return (int)$this->pdb->lastInsertId();
        } catch (PDOException){
            throw new DatabaseException("Erreur lors de l'authentification");
        }
    }

    /**
     * Crée un lien en base de données entre un job et un technicien
     * via la table de jointure tech_jobs (relation N-N).
     *
     * @param int $Pk_Job
     * @param int $Pk_Tech
     * @return void
     * @throws DatabaseException
     */
    public function linkToTech(int $Pk_Job, int $Pk_Tech) : void {
        try{
            $query = $this->pdb->prepare("INSERT INTO tech_jobs (Fk_Job, Fk_Tech) VALUES (?, ?)");
            $query->execute([$Pk_Job, $Pk_Tech]);
        } catch (PDOException){
            throw new DatabaseException("Erreur lors de l'insertion dans la table tech_fonction");
        }
    }

    /**
     * Supprime tous les liens existants entre un job et ses techniciens
     * dans la table de jointure tech_jobs.
     * Utilisé notamment avant une suppression ou une réaffectation complète.
     *
     * @param int $Pk_Jobs
     * @return void
     * @throws DatabaseException
     */
    public function unlinkAllTech(int $Pk_Jobs) : void {
        try{
            $query = "DELETE FROM tech_jobs WHERE Fk_Job = ?";
            $query = $this->pdb->prepare($query);
            $query->execute([$Pk_Jobs]);
        } catch (PDOException){
            throw new DatabaseException("Erreur lors de la suppression des fonctions du technicien");
        }
    }

    /**
     * Met à jour les informations d'un job existant en base de données.
     * Vérifie d'abord que le job existe via sa PK avant d'effectuer l'UPDATE.
     * Lance une NotFoundException si le job est introuvable.
     *
     * @param JobEntity $entity
     * @return void
     * @throws DatabaseException
     * @throws NotFoundException
     */
    public function update(JobEntity $entity) : void {

        try {
            $check = $this->pdb->prepare("SELECT 1 FROM job WHERE Pk_Job = ?");
            $check->execute([$entity->getPk()]);

            if ($check->rowCount() === 0) {
                throw new NotFoundException("Tech introuvable");
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
        } catch (PDOException) {
            throw new DatabaseException("Erreur lors de l'update");
        }
    }

    /**
     * Supprime un job de la base de données via sa PK.
     * Vérifie d'abord via checkLink() qu'aucun technicien n'est lié à ce job.
     * Si un lien existe, la suppression est bloquée (LinkExistBetween).
     * Lance une NotFoundException si le job n'existe pas au moment du DELETE.
     *
     * @param int $pk
     * @return void
     * @throws DatabaseException
     * @throws LinkExistBetween
     * @throws NotFoundException
     */
    public function delete(int $pk) : void {
        try {
            $this->checkLink($pk);

            $query = $this->pdb->prepare("DELETE FROM job WHERE Pk_Job = ?");
            $query->execute([$pk]);

            if ($query->rowCount() === 0) {
                throw new NotFoundException("Le job n'existe pas");
            }
        } catch (PDOException) {
            throw new DatabaseException("Erreur lors de la suppression");
        }
    }

    /**
     * Vérifie si un lien existe entre un job et au moins un technicien
     * dans la table de jointure tech_jobs.
     * Lance une LinkExistBetween si un lien est détecté,
     * ce qui empêche par exemple la suppression du job.
     *
     * @param int $pk
     * @return void
     * @throws DatabaseException
     * @throws LinkExistBetween
     */
    public function checkLink(int $pk) : void {
        try {
            $query = $this->pdb->prepare("SELECT COUNT(*) FROM tech_jobs WHERE Fk_Job = ?");
            $query->execute([$pk]);

            if ($query->fetchColumn() > 0) {
                throw new LinkExistBetween("Un lien existe entre le jobs et un technicien");
            }
        } catch (PDOException) {
            throw new DatabaseException("Le lien n'a pas pu être vérifié");
        }
    }

    /**
     * Récupère l'identifiant textuel (Ident) d'un projet depuis sa clé primaire.
     * Utilisé pour afficher le nom/identifiant du projet associé à un job.
     *
     * @param int $id
     * @return string
     * @throws DatabaseException
     */
    public function getIdentById(int $id) : string {
        try{
            $query = $this->pdb->prepare("SELECT Ident FROM project WHERE Pk_Project = ?");
            $query->execute([$id]);

            return $query->fetchColumn();
        } catch (PDOException){
            throw new DatabaseException("Erreur lors de la lecture de l'identifiant du projet");
        }
    }

    /**
     * Récupère la clé primaire (PK) d'un projet depuis son identifiant textuel (Ident).
     * Utilisé pour faire la correspondance entre un Ident lisible et son ID en base.
     *
     * @param string $ident
     * @return int
     * @throws DatabaseException
     */
    public function getIdByIdent(string $ident) : int {
        try{
            $query = $this->pdb->prepare("SELECT Pk_Project FROM project WHERE Ident = ?");
            $query->execute([$ident]);

            return $query->fetchColumn();
        } catch (PDOException){
            throw new DatabaseException("Erreur lors de la lecture de l'identifiant du projet");
        }
    }
}