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
            $dsn = 'pgsql:host=' . $this->server .
                ';dbname=' . $this->dbname .
                ';sslmode=require';
            $conn = new PDO($dsn, $this->user, $this->pass);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return [
                'status' => 'success',
                'connection' => $conn
            ];
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            if (strpos($msg, 'Endpoint ID is not specified') !== false || strpos($msg, 'connection is insecure') !== false) {
                return [
                    'status' => 'error',
                    'message' => 'Erro de conexão Neon: verifique se o endpoint está correto e se sslmode=require está configurado.',
                    'details' => $msg
                ];
            }
            return [
                'status' => 'error',
                'message' => 'Database Error',
                'details' => $msg
            ];
        }
    }
}

?>