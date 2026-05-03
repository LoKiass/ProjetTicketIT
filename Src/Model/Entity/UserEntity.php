<?php

namespace DISEUMAT\Model\Entity;

class UserEntity
{
    private string $Login;
    private int $Pswd;
    private string $Staus;
    private bool $Actif;

    public function __construct(string $Login, int $Pswd, string $Staus, bool $Actif){
        $this->Login = $Login;
        $this->Pswd = $Pswd;
        $this->Staus = $Staus;
        $this->Actif = $Actif;
    }

    // Getter
    public function getLogin(): string{
        return $this->Login;
    }
    public function getPswd(): int{
        return $this->Pswd;
    }
    public function getStaus(): string{
        return $this->Staus;
    }
    public function getActif(): bool{
        return $this->Actif;
    }

    // Setter
    public function setLogin(string $Login){
        $this->Login = $Login;
    }
    public function setPswd(int $Pswd){
        $this->Pswd = $Pswd;
    }
    public function setStaus(string $Staus){
        $this->Staus = $Staus;
    }
    public function setActif(bool $Actif){
        $this->Actif = $Actif;
    }

}