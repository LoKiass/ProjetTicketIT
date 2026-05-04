<?php

namespace DISEUMAT\Controller\Model\Service\Manager;

class DBManager
{
    private $pdo;
    private static $instance;

    private function __construct(){
        try {
            $this->pdo = new \PDO('mysql:host=localhost;dbname=DISEUMAT', 'root', '');
        } catch (\PDOException $e) {

        }
    }

    public static function getInstance(){
        if(empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }
}