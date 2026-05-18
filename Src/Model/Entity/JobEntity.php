<?php

namespace DISEUMAT\Model\Entity;

class JobEntity
{
    private int $pk;
    private int $Fk_project;
    private int $Parent;
    private string $Titre;
    private string $Dscr;
    private string $Dstart;
    private string $Dech;
    private string $Dclot;
    // private array $Project;
    private array $Techs;
    // private jobs $Parent;

    public function __construct(){
        $this->pk = 0;
        $this->Fk_project = 0;
        $this->Parent = 0;
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
    public function getParent(): int{
        return $this->Parent;
    }
    public function getTitre(): string{
        return $this->Titre;
    }
    public function getDscr(): string{
        return $this->Dscr;
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
    public function getTechs(): array{
        return $this->Techs;
    }
    public function setPk(int $Pk){
        $this->pk = $Pk;
    }
    public function setFk_project(int $Fk_project){
        $this->Fk_project = $Fk_project;
    }
    public function setParent(int $Parent){
        $this->Parent = $Parent;
    }
    public function setTitre(string $Titre){
        $this->Titre = $Titre;
    }
    public function setDscr(string $Dscr){
        $this->Dscr = $Dscr;
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
    public function addTech(TechEntity $Tech){
        $this->Techs[] = $Tech;
    }

}