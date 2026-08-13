<?php


class NodeManager
{


    private $nodes = [

        "Technology" => "library_node1",

        "Science" => "library_node2",

        "Fiction" => "library_node3"

    ];





    /*
        Find Database Node
        By Book Category
    */

    public function getNodeByCategory($category)
    {


        if (isset($this->nodes[$category])) {


            return $this->nodes[$category];
        }



        return null;
    }






    /*
        Get All Nodes
    */


    public function getAllNodes()
    {


        return $this->nodes;
    }





    /*
        Database Connection
    */


    public function connect($database)
    {


        $connection = new mysqli(

            "localhost",

            "root",

            "",

            $database

        );



        if ($connection->connect_error) {


            throw new Exception(

                "Database Connection Failed: "
                    . $database

            );
        }



        return $connection;
    }
}
