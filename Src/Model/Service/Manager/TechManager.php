<?php

namespace DISEUMAT\Model\Service\Manager;
use DISEUMAT\Exception\DatabaseException;
use DISEUMAT\Exception\MissingInformation;
use DISEUMAT\Exception\NotCreatedInDatabase;
use DISEUMAT\Exception\NotFoundException;
use DISEUMAT\Model\Entity\TechEntity;
use PDO;
use PDOException;

/**
 * Manager responsable de toutes les opérations en base de données
 * liées aux techniciens du système.
 */
class TechManager
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
     * Récupère un technicien spécifique depuis la base de données via sa clé primaire.
     * Lance une NotFoundException si aucun technicien ne correspond à l'ID fourni.
     *
     * @param int $id
     * @return TechEntity
     * @throws DatabaseException
     * @throws MissingInformation
     * @throws NotFoundException
     */
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

        } catch (PDOException){
            throw new DatabaseException("Erreur lors de l'authentifications à la DB");
        } catch (MissingInformation){
            throw new MissingInformation("Des informations sont manquantes");
        }

    }

    /**
     * Récupère la liste complète de tous les techniciens présents en base de données.
     * Retourne un tableau vide si aucun technicien n'existe.
     *
     * @return array
     * @throws DatabaseException
     * @throws MissingInformation
     */
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
                return [];
            }

            return $TabTech;
        }catch (PDOException){
            throw new DatabaseException("Erreur lors de l'authentification");
        }
    }

    /**
     * Insère un nouveau technicien en base de données à partir d'une entité.
     * Retourne la clé primaire (PK) du technicien nouvellement créé.
     * Lance une NotCreatedInDatabase si aucune ligne n'a été insérée.
     *
     * @param $entity
     * @return int
     * @throws DatabaseException
     * @throws NotCreatedInDatabase
     */
    public function create($entity) : int {
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
                throw new NotCreatedInDatabase("Le techniciens n'a pas été crée");
            }

            return (int)$this->pdb->lastInsertId();
        } catch (PDOException){
            throw new DatabaseException();
        }
    }

    /**
     * Met à jour les informations d'un technicien existant en base de données.
     * Vérifie d'abord que le technicien existe via sa PK avant d'effectuer l'UPDATE.
     *
     * @param TechEntity $entity
     * @return void
     * @throws DatabaseException
     */
    public function update(TechEntity $entity) : void {
        try {
            $check = $this->pdb->prepare("SELECT 1 FROM tech WHERE Pk_Tech = ?");
            $check->execute([$entity->getPk()]);

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
        } catch (PDOException) {
            throw new DatabaseException("Erreur lors de l'authentification");
        }
    }

    /**
     * Crée un lien en base de données entre un technicien et une fonction
     * via la table de jointure fonction_tech (relation N-N).
     * Utilise INSERT IGNORE pour éviter les doublons silencieusement.
     *
     * @param int $Pk_Tech
     * @param int $Pk_Fonction
     * @return void
     * @throws DatabaseException
     */
    public function LinkToFunction(int $Pk_Tech, int $Pk_Fonction) : void {
        try{
            $query = "INSERT IGNORE INTO fonction_tech (Fk_Tech, Fk_Fonction) VALUES (?, ?)";
            $query = $this->pdb->prepare($query);
            $query->execute([$Pk_Tech, $Pk_Fonction]);

        } catch (PDOException){
            throw new DatabaseException("Erreur lors de l'insertion dans la table tech_fonction");
        }
    }

    /**
     * Supprime tous les liens existants entre un technicien et ses fonctions
     * dans la table de jointure fonction_tech.
     * Utilisé notamment avant une réaffectation complète des fonctions d'un technicien.
     *
     * @param int $Pk_Tech
     * @return void
     * @throws DatabaseException
     */
    public function unlinkAllFunctions(int $Pk_Tech) : void {
        try{
            $query = "DELETE FROM fonction_tech WHERE Fk_Tech = ?";
            $query = $this->pdb->prepare($query);
            $query->execute([$Pk_Tech]);
        } catch (PDOException){
            throw new DatabaseException("Erreur lors de la suppression des fonctions du technicien");
        }
    }

    /**
     * Récupère la liste de tous les techniciens affectés à un job spécifique,
     * via la table de jointure tech_jobs.
     * Retourne un tableau vide si aucun technicien n'est lié à ce job.
     *
     * @throws MissingInformation
     * @throws DatabaseException
     */
    public function listByJob(int $Pk): array
    {
        try{
            $statement = "SELECT t.* 
            FROM tech t
            INNER JOIN tech_jobs ft ON t.Pk_Tech = ft.fk_tech
            WHERE ft.Fk_Job = ?";

            $query = $this->pdb->prepare($statement);
            $query->execute([$Pk]);

            $TabTech = array();

            while($record = $query->fetch()){
                $TabTech[] = TechEntity::fromArray($record);
            }
            return $TabTech;
        } catch (PDOException){
            throw new DatabaseException("Erreur lors de l'authentifications");
        } catch (MissingInformation){
            throw new MissingInformation("Des informations sont manquantes");
        }
    }
}