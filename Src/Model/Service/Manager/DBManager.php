<?php

namespace DISEUMAT\Model\Service\Manager;

class DBManager
{
    private $pdo;
    private static $instance;

    private function __construct(){
        require 'config.php';
        $this->pdo = new \PDO($dsn, $dbuser, $dbpass);
    }

    public static function getInstance(){
        if(empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }
}