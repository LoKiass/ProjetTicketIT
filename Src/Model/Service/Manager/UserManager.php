<?php

namespace DISEUMAT\Model\Service\Manager;

use DISEUMAT\Exception\DatabaseException;
use DISEUMAT\Exception\InvalidCredentialException;
use DISEUMAT\Exception\MissingInformation;
use DISEUMAT\Exception\NotFoundException;
use DISEUMAT\Model\Entity\UserEntity as UserEntity;
use PDO;
use PDOException;

/**
 *
 */
class UserManager
{
    /**
     * @var PDO
     */
    private PDO $pdb;
    /**
     * @var string
     */
    private string $keyString;

    /**
     *
     */
    public function __construct(){
        $this->pdb = DBManager::getInstance();
        $this->keyString = 'CG6eeGK0jaKUU2U7'; // Clé de chiffrement pour le mot de passe
    }

    /*
     * Cette méthode permet de vérifier si les credentials fournis sont valides en BDDà partir du login et du mot de passe
     */
    /**
     * @param UserEntity $entity
     * @return UserEntity
     * @throws DatabaseException
     * @throws InvalidCredentialException
     * @throws MissingInformation
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
        }
    }

    /*
     * Cette méthode permet de lire tous les users de la BDD
     */
    /**
     * @return array
     * @throws DatabaseException
     * @throws MissingInformation
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
    /**
     * @param string $Login
     * @return UserEntity
     * @throws DatabaseException
     * @throws MissingInformation
     * @throws NotFoundException
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
    /**
     * @param string $login
     * @param string $newPassword
     * @return void
     * @throws DatabaseException
     * @throws NotFoundException
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
        }
    }

    /*
     * Cette méthode permet de crée un utilisateurs en BDD
     */
    /**
     * @param UserEntity $entity
     * @return void
     * @throws DatabaseException
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