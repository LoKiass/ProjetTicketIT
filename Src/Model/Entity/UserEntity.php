<?php

namespace DISEUMAT\Model\Entity;
use DISEUMAT\Exception\MissingInformation;

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
        return $this->Statut;
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

    public static function fromArray(array $data) : UserEntity
    {
        try{
            $instance = new self();

            $instance->setLogin($data['Login']);
            $instance->setPswd($data['Pswd']);
            $instance->setStatut($data['Statut']);
            $instance->setActif((bool)($data['Actif']));

            return $instance;
        } catch (\Throwable $th){
            throw new MissingInformation("Des informations sont manquantes");
        }
    }

}