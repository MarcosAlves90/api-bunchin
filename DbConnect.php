<?php

/**
 * Database Connection
 */
class DbConnect
{
    private $server;
    private $dbname;
    private $user;
    private $pass;

    public function __construct()
    {
        $this->server = getenv('DB_HOST');
        $this->dbname = getenv('DB_NAME');
        $this->user = getenv('DB_USER');
        $this->pass = getenv('DB_PASSWORD');
    }

    public function connect()
    {
        try {
            $conn = new PDO('pgsql:host=' . $this->server . ';dbname=' . $this->dbname, $this->user, $this->pass);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        } catch (\Exception $e) {
            echo "Database Error: " . $e->getMessage();
        }
    }
}

?>