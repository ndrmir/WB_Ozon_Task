<?php

namespace Libraries;

use PDO;
use PDOException;

//Класс для работы с базой данных
class ConnectDb
{
    protected $dbname = 'ozon';
    protected $dbhost = 'localhost';
    protected $dbusername = 'root';
    protected $dbuserpassword = 'root';
    protected $db;

    public function __construct($dbhost = null, $dbusername = null, $dbuserpassword = null, $dbname = null)
    {

        if ($dbhost && $dbusername && $dbuserpassword && $dbname) {
            $this->dbhost = $dbhost;
            $this->dbusername = $dbusername;
            $this->dbuserpassword = $dbuserpassword;
            $this->dbname = $dbname;
        }
    }

    public function connect()
    {
        $config = [
            'dns' => 'mysql:host=' . $this->dbhost . ';dbname=' . $this->dbname . ';charset=utf8',
            'username' => $this->dbusername,
            'password' => $this->dbuserpassword,
        ];
        try {
            $this->db = new PDO($config['dns'], $config['username'], $config['password']);
            $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            return $this->db;
        } catch (PDOException $PDOException) {
            echo $PDOException->getMessage();
        }
        return false;
    }
    public function close()
    {
        $this->db = null;
    }
}
