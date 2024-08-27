<?php

namespace App;

use PDO;

class DataBaseConf {
    protected $dsn = 'mysql:host=mysql;dbname=ex_db';
    protected $username = 'user';
    protected $password = 'test';

    public function makeRequest($sqlRequest) {
        $pdo = new PDO($this->dsn, $this->username, $this->password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $resultReq = $pdo->exec($sqlRequest);

        if ($resultReq) {
            $val = true;
        } else {
            $val = false;
        }

        return $val;
    }

    public function getDsn() {
        return $this->dsn;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getPassword() {
        return $this->password;
    }
}