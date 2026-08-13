<?php

class Database
{
    private $connection;

    public function __construct()
    {
        /*
        ==========================================
        Load Database Configuration
        ==========================================
        */

        $configPath =
            dirname(__DIR__) .
            "/Config/database.php";

        if (!file_exists($configPath)) {
            die("Database configuration file not found: "
                . $configPath);
        }

        $config = require $configPath;


        /*
        ==========================================
        Required Configuration Check
        ==========================================
        */

        $requiredKeys = [
            "host",
            "user",
            "password",
            "database"
        ];

        foreach ($requiredKeys as $key) {

            if (!array_key_exists($key, $config)) {

                die("Missing database configuration: "
                    . $key);
            }
        }


        /*
        ==========================================
        Create MySQL Connection
        ==========================================
        */

        $this->connection = new mysqli(

            $config["host"],

            $config["user"],

            $config["password"],

            $config["database"]

        );


        /*
        ==========================================
        Connection Error
        ==========================================
        */

        if ($this->connection->connect_errno) {

            die("Database Connection Failed: "
                . $this->connection->connect_error);
        }


        /*
        ==========================================
        Character Set
        ==========================================
        */

        if (!$this->connection->set_charset("utf8mb4")) {

            die("Database Charset Error: "
                . $this->connection->error);
        }
    }


    /*
    ==========================================
    Get Database Connection
    ==========================================
    */

    public function getConnection()
    {
        return $this->connection;
    }


    /*
    ==========================================
    Close Database Connection
    ==========================================
    */

    public function close()
    {
        if ($this->connection) {

            $this->connection->close();

            $this->connection = null;
        }
    }
}
