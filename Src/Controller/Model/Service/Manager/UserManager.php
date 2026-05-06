<?php

namespace DISEUMAT\Controller\Model\Service\Manager;

class UserManager
{
    private $pdb;

    public function __construct(){
        $this->pdb = DBManager::getInstance();
    }

    public function checkUser($Login, $Pswd) : bool{
        $query = "SELECT * FROM User WHERE Login = '$Login' AND Pswd = '$Pswd'";
        $checkRetour = $this->pdb->query($query);
        if ($checkRetour->rowCount() == 1){
            return true;
        }
        return false;
    }


}