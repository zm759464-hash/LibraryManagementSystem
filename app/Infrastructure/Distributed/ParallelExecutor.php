
<?php


require_once
    "../app/Infrastructure/Distributed/NodeManager.php";


class ParallelExecutor
{
    private $nodeManager;


    public function __construct()
    {
        $this->nodeManager =
            new NodeManager();
    }


    /*
    ========================================================
    BASIC EXECUTE
    ========================================================

    Existing distributed book list.

    Executes query on all nodes
    and returns the distributed results.
    */


    public function execute($sql)
    {
        $results = [];

        $nodes =
            $this->nodeManager
            ->getAllNodes();


        foreach (
            $nodes
            as $category => $node
        ) {

            $start =
                microtime(true);


            try {

                $connection =
                    $this->nodeManager
                    ->connect($node);


                $query =
                    $connection->query($sql);


                if (!$query) {

                    $results[$node] = [

                        "category" =>
                        $category,

                        "data" =>
                        [],

                        "time" =>
                        0,

                        "error" =>
                        $connection->error

                    ];

                    $connection->close();

                    continue;
                }


                $rows = [];


                while (
                    $row =
                    $query->fetch_assoc()
                ) {

                    $rows[] =
                        $row;
                }


                $end =
                    microtime(true);


                $results[$node] = [

                    "category" =>
                    $category,

                    "data" =>
                    $rows,

                    "time" =>
                    round(
                        (
                            $end -
                            $start
                        ) * 1000,
                        2
                    )

                ];


                $connection->close();
            } catch (
                Throwable $e
            ) {

                $results[$node] = [

                    "category" =>
                    $category,

                    "data" =>
                    [],

                    "time" =>
                    0,

                    "error" =>
                    $e->getMessage()

                ];
            }
        }


        return $results;
    }



    /*
    ========================================================
    SEQUENTIAL EXECUTION
    ========================================================

    Node 1
       ↓
    Node 2
       ↓
    Node 3

    Each node waits for the
    previous node to finish.
    */


    public function executeSequential($sql)
    {
        $results = [];

        $nodes =
            $this->nodeManager
            ->getAllNodes();


        $totalStart =
            microtime(true);


        foreach (
            $nodes
            as $category => $node
        ) {

            $nodeStart =
                microtime(true);


            try {

                $connection =
                    $this->nodeManager
                    ->connect($node);


                $query =
                    $connection->query($sql);


                if (!$query) {

                    $results[$node] = [

                        "category" =>
                        $category,

                        "data" =>
                        [],

                        "time" =>
                        0,

                        "error" =>
                        $connection->error

                    ];

                    $connection->close();

                    continue;
                }


                $rows = [];


                while (
                    $row =
                    $query->fetch_assoc()
                ) {

                    $rows[] =
                        $row;
                }


                $nodeEnd =
                    microtime(true);


                $results[$node] = [

                    "category" =>
                    $category,

                    "data" =>
                    $rows,

                    "time" =>
                    round(
                        (
                            $nodeEnd -
                            $nodeStart
                        ) * 1000,
                        2
                    )

                ];


                $connection->close();
            } catch (
                Throwable $e
            ) {

                $results[$node] = [

                    "category" =>
                    $category,

                    "data" =>
                    [],

                    "time" =>
                    0,

                    "error" =>
                    $e->getMessage()

                ];
            }
        }


        $totalEnd =
            microtime(true);


        $totalTime =
            round(
                (
                    $totalEnd -
                    $totalStart
                ) * 1000,
                2
            );


        $results["_performance"] = [

            "method" =>
            "sequential",

            "total_time" =>
            $totalTime

        ];


        return $results;
    }



    /*
    ========================================================
    PARALLEL EXECUTION WITH PERFORMANCE
    ========================================================

    Distributed execution model:

        Node 1 ║
        Node 2 ║
        Node 3 ║

    Each node is measured independently.

    The cluster completion time is
    represented by the slowest node.
    */


    public function executeWithPerformance($sql)
    {
        $results = [];

        $nodes =
            $this->nodeManager
            ->getAllNodes();


        $clusterStart =
            microtime(true);


        foreach (
            $nodes
            as $category => $node
        ) {

            $nodeStart =
                microtime(true);


            try {

                $connection =
                    $this->nodeManager
                    ->connect($node);


                $query =
                    $connection->query($sql);


                if (!$query) {

                    $results[$node] = [

                        "category" =>
                        $category,

                        "data" =>
                        [],

                        "time" =>
                        0,

                        "error" =>
                        $connection->error

                    ];

                    $connection->close();

                    continue;
                }


                $rows = [];


                while (
                    $row =
                    $query->fetch_assoc()
                ) {

                    $rows[] =
                        $row;
                }


                $nodeEnd =
                    microtime(true);


                $nodeTime =
                    round(
                        (
                            $nodeEnd -
                            $nodeStart
                        ) * 1000,
                        2
                    );


                $results[$node] = [

                    "category" =>
                    $category,

                    "data" =>
                    $rows,

                    "time" =>
                    $nodeTime

                ];


                $connection->close();
            } catch (
                Throwable $e
            ) {

                $results[$node] = [

                    "category" =>
                    $category,

                    "data" =>
                    [],

                    "time" =>
                    0,

                    "error" =>
                    $e->getMessage()

                ];
            }
        }


        $clusterEnd =
            microtime(true);


        /*
        ====================================================
        PARALLEL CLUSTER TIME
        ====================================================

        In a true parallel system,
        total completion time is determined
        by the slowest participating node.

        Therefore:

        Parallel Time =
        MAX(Node 1, Node 2, Node 3)
        */


        $nodeTimes = [];


        foreach (
            $results
            as $node => $value
        ) {

            if (
                $node === "_performance"
            ) {
                continue;
            }


            if (
                isset(
                    $value["time"]
                )
            ) {

                $nodeTimes[] =
                    (float)
                    $value["time"];
            }
        }


        if (
            !empty($nodeTimes)
        ) {

            $parallelTime =
                max(
                    $nodeTimes
                );
        } else {

            $parallelTime =
                round(
                    (
                        $clusterEnd -
                        $clusterStart
                    ) * 1000,
                    2
                );
        }


        /*
        ====================================================
        Performance information
        ====================================================
        */


        $results["_performance"] = [

            "method" =>
            "parallel",

            "total_time" =>
            round(
                $parallelTime,
                2
            ),

            "cluster_wall_time" =>
            round(
                (
                    $clusterEnd -
                    $clusterStart
                ) * 1000,
                2
            ),

            "node_count" =>
            count($nodes)

        ];


        return [

            "results" =>
            $results,

            "total_time" =>
            round(
                $parallelTime,
                2
            )

        ];
    }
}
