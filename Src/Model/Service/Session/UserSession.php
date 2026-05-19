<?php

namespace DISEUMAT\Model\Service\Session;

use DISEUMAT\Model\Entity\UserEntity;

class UserSession
{

    public function create($entity)
    {
        $_SESSION['userLogged'] = serialize($entity);
    }

    public function delete(? int $id)
    {
        session_unset();
    }

    /**
     * @param int|null $id
     * @return UserEntity|null
     */
    public function read(? int $id) : ?UserEntity
    {
        if(isset($_SESSION['userLogged'])) return unserialize($_SESSION['userLogged']);
        else return null;

        //return isset($_SESSION['UserC']) ? unserialize($_SESSION['UserC']) : null;
    }
}