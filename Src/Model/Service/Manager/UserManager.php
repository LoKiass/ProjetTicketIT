<?php

namespace DISEUMAT\Model\Service\Manager;

use DISEUMAT\Exception\DatabaseException;
use DISEUMAT\Exception\InvalidCredentialException;
use DISEUMAT\Exception\MissingInformation;
use DISEUMAT\Exception\NotFoundException;
use DISEUMAT\Model\Entity\UserEntity as UserEntity;
use PDO;
use PDOException;

class UserManager
{
    private PDO $pdb;
    private string $keyString;

    public function __construct(){
        $this->pdb = DBManager::getInstance();
        $this->keyString = 'CG6eeGK0jaKUU2U7'; // Clé de chiffrement pour le mot de passe
    }

    /*
     * Cette méthode permet de vérifier si les credentials fournis sont valides en BDDà partir du login et du mot de passe
     */
    /**
     * @throws MissingInformation
     * @throws DatabaseException
     * @throws InvalidCredentialException
     */
    public function checkUser(UserEntity $entity) : UserEntity
    {
        try {
            $Login = $entity->getLogin();
            $Pswd = $entity->getPswd();

            $query = $this->pdb->prepare("SELECT * FROM User WHERE Login = ? AND Pswd = AES_ENCRYPT(?, ?)");
            $query->execute([$Login, $this->keyString ,$Pswd]);

            $data = $query->fetch();

            if(!$data){
                throw new InvalidCredentialException("Les informations fournites ne sont pas valides");
            }

            return UserEntity::fromArray($data);
        } catch (PDOException) {
            throw new DatabaseException("Erreur lors de l'authentification");
        } catch (MissingInformation) {
            throw new MissingInformation("Des informations sont manquantes");
        } catch (InvalidCredentialException) {
            throw new InvalidCredentialException("Les informations fournites ne sont pas valides");
        }
    }

    /*
     * Cette méthode permet de lire tous les users de la BDD
     */
        public function list() : array{
            try{
                $query = $this->pdb->prepare("SELECT * FROM user");
                $query->execute();

                $TabUser = array();

                while($record = $query->fetch()){
                    $tempUser = UserEntity::fromArray($record);
                    $TabUser[] = clone $tempUser;
                }

                if (empty($TabUser)){
                    return [];
                }
                return $TabUser;
            } catch (PDOException){
                throw new DatabaseException("Erreur lors de l'accès à la DB");
            }
        }

    /*
     * Cette méthode permet de lire un user de la BDD à partir du login fournit
     */
    public function read(string $Login) : UserEntity {
        try{
            $query = $this->pdb->prepare("SELECT * FROM user WHERE Login = ?");
            $query->execute([$Login]);

            $record = $query->fetch();

            if(!$record){
                throw new NotFoundException("Le user specifier est introuvable");
            }

            return UserEntity::fromArray($record);
        } catch (PDOException){
            throw new DatabaseException("Erreur de le l'accès à la DB");
        }
    }

    /*
     * Cette méthode permet de modifier le mots de passe de l'utilisateurs
     */
    public function updatePassword(string $login, string $newPassword) : void {
        try{
            $query = $this->pdb->prepare("UPDATE user SET Pswd = AES_ENCRYPT(?, ?) WHERE Login = ?");
            $query->execute([$this->keyString, $newPassword, $login]);

            if($query->rowCount() === 0){
                throw new NotFoundException("Le user specifier aura subit aucune modification");
            }

        } catch (PDOException){
            throw new DatabaseException("Impossible de mettre à jours le mots de passe");
        } catch (NotFoundException){
            throw new NotFoundException("Le user specifier n'existe pas");
        }
    }

    /*
     * Cette méthode permet de crée un utilisateurs en BDD
     */
    public function create(UserEntity $entity) : void {
        try{
            $query = $this->pdb->prepare("INSERT INTO user (Login, Pswd, Actif, Statut) VALUES (?, AES_ENCRYPT(?, ?), ?, ?)");
            $query->execute([$entity->getLogin(), $this->keyString, $entity->getPswd(), $entity->getActif(), $entity->getStatut()]);
        } catch (PDOException){
            throw new DatabaseException("Erreur lors de l'insertion dans la table user");
        }
    }

}