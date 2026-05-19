<?php

namespace DISEUMAT\Model\Entity;

use DISEUMAT\Exception\MissingInformation;

class JobEntity
{
    private int $pk;
    private int $Fk_project;
    private string $Titre;
    private string $Dscr;
    private string $Status;
    private string $Prior;
    private string $Dstart;
    private string $Dech;
    private string $Dclot;
    // private array $Project;
    private array $Techs;
    // private jobs $Parent;

    public function __construct(){
        $this->pk = 0;
        $this->Fk_project = 0;
        $this->Titre = "";
        $this->Dscr = "";
        $this->Dstart = "";
        $this->Dech = "";
        $this->Dclot = "";
        $this->Techs = array();
    }
    public function getPk(): int{
        return $this->pk;
    }
    public function getFk_project(): int{
        return $this->Fk_project;
    }
    public function getTitre(): string{
        return $this->Titre;
    }
    public function getDscr(): string{
        return $this->Dscr;
    }
    public function getStatus(): string{
        return $this->Status;
    }
    public function getPrior(): string{
        return $this->Prior;
    }
    public function getDstart(): string{
        return $this->Dstart;
    }
    public function getDech(): string{
        return $this->Dech;
    }
    public function getDclot(): string{
        return $this->Dclot;
    }
    public function getTech(): array{
        return $this->Techs;
    }
    public function setPk(?int $Pk) : void{ // Dans le cas de la créations d'un job, il n'y a pas de PK
        if ($Pk == 0) return;
        $this->pk = $Pk;
    }
    public function setFk_project(int $Fk_project){
        $this->Fk_project = $Fk_project;
    }
    public function setTitre(string $Titre){
        $this->Titre = $Titre;
    }
    public function setDscr(string $Dscr){
        $this->Dscr = $Dscr;
    }
    public function setStatus(string $Status){
        $this->Status = $Status;
    }
    public function setPrior(string $Prior){
        $this->Prior = $Prior;
    }
    public function setDstart(string $Dstart){
        $this->Dstart = $Dstart;
    }
    public function setDech(string $Dech){
        $this->Dech = $Dech;
    }
    public function setDclot(string $Dclot){
        $this->Dclot = $Dclot;
    }
    public function setTech(array $Tech){
        $this->Techs = $Tech;
    }

    public static function fromArray(array $data) : JobEntity{
        try{
            $instance = new self();

            $instance->setPk($data['Pk_Job'] ?? null);
            $instance->setFk_project($data['Fk_Project']);
            $instance->setTitre($data['Titre']);
            $instance->setStatus($data['Status']);
            $instance->setPrior($data['Prior']);
            $instance->setDscr($data['Dscr']);
            $instance->setDstart($data['Dstart']);
            $instance->setDech($data['Dech']);
            $instance->setDclot($data['Dclot']);

            return $instance;
        } catch (\Throwable $e){
            throw new MissingInformation("Des informations sont manquantes !");
        }
    }

}