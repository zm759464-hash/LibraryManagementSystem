<?php

class NodeManager
{
    /*
    ========================================================
    DISTRIBUTED DATABASE NODE MANAGER
    ========================================================

    Configuration is loaded from:

    app/Config/nodes.php

    Node 1 → Technology → 3307
    Node 2 → Science    → 3308
    Node 3 → Fiction    → 3309
    ========================================================
    */

    private $nodes = [];


    /*
    ========================================================
    CONSTRUCTOR
    ========================================================
    */

    public function __construct()
    {
        /*
            Load central node configuration
        */

        $configPath =
            dirname(__DIR__, 2) .
            "/Config/nodes.php";


        if (!file_exists($configPath)) {

            throw new Exception(
                "Node configuration file not found: "
                . $configPath
            );
        }


        $config =
            require $configPath;


        /*
            Validate configuration
        */

        if (!is_array($config)) {

            throw new Exception(
                "Invalid node configuration."
            );
        }


        /*
            Convert:

            node1 → Technology
            node2 → Science
            node3 → Fiction
        */

        $this->nodes = [

            "Technology" =>
                $config["node1"],

            "Science" =>
                $config["node2"],

            "Fiction" =>
                $config["node3"]

        ];
    }


    /*
    ========================================================
    FIND DATABASE NODE BY CATEGORY
    ========================================================
    */

    public function getNodeByCategory(
        $category
    ) {

        if (
            isset(
                $this->nodes[$category]
            )
        ) {

            return
                $this->nodes[$category];
        }


        return null;
    }


    /*
    ========================================================
    GET ALL DISTRIBUTED NODES
    ========================================================
    */

    public function getAllNodes()
    {
        return $this->nodes;
    }


    /*
    ========================================================
    DATABASE CONNECTION
    ========================================================
    */

    public function connect(
        $node
    ) {

        /*
        ----------------------------------------------------
        Accept node configuration array
        ----------------------------------------------------
        */

        if (is_array($node)) {

            $host =
                $node["host"];

            $port =
                $node["port"];

            $user =
                $node["user"];

            $password =
                $node["password"];

            $database =
                $node["database"];

        } else {

            /*
            ------------------------------------------------
            Backward compatibility
            ------------------------------------------------

            If a database name is passed directly,
            try to find it from the configuration.
            */

            $nodeConfig =
                $this->findNodeByDatabase(
                    $node
                );


            if ($nodeConfig === null) {

                throw new Exception(
                    "Unknown database node: "
                    . $node
                );
            }


            $host =
                $nodeConfig["host"];

            $port =
                $nodeConfig["port"];

            $user =
                $nodeConfig["user"];

            $password =
                $nodeConfig["password"];

            $database =
                $nodeConfig["database"];
        }


        /*
        ====================================================
        MYSQL CONNECTION
        ====================================================
        */

        mysqli_report(
            MYSQLI_REPORT_OFF
        );


        $connection =
            new mysqli(

                $host,

                $user,

                $password,

                $database,

                $port

            );


        /*
        ====================================================
        CONNECTION ERROR
        ====================================================
        */

        if (
            $connection->connect_errno
        ) {

            throw new Exception(

                "Database Connection Failed: "
                . $database
                . " on port "
                . $port
                . " - "
                . $connection->connect_error

            );
        }


        /*
        ====================================================
        UTF-8 / Myanmar Text Support
        ====================================================
        */

        if (
            !$connection->set_charset(
                "utf8mb4"
            )
        ) {

            $connection->close();


            throw new Exception(
                "Failed to set utf8mb4 charset for "
                . $database
            );
        }


        return $connection;
    }


    /*
    ========================================================
    FIND NODE BY DATABASE NAME
    ========================================================
    */

    private function findNodeByDatabase(
        $database
    ) {

        foreach (
            $this->nodes
            as $node
        ) {

            if (
                isset(
                    $node["database"]
                )
                &&
                $node["database"] ===
                    $database
            ) {

                return $node;
            }
        }


        return null;
    }
}