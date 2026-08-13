<?php


class PerformanceService
{


    private $nodes = [

        "library_node1",

        "library_node2",

        "library_node3"

    ];




    public function measure()
    {


        $metrics = [];

        $totalStart =
            microtime(true);




        foreach (
            $this->nodes
            as $node
        ) {


            $start =
                microtime(true);



            $conn =
                new mysqli(

                    "localhost",

                    "root",

                    "",

                    $node

                );




            if (
                $conn->connect_error
            ) {


                $metrics[$node] = [

                    "status" => "DOWN",

                    "time" => 0

                ];


                continue;
            }





            $sql =
                "SELECT COUNT(*) AS total FROM books";



            $result =
                $conn->query($sql);



            $data =
                $result->fetch_assoc();




            $end =
                microtime(true);




            $metrics[$node] = [

                "status" => "ONLINE",

                "books" => $data["total"],

                "time" =>
                round(
                    ($end - $start) * 1000,
                    2
                )

            ];
        }





        $totalEnd =
            microtime(true);



        $metrics["total_time"] =
            round(

                ($totalEnd - $totalStart) * 1000,

                2

            );



        return $metrics;
    }
}
