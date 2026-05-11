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


}