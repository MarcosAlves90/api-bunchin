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
        // Acessar as variáveis de ambiente para as credenciais do banco de dados
        $this->server = getenv('DB_HOST');   // O host do banco de dados, como definido no Render
        $this->dbname = getenv('DB_NAME');   // O nome do banco de dados
        $this->user = getenv('DB_USER');     // O nome de usuário do banco de dados
        $this->pass = getenv('DB_PASSWORD'); // A senha do banco de dados
    }

    public function connect()
    {
        try {
            // Conexão com PostgreSQL usando PDO
            $conn = new PDO('pgsql:host=' . $this->server . ';dbname=' . $this->dbname, $this->user, $this->pass);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        } catch (\Exception $e) {
            echo "Database Error: " . $e->getMessage();
        }
    }
}

?>