<?php

namespace DISEUMAT\Controller\Model\Entity;

class UserEntity
{
    private string $Login;
    private string $Pswd;
    private string $Statut;
    private bool $Actif;

    public function __construct(){
        $this->Login = "";
        $this->Pswd = "";
        $this->Statut = "";
        $this->Actif = false;
    }
    // Getter
    public function getLogin(): string{
        return $this->Login;
    }
    public function getPswd(): string{
        return $this->Pswd;
    }
    public function getStatut(): string{
        return $this->Staus;
    }
    public function getActif(): bool{
        return $this->Actif;
    }

    // Setter
    public function setLogin(string $Login){
        $this->Login = $Login;
    }
    public function setPswd(string $Pswd){
        $this->Pswd = $Pswd;
    }
    public function setStatut(string $Statut){
        $this->Statut = $Statut;
    }
    public function setActif(bool $Actif){
        $this->Actif = $Actif;
    }

}