<?php

namespace DISEUMAT\Model\Service\Manager;

use DISEUMAT\Exception\DatabaseException;
use DISEUMAT\Exception\NotFoundException;
use DISEUMAT\Model\Entity\UserEntity as UserEntity;
use PDOException;

class UserManager
{
    private $pdb;

    public function __construct(){
        $this->pdb = DBManager::getInstance();
    }

    public function checkUser(UserEntity $entity) : UserEntity
    {
        try {
            $Login = $entity->getLogin();
            $Pswd = $entity->getPswd();

            $query = $this->pdb->prepare("SELECT * FROM User WHERE Login = ? AND Pswd = ?");
            $query->execute([$Login, $Pswd]);

            $data = $query->fetch(\PDO::FETCH_ASSOC);

            if(!$data){
                throw new NotFoundException("Ressource non trouver", 0);
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
        $query = $this->pdb->prepare("SELECT * FROM user");
        $query->execute();

        $TabUser = array();

        while($record = $query->fetch()){
            $tempUser = UserEntity::fromArray($record);
            $TabUser[] = clone $tempUser;
        }
        return $TabUser;
    }
    public function read(string $Login){
        $query = $this->pdb->prepare("SELECT * FROM user WHERE Login = ?");
        $query->execute([$Login]);

        $record = $query->fetch();

        print_r($record);
        return UserEntity::fromArray($record);
    }

    public function updatePassword(string $login, string $newPassword) : void
    {
        $query = $this->pdb->prepare("UPDATE user SET Pswd = ? WHERE Login = ?");
        $query->execute([$newPassword, $login]);
    }

}