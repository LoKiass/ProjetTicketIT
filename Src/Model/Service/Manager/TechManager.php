<?php

namespace DISEUMAT\Model\Service\Manager;
use DISEUMAT\Exception\DatabaseException;
use DISEUMAT\Exception\NotCreatedInDatabase;
use DISEUMAT\Exception\NotFoundException;
use DISEUMAT\Model\Entity\FonctionEntity;
use DISEUMAT\Model\Entity\TechEntity;

class TechManager
{
    private $pdb;
    public function __construct(){
        $this->pdb = DBManager::getInstance();
    }

    /*
     * Cette méthode permet de lire un technicien en fonction de son id en BDD
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

        } catch (\PDOException $e){
            throw new DatabaseException($e->getMessage(), 0);
        }

    }

    /*
     * Cette methode permet de lire tous les technicien de la BDD
     * Et de recuper leurs fonctions relatifs
     */
    public function list() : array {
        try {
            // Recuperer tous les technicien
            $sqlTech = <<< SQL
            SELECT Pk_Tech, Nom, Pren, Email, Actif
            FROM tech;
        SQL;
            $stmtTech = $this->pdb->prepare($sqlTech);
            $stmtTech->execute();

            // Recuperer les fonctions relatifs aux technicien
            $sqlFonctions = <<< SQL
            SELECT f.Pk_Fonction, f.Descr
            FROM fonction f
            INNER JOIN fonction_tech ft ON f.Pk_Fonction = ft.Fk_Fonction
            WHERE ft.Fk_Tech = ?; 
        SQL;
            $stmtFonctions = $this->pdb->prepare($sqlFonctions);

            $TabTech = array();
            while ($record = $stmtTech->fetch()) {
                $tempTech = TechEntity::fromArray($record);

                $stmtFonctions->execute([$record['Pk_Tech']]);
                $tabFonctionsTech = $stmtFonctions->fetchAll();

                $tempTech->setFonctions($tabFonctionsTech);

                $TabTech[] = $tempTech;
            }

            if (empty($TabTech)) {
                return [];
            }

            return $TabTech;

        } catch (\PDOException $e) {
            throw new DatabaseException($e->getMessage(), 0);
        }
    }

    /*
     * Cette méthode permet de créé un technicien en BDD
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
                throw new NotCreatedInDatabase("Le techniciens n'a pas été crée", 0);
           }

           return (int)$this->pdb->lastInsertId();
       } catch (\PDOException $e){
            throw new DatabaseException($e->getMessage(), 0);
       }
    }

    /*
     * Cette méthode permet de modifier un téchnciens déjà existant en BDD
     */
    public function update(TechEntity $entity) : void {
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
        } catch (\PDOException $e) {
            throw new DatabaseException($e->getMessage(), 0);
        }
    }
    public function LinkToFunction(int $Pk_Tech, int $Pk_Fonction) : void {
        try{
            $query = "INSERT IGNORE INTO fonction_tech (Fk_Tech, Fk_Fonction) VALUES (?, ?)";
            $query = $this->pdb->prepare($query);
            $query->execute([$Pk_Tech, $Pk_Fonction]);

        } catch (\PDOException $e){
            throw new DatabaseException("Erreur lors de l'insertion dans la table tech_fonction");
        }
    }
    public function unlinkAllFunctions(int $Pk_Tech) : void {
        try{
            $query = "DELETE FROM fonction_tech WHERE Fk_Tech = ?";
            $query = $this->pdb->prepare($query);
            $query->execute([$Pk_Tech]);
        } catch (\PDOException $e){
            throw new DatabaseException("Erreur lors de la suppression des fonctions du technicien");
        }
    }

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
        } catch (PDOException $e){
            throw new DatabaseException("Erreur lors de l'authentifications");
        }
    }
}