<?php

namespace DISEUMAT\Model\Entity;

use DISEUMAT\Exception\MissingInformation;

class ProjectEntity
{
    private int $Pk;
    private string $Ident;
    private string $Descr;
    private string $Dstart;
    private string $DClotEst;
    private int $budget;
    private array $Jobs;
    public function __construct(){
        $this->Pk = 0;
        $this->Ident = "";
        $this->Descr = "";
        $this->Dstart = "";
        $this->DClotEst = "";
        $this->budget = 0;
        $this->Jobs = array();
    }
    public function getPk(): int
    {
        return $this->Pk;
    }

    public function setPk(int $Pk): void
    {
        $this->Pk = $Pk;
    }

    public function getIdent(): string
    {
        return $this->Ident;
    }

    public function setIdent(string $Ident): void
    {
        $this->Ident = $Ident;
    }

    public function getDescr(): string
    {
        return $this->Descr;
    }

    public function setDescr(string $Descr): void
    {
        $this->Descr = $Descr;
    }

    public function getJobs(): array
    {
        return $this->Jobs;
    }

    public function setJobs(array $Jobs): void
    {
        $this->Jobs = $Jobs;
    }

    public function getBudget(): int
    {
        return $this->budget;
    }

    public function setBudget(int $budget): void
    {
        $this->budget = $budget;
    }

    public function getDClotEst(): string
    {
        return $this->DClotEst;
    }

    public function setDClotEst(string $DClotEst): void
    {
        $this->DClotEst = $DClotEst;
    }

    public function getDstart(): string
    {
        return $this->Dstart;
    }

    public function setDstart(string $Dstart): void
    {
        $this->Dstart = $Dstart;
    }

    public static function fromArray(array $data) : ProjectEntity
    {
        try{
            $instance = new self();

            $instance->setPk($data['Pk_Project'] ?? null);
            $instance->setIdent($data['Ident']);
            $instance->setDescr($data['Descr']);
            $instance->setDstart($data['Dstart']);
            $instance->setDClotEst($data['DClotEst']);
            $instance->setBudget($data['Budget']);

            return $instance;
        } catch (\Exception $e){
            throw new MissingInformation("Des informations sont manquantes");
        }
    }

}