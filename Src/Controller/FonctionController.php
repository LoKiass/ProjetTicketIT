<?php

namespace DISEUMAT\Controller;

use DISEUMAT\Controller\BaseController;
use DISEUMAT\Exception\DatabaseException;
use DISEUMAT\Exception\NotFoundException;
use DISEUMAT\Model\Service\Manager\FonctionManager;

class FonctionController extends BaseController
{
    private FonctionManager $FM;
    public function __construct(){
        parent::__construct();
        $this->FM = new FonctionManager();
    }
    public function getFonction() : void {
        $this->requireLogin();
        try{
            if(isset($_GET['Pk'])){ // si on veut voir une fonction
                $Fonction = $this->FM->read($_GET['Pk']);
                echo $this->TemplateEngine->render("Fonction/InfoFonction.twig", ['FonctionEntity' => $Fonction]);
            }
            else{
                $TabFonction = $this->FM->list();
                echo $this->TemplateEngine->render("Fonction/ListFonction.twig", ['TabFonction' => $TabFonction]);
            }
        } catch (NotFoundException $e){
            echo $this->TemplateEngine->render("Fonction/ListFonction.twig", [ 'TabFonction' => null, 'errorMessage' => $e->getMessage()]);
        } catch (DatabaseException $e) {
            echo $this->TemplateEngine->render("Fonction/ListFonction.twig", [ 'TabFonction' => null, 'errorMessage' => $e->getMessage()]);
        }
    }
    public function createFonction() : void {
        echo $this->TemplateEngine->render("Fonction/CreateFonction.twig");
    }

}