<?php

namespace DISEUMAT\Controller\Model\Service\Manager;

use DISEUMAT\Controller\Model\Entity\UserEntity as UserEntity;

class UserManager
{
    private $pdb;

    public function __construct(){
        $this->pdb = DBManager::getInstance();
    }

    public function checkUser(UserEntity $entity) : UserEntity|bool
    {
        $Login = $entity->getLogin();
        $Pswd = $entity->getPswd();

        $query = $this->pdb->prepare("SELECT * FROM user WHERE Login = ? AND Pswd = ?");
        $query->execute([$Login, $Pswd]);

        $data = $query->fetch(\PDO::FETCH_ASSOC);

        if ($data) {
            $userFound = new UserEntity();

            $userFound->setLogin($data['Login']);
            $userFound->setPswd($data['Pswd']);
            $userFound->setActif($data['Actif']);
            $userFound->setStatut($data['Statut']);

            return $userFound;
        }

        return false;
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