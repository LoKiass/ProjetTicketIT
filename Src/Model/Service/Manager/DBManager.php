<?php

namespace DISEUMAT\Model\Service\Manager;

use PDO;

class DBManager
{
    private $pdo;
    private static $instance;

    private function __construct(){
        require 'config.php'; // Inclut les informations relatifs à la bd
        /** @var string $dbuser */
        /** @var string $dbpass */
        /** @var string $dsn */
        $this->pdo = new PDO($dsn, $dbuser, $dbpass);
    }

    public static function getInstance(){
        if(empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }
}