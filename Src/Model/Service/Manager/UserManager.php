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
 * Manager responsable de toutes les opérations en base de données
 * liées aux utilisateurs (comptes de connexion) du système.
 */
class UserManager
{
    /**
     * @var PDO
     */
    private PDO $pdb;

    /**
     * @var string Clé secrète utilisée pour le chiffrement AES des mots de passe en BDD.
     */
    private string $keyString;

    /**
     * Initialise la connexion à la base de données via le singleton DBManager
     * et définit la clé de chiffrement AES pour les mots de passe.
     */
    public function __construct(){
        $this->pdb = DBManager::getInstance();
        $this->keyString = 'CG6eeGK0jaKUU2U7';
    }

    /**
     * Vérifie si les identifiants fournis (login + mot de passe) correspondent
     * à un utilisateur valide en base de données.
     * Le mot de passe est comparé après chiffrement AES côté SQL.
     * Lance une InvalidCredentialException si aucun utilisateur ne correspond.
     *
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

            $query = $this->pdb->prepare("SELECT * FROM user WHERE Login = ? AND Pswd = AES_ENCRYPT(?, ?)");
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

    /**
     * Récupère la liste complète de tous les utilisateurs présents en base de données.
     * Retourne un tableau vide si aucun utilisateur n'existe.
     *
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

    /**
     * Récupère un utilisateur spécifique depuis la base de données via son login.
     * Lance une NotFoundException si aucun utilisateur ne correspond au login fourni.
     *
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

    /**
     * Met à jour le mot de passe d'un utilisateur existant en base de données.
     * Le nouveau mot de passe est chiffré via AES_ENCRYPT avant stockage.
     * Lance une NotFoundException si aucune ligne n'a été modifiée
     * (login inexistant ou mot de passe identique à l'ancien).
     *
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

    /**
     * Insère un nouvel utilisateur en base de données à partir d'un UserEntity.
     * Le mot de passe est automatiquement chiffré via AES_ENCRYPT lors de l'insertion.
     *
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