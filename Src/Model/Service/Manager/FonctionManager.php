<?php

namespace DISEUMAT\Model\Service\Manager;

use DISEUMAT\Exception\DatabaseException;
use DISEUMAT\Exception\LinkExistBetween;
use DISEUMAT\Exception\MissingInformation;
use DISEUMAT\Exception\NotCreatedInDatabase;
use DISEUMAT\Exception\NotFoundException;
use DISEUMAT\Model\Entity\FonctionEntity;

use PDO;
use PDOException;

/**
 * Manager responsable de toutes les opérations en base de données
 * liées aux fonctions (rôles/compétences) attribuables aux techniciens.
 */
class FonctionManager
{
    /**
     * @var PDO Instance de connexion à la base de données.
     */
    private PDO $pdb;

    /**
     * Initialise la connexion à la base de données via le singleton DBManager.
     */
    public function __construct(){
        $this->pdb = DBManager::getInstance();
    }

    /**
     * Insère une nouvelle fonction en base de données à partir d'un FonctionEntity.
     * Lance une NotCreatedInDatabase si aucune ligne n'a été insérée.
     *
     * @param FonctionEntity $entity
     * @return void
     * @throws DatabaseException
     * @throws NotCreatedInDatabase
     */
    public function create(FonctionEntity $entity) : void {
        try{
            $query = $this->pdb->prepare("INSERT INTO fonction (Descr, Niveau) VALUES (?, ?)");
            $query->execute([$entity->getDescr(), $entity->getNiveau()]);

            if($query->rowCount() === 0){
                throw new NotCreatedInDatabase("L'enregistrement a pas été crée, veuillez re-essayer");
            }
        } catch (PDOException){
            throw new DatabaseException("Erreur lors de l'authentification");
        }
    }

    /**
     * Met à jour la description et le niveau d'une fonction existante en base de données.
     * Lance une NotFoundException si aucune ligne n'a été modifiée (fonction inexistante).
     *
     * @param FonctionEntity $FonctionEntity
     * @return void
     * @throws DatabaseException
     * @throws NotFoundException
     */
    public function update(FonctionEntity $FonctionEntity) : void {
        try{
            $query = $this->pdb->prepare("UPDATE fonction SET Descr = ?, Niveau = ? WHERE Pk_Fonction = ?");
            $query->execute([$FonctionEntity->getDescr(), $FonctionEntity->getNiveau(), $FonctionEntity->getPk()]);

            if($query->rowCount() === 0){
                throw new NotFoundException("La fonction n'existe pas");
            }
        } catch (PDOException){
            throw new DatabaseException("Erreur lors de l'authentification");
        }
    }

    /**
     * Récupère la liste complète de toutes les fonctions présentes en base de données.
     * Retourne un tableau vide si aucune fonction n'existe.
     *
     * @return array
     * @throws DatabaseException|MissingInformation
     */
    public function list() : array
    {
        try {
            $query = $this->pdb->prepare("SELECT * FROM fonction");
            $query->execute();

            $TabFunction = array();
            while ($record = $query->fetch()) {
                $tempFunction = FonctionEntity::fromArray($record);
                $TabFunction[] = clone $tempFunction;
            }

            if (empty($TabFunction)) {
                return [];
            }

            return $TabFunction;
        } catch (PDOException) {
            throw new DatabaseException("Erreur lors de l'authentification");
        }
    }

    /**
     * Récupère une fonction spécifique depuis la base de données via sa clé primaire.
     * Lance une NotFoundException si aucune fonction ne correspond à la PK fournie.
     *
     * @param int $pk
     * @return FonctionEntity
     * @throws DatabaseException
     * @throws NotFoundException|MissingInformation
     */
    public function read(int $pk) : FonctionEntity {
        try{
            $query = $this->pdb->prepare("SELECT * FROM fonction WHERE Pk_Fonction = ?");
            $query->execute([$pk]);

            if($query->rowCount() === 0){
                throw new NotFoundException("La fonction n'existe pas");
            }

            return FonctionEntity::fromArray($query->fetch());

        } catch (PDOException){
            throw new DatabaseException("Erreur lors de l'authentification");
        }
    }

    /**
     * Supprime une fonction de la base de données via sa clé primaire.
     * Vérifie d'abord via checkLink() qu'aucun technicien n'est lié à cette fonction.
     * Si un lien existe, la suppression est bloquée (LinkExistBetween).
     * Lance une NotFoundException si la fonction n'existe pas au moment du DELETE.
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

            $query = $this->pdb->prepare("DELETE FROM fonction WHERE Pk_Fonction = ?");
            $query->execute([$pk]);

            if ($query->rowCount() === 0) {
                throw new NotFoundException("La fonction n'existe pas");
            }
        } catch (PDOException) {
            throw new DatabaseException("Erreur lors de la suppression");
        } catch (LinkExistBetween) {
            throw new LinkExistBetween("Un lien exite entre la fonction et un technicien, impossible de supprimer");
        }
    }

    /**
     * Vérifie si un lien existe entre une fonction et au moins un technicien
     * dans la table de jointure fonction_tech.
     * Lance une LinkExistBetween si un lien est détecté — le message sera
     * surchargé par la méthode appelante (ex: delete()).
     *
     * @param int $pk
     * @return void
     * @throws DatabaseException
     * @throws LinkExistBetween
     */
    public function checkLink(int $pk) : void {
        try {
            $query = $this->pdb->prepare("SELECT COUNT(*) FROM fonction_tech WHERE Fk_Fonction = ?");
            $query->execute([$pk]);

            if ($query->fetchColumn() > 0) {
                throw new LinkExistBetween("Le message vas ce faire overide par la la méthode qui l'appelle");
            }
        } catch (PDOException) {
            throw new DatabaseException("Le lien n'a pas pu être vérifié");
        }
    }

    /**
     * Récupère la liste de toutes les fonctions attribuées à un technicien spécifique,
     * via la table de jointure fonction_tech.
     * Retourne un tableau vide si aucune fonction n'est liée à ce technicien.
     *
     * @param int $Pk
     * @return array
     * @throws DatabaseException
     * @throws MissingInformation
     */
    public function listByTech(int $Pk): array
    {
        try{
            $statement = "SELECT f.* 
            FROM fonction f
            INNER JOIN fonction_tech ft ON f.Pk_Fonction = ft.Fk_Fonction
            WHERE ft.Fk_Tech = ?";

            $query = $this->pdb->prepare($statement);
            $query->execute([$Pk]);

            $TabFonction = array();

            while($record = $query->fetch()){
                $TabFonction[] = FonctionEntity::fromArray($record);
            }
            return $TabFonction;
        } catch (PDOException){
            throw new DatabaseException("Erreur lors de l'authentifications");
        } catch (MissingInformation) {
            throw new MissingInformation("Des informations sont manquantes");
        }

    }
}