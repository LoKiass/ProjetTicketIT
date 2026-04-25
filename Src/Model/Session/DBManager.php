<?php

namespace ProjectTicketIT\Model\Session;

class DBManager
{
    private $pdo;

    private function __construct(){
        try {
            $this->pdo = new \PDO('mysql:host=localhost;dbname=DISEUMAT', 'root', '');
        } catch (\PDOException $e) {

        }
    }

    public static function getInstance(){
        return new self();
    }
}