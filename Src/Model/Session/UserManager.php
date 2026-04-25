<?php

namespace DISEUMAT\Model\Session;

class UserManager
{
    private $pdb;

    public function __construct(){
        $this->pdb = DBManager::getInstance();
    }

    public function checkUser($Login, $Pswd) : bool{
        $query = "SELECT * FROM user WHERE login = '$Login' AND pswd = '$Pswd'";
        $checkRetour = $this->pdb->query($query);
        if ($checkRetour->rowCount() == 1){
            return true;
        }
        return false;
    }


}