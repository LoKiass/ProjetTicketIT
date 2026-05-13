<?php

namespace DISEUMAT\Model\Service\Manager;

use DISEUMAT\Exception\DatabaseException;
use DISEUMAT\Exception\LinkExistBetween;
use DISEUMAT\Exception\NotCreatedInDatabase;
use DISEUMAT\Exception\NotFoundException;
use DISEUMAT\Model\Entity\FonctionEntity;

use PDOException;

class FonctionManager
{
    private $pdb;
    public function __construct(){
        $this->pdb = DBManager::getInstance();
    }
    /*
     * Cette fonction permet de crée une fonction dans la BDD
     */
    public function create(FonctionEntity $entity) : void {
        try{
            $query = $this->pdb->prepare("INSERT INTO fonction (Descr, Niveau) VALUES (?, ?)");
            $query->execute([$entity->getDescr(), $entity->getNiveau()]);

            if($query->rowCount() === 0){
               throw new NotCreatedInDatabase("L'enregistrement a pas été crée, veuillez re-essayer");
            }
        } catch (PDOException $e){
            throw new DatabaseException("Erreur lors de l'authentification", 0);
        }
    }
    /*
     * Cette fonction permet de modifier les informations d'une fonction déjà existante en BDD
     */
    public function update(FonctionEntity $FonctionEntity) : void {
        try{
            $query = $this->pdb->prepare("UPDATE fonction SET Descr = ?, Niveau = ? WHERE Pk_Fonction = ?");
            $query->execute([$FonctionEntity->getDescr(), $FonctionEntity->getNiveau(), $FonctionEntity->getPk()]);

            if($query->rowCount() === 0){
                throw new NotFoundException("La fonction n'existe pas");
            }
        } catch (PDOException $e){
            throw new DatabaseException("Erreur lors de l'authentification", 0);
        }
    }

    /*
     * Cette methode permet de lire toutes les fonctions de la BDD
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
                throw new NotFoundException("La liste est vide ou aucune fonction n'existe");
            }

            return $TabFunction;
        } catch (PDOException $e) {
            throw new DatabaseException("Erreur lors de l'authentification", 0);
        }
    }
    /*
     * Cette methode permet de lire une fonction de la BDD
     */
    public function read(int $pk) : FonctionEntity {
        try{
            $query = $this->pdb->prepare("SELECT * FROM fonction WHERE Pk_Fonction = ?");
            $query->execute([$pk]);

            if($query->rowCount() === 0){
                throw new NotFoundException("La fonction n'existe pas");
            }

            return FonctionEntity::fromArray($query->fetch());

        } catch (PDOException $e){
            throw new DatabaseException("Erreur lors de l'authentification", 0);
        }
    }

    /*
     * Cette méthode permet de supprimer une fonction de la BD si aucun liens n'existe entre elle et un technicien (Via checklink)
     */
    public function delete(int $pk) : void {
        try {
            $this->checkLink($pk);

            $query = $this->pdb->prepare("DELETE FROM fonction WHERE Pk_Fonction = ?");
            $query->execute([$pk]);

            if ($query->rowCount() === 0) {
                throw new NotFoundException("La fonction n'existe pas");
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
            $query = $this->pdb->prepare("SELECT COUNT(*) FROM fonction_tech WHERE Fk_Fonction = ?");
            $query->execute([$pk]);

            if ($query->fetchColumn() > 0) {
                throw new LinkExistBetween("Un lien existe entre la fonction et un technicien");
            }
        } catch (PDOException $e) {
            throw new DatabaseException("Le lien n'a pas pu être vérifié", 0, $e);
        }
    }

    public function listByTech(int $Pk): array
    {
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
    }
}