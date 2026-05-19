<?php

namespace DISEUMAT\Model\Entity;

use DISEUMAT\Exception\MissingInformation;

class TechEntity
{
    private int $Pk;
    private int $Fk_equipe;
    private string $Nom;
    private string $Prenom;
    private string $Email;
    private bool $Actif;
    private array $Fonction;
    public function __construct()
    {
        $this->Pk = 0;
        $this->Fk_equipe = 0;
        $this->Nom = "";
        $this->Prenom = "";
        $this->Email = "";
        $this->Actif = false;
        $this->Fonction = array();
    }

    // Getter & setter
    public function getPk(): int {
        return $this->Pk; //
    }
    public function getFk_equipe(): int {
        return $this->Fk_equipe;
    }
    public function getNom(): string {
        return $this->Nom;
    }
    public function getPrenom(): string {
        return $this->Prenom;
    }
    public function getEmail(): string {
        return $this->Email;
    }
    public function getActif(): bool{
        return $this->Actif;
    }
    public function setPk(int $Pk) : void {
        $this->Pk = $Pk;
    }
    public function setFk_equipe(int $Fk_equipe) : void{
        $this->Fk_equipe = $Fk_equipe;
    }
    public function setNom(string $Nom) : void {
        $this->Nom = $Nom;
    }
    public function setPrenom(string $Prenom) : void {
        $this->Prenom = $Prenom;
    }
    public function setEmail(string $Email) : void{
        $this->Email = $Email;
    }
    public function setActif(bool $Actif) : void
    {
        $this->Actif = $Actif;
    }
    public function setFonctions(array $fonctions): void {
        $this->Fonction = $fonctions;
    }

    public function getFonctions(): array {
        return $this->Fonction;
    }
    public static function fromArray(array $data) : TechEntity
    {
        try{
            $instance = new self();

            $instance->setPk($data['Pk_Tech'] ?? null);
            $instance->setNom($data['Nom']);
            $instance->setPrenom($data['Pren']);
            $instance->setEmail($data['Email']);
            $instance->setActif((bool)($data['Actif']));

            return $instance;
        }catch (\Throwable){
            throw new MissingInformation("Des informations sont manquantes");
        }
    }
}