<?php


class ParallelQueryExecutor
{


    private $nodes = [

        "library_node1",

        "library_node2",

        "library_node3"

    ];





    public function execute(
        $keyword
    ) {


        $results = [];



        foreach (
            $this->nodes
            as $node
        ) {


            $connection =
                new mysqli(

                    "localhost",

                    "root",

                    "",

                    $node

                );



            if (
                $connection->connect_error
            ) {

                continue;
            }





            $sql = "

            SELECT *

            FROM books

            WHERE

            title LIKE '%$keyword%'

            OR

            author LIKE '%$keyword%'

            ";




            $query =
                $connection
                ->query($sql);





            while (
                $row =
                $query->fetch_assoc()
            ) {


                $row["node"] =
                    $node;



                $results[] =
                    $row;
            }
        }



        return $results;
    }
}
