<?php

namespace DISEUMAT\Model\Entity;

class FonctionEntity
{
    private int $Pk;
    private string $Descr;
    private string $Niveau;
    private array $Techs;
    public function __construct(){
        $this->Pk = 0;
        $this->Descr = "";
        $this->Niveau = "";
        $this->Techs = array();
    }

    public function getPk(): int{
        return $this->Pk;
    }
    public function getDescr(): string{
        return $this->Descr;
    }
    public function getNiveau(): string{
        return $this->Niveau;
    }
    public function getTechs(): array{
        return $this->Techs;
    }

    public function setPk(int $Pk){
        $this->Pk = $Pk;
    }
    public function setDescr(string $Descr){
        $this->Descr = $Descr;
    }
    public function setNiveau(string $Niveau){
        $this->Niveau = $Niveau;
    }
    public function setTechs(array $Techs): void { // Debut liste (toutes les fonctions)
        $this->Techs = $Techs;
    }
}