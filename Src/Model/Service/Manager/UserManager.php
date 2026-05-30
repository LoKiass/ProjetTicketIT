<?php

namespace DISEUMAT\Model\Service\Manager;

use DISEUMAT\Exception\DatabaseException;
use DISEUMAT\Exception\InvalidCredentialException;
use DISEUMAT\Exception\NotFoundException;
use DISEUMAT\Model\Entity\UserEntity as UserEntity;
use PDOException;

class UserManager
{
    private $pdb;
    private $keyString;

    public function __construct(){
        $this->pdb = DBManager::getInstance();
        $this->keyString = 'CG6eeGK0jaKUU2U7';
    }

    public function checkUser(UserEntity $entity) : UserEntity
    {
        try {
            $Login = $entity->getLogin();
            $Pswd = $entity->getPswd();

            $query = $this->pdb->prepare("SELECT * FROM User WHERE Login = ? AND Pswd = AES_ENCRYPT(?, ?)");
            $query->execute([$Login, $this->keyString ,$Pswd]);

            $data = $query->fetch(\PDO::FETCH_ASSOC);

            if(!$data){
                throw new InvalidCredentialException("Les informations fournites ne sont pas valides", 0);
            }

            $userFound = new UserEntity();

            $userFound->setLogin($data['Login']);
            $userFound->setPswd($data['Pswd']);
            $userFound->setActif($data['Actif']);
            $userFound->setStatut($data['Statut']);

            return $userFound;
        } catch (PDOException $e) {
            throw new DatabaseException("Erreur lors de l'authentification", 0);
        }
    }

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
            } catch (PDOException $e){
                throw new DatabaseException("Erreur lors de l'accès à la DB", 0);
            }
        }
    public function read(string $Login) : UserEntity {
        try{
            $query = $this->pdb->prepare("SELECT * FROM user WHERE Login = ?");
            $query->execute([$Login]);

            $record = $query->fetch();

            if(!$record){
                throw new NotFoundException("Le user specifier est introuvable", 0);
            }

            return UserEntity::fromArray($record);
        } catch (PDOException $e){
            throw new DatabaseException("Erreur de le l'accès à la DB", 0);
        }
    }

    public function updatePassword(string $login, string $newPassword) : void {
        try{
            $query = $this->pdb->prepare("UPDATE user SET Pswd = AES_ENCRYPT(?, ?) WHERE Login = ?");
            $query->execute([$this->keyString, $newPassword, $login]);

            if($query->rowCount() === 0){
                throw new NotFoundException("Le user specifier aura subit aucune modification", 0);
            }

        } catch (PDOException $e){
            throw new DatabaseException("Impossible de mettre à jours le mots de passe", 0);
        }
    }

    public function create(UserEntity $entity) : void {
        try{
            $query = $this->pdb->prepare("INSERT INTO user (Login, Pswd, Actif, Statut) VALUES (?, AES_ENCRYPT(?, ?), ?, ?)");
            $query->execute([$entity->getLogin(), $this->keyString, $entity->getPswd(), $entity->getActif(), $entity->getStatut()]);
        } catch (PDOException $e){
            throw new DatabaseException("Erreur lors de l'insertion dans la table user");
        }
    }

}