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

    public function delete(int $pk) : void {
        try{
            $this->checkLink($pk); // Verifier l'existence d'un lien entre la fonction et un technicien

            $query = $this->pdb->prepare("DELETE FROM fonction WHERE Pk_Fonction = ?");
            $query->execute([$pk]);
            if($query->rowCount() === 0){
                throw new NotFoundException("La fonction n'existe pas");
            }

        } catch (PDOException $e){
            throw new DatabaseException("Erreur lors de l'authentification", 0);
        }
    }
    public function checkLink(int $pk) : void{
        try{
            $query = $this->pdb->prepare("
            SELECT COUNT(*) FROM fonction_tech WHERE Fk_Fonction = ? "
            );

            $query->execute([$pk]);
            if($query->fetchColumn() > 0){
                throw new LinkExistBetween("Un liens existe entre la fonction et un téchniciens");
            }
        } catch (PDOException $e){
            throw new DatabaseException("Le liens n'a pas pue être verifier", 0);
        }
    }
}