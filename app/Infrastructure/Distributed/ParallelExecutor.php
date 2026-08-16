<?php

require_once
    "../app/Infrastructure/Distributed/NodeManager.php";


class ParallelExecutor
{
    private $nodeManager;


    /*
    ========================================================
    CONSTRUCTOR
    ========================================================
    */

    public function __construct()
    {
        $this->nodeManager =
            new NodeManager();
    }


    /*
    ========================================================
    BASIC EXECUTE
    ========================================================

    Execute SQL across all distributed nodes.

    This method uses REAL asynchronous execution.

        Technology -> 3307
        Science    -> 3308
        Fiction    -> 3309

    All queries are started before results are collected.
    ========================================================
    */

    public function execute($sql)
    {
        $parallel =
            $this->executeAsync(
                $sql
            );


        return
            $parallel["results"];
    }


    /*
    ========================================================
    REAL PARALLEL EXECUTION
    ========================================================

    MySQLi asynchronous query processing.

    Step 1:
        Connect to every node.

    Step 2:
        Send MYSQLI_ASYNC query to every node.

    Step 3:
        Use mysqli_poll() to wait for completed queries.

    Step 4:
        Fetch results from every node.

    Therefore:

             SQL
              |
        ┌─────┼─────┐
        ↓     ↓     ↓
      Node1 Node2 Node3
       3307  3308  3309
        ↓     ↓     ↓
        └─────┼─────┘
              ↓
        Merge Results
    ========================================================
    */

    private function executeAsync($sql)
    {
        $results = [];


        $nodes =
            $this->nodeManager
            ->getAllNodes();


        /*
        ====================================================
        PERFORMANCE START
        ====================================================
        */

        $clusterStart =
            microtime(true);


        /*
        ====================================================
        CONNECTION STORAGE
        ====================================================
        */

        $connections = [];


        /*
        ====================================================
        START ASYNC QUERY ON ALL NODES
        ====================================================

        IMPORTANT:

        We DO NOT wait for Node 1 before starting Node 2.

        Every node receives its query first.
        ====================================================
        */

        foreach (
            $nodes as $category => $node
        ) {

            $nodeStart =
                microtime(true);


            try {

                /*
                ------------------------------------------------
                Connect to node
                ------------------------------------------------
                */

                $connection =
                    $this->nodeManager
                    ->connect($node);


                /*
                ------------------------------------------------
                Start asynchronous query
                ------------------------------------------------
                */

                $started =
                    $connection->query(
                        $sql,
                        MYSQLI_ASYNC
                    );


                if (!$started) {

                    $results[$category] = [

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


                /*
                ------------------------------------------------
                Store connection information
                ------------------------------------------------
                */

                $connections[] = [

                    "connection" =>
                        $connection,

                    "category" =>
                        $category,

                    "start" =>
                        $nodeStart

                ];

            } catch (
                Throwable $e
            ) {

                $results[$category] = [

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


        /*
        ====================================================
        WAIT FOR ASYNC RESULTS
        ====================================================

        mysqli_poll() waits for one or more
        asynchronous MySQL queries to finish.
        ====================================================
        */

        while (
            !empty($connections)
        ) {

            $read = [];


            $errors = [];


            $reject = [];


            /*
            ------------------------------------------------
            Prepare connection lists
            ------------------------------------------------
            */

            foreach (
                $connections as $item
            ) {

                $read[] =
                    $item["connection"];
            }


            /*
            ------------------------------------------------
            Poll asynchronous queries
            ------------------------------------------------
            */

            $ready =
                mysqli_poll(
                    $read,
                    $errors,
                    $reject,
                    1
                );


            /*
            ------------------------------------------------
            No result yet

            Continue waiting.
            ------------------------------------------------
            */

            if (
                $ready === null
                ||
                $ready === false
                ||
                $ready === 0
            ) {

                continue;
            }


            /*
            =================================================
            PROCESS READY CONNECTIONS
            =================================================
            */

            foreach (
                $connections as $index => $item
            ) {

                $connection =
                    $item["connection"];


                /*
                ------------------------------------------------
                Check whether this connection is ready.
                ------------------------------------------------
                */

                $isReady =
                    false;


                foreach (
                    $read as $readyConnection
                ) {

                    if (
                        $readyConnection ===
                        $connection
                    ) {

                        $isReady =
                            true;

                        break;
                    }
                }


                if (!$isReady) {

                    continue;
                }


                $category =
                    $item["category"];


                $nodeStart =
                    $item["start"];


                try {

                    /*
                    --------------------------------------------
                    Retrieve asynchronous result
                    --------------------------------------------
                    */

                    $query =
                        $connection
                        ->reap_async_query();


                    if (!$query) {

                        $results[$category] = [

                            "category" =>
                                $category,

                            "data" =>
                                [],

                            "time" =>
                                0,

                            "error" =>
                                $connection->error

                        ];

                    } else {

                        /*
                        ----------------------------------------
                        Fetch rows
                        ----------------------------------------
                        */

                        $rows = [];


                        while (
                            $row =
                            $query->fetch_assoc()
                        ) {

                            $rows[] =
                                $row;
                        }


                        /*
                        ----------------------------------------
                        Node execution time
                        ----------------------------------------
                        */

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


                        $results[$category] = [

                            "category" =>
                                $category,

                            "data" =>
                                $rows,

                            "time" =>
                                $nodeTime

                        ];


                        /*
                        ----------------------------------------
                        Free result
                        ----------------------------------------
                        */

                        $query->free();
                    }

                } catch (
                    Throwable $e
                ) {

                    $results[$category] = [

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


                /*
                ------------------------------------------------
                Close connection
                ------------------------------------------------
                */

                $connection->close();


                /*
                ------------------------------------------------
                Remove processed connection
                ------------------------------------------------
                */

                unset(
                    $connections[$index]
                );
            }


            /*
            ------------------------------------------------
            Re-index array
            ------------------------------------------------
            */

            $connections =
                array_values(
                    $connections
                );
        }


        /*
        ====================================================
        CLUSTER END
        ====================================================
        */

        $clusterEnd =
            microtime(true);


        /*
        ====================================================
        CALCULATE NODE TIMES
        ====================================================
        */

        $nodeTimes = [];


        foreach (
            $results as $category => $value
        ) {

            if (
                $category === "_performance"
            ) {

                continue;
            }


            if (
                isset(
                    $value["time"]
                )
                &&
                !isset(
                    $value["error"]
                )
            ) {

                $nodeTimes[] =
                    (float)
                    $value["time"];
            }
        }


        /*
        ====================================================
        PARALLEL COMPLETION TIME
        ====================================================

        In true parallel execution:

        The total logical query completion time is
        approximately determined by the slowest node.

        Example:

            Node 1 = 120ms
            Node 2 = 80ms
            Node 3 = 150ms

        Parallel time ≈ 150ms
        ====================================================
        */

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
        RETURN PERFORMANCE INFORMATION
        ====================================================
        */

        $results["_performance"] = [

            "method" =>
                "parallel_async",

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
                count(
                    $nodes
                )

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


    /*
    ========================================================
    SEQUENTIAL EXECUTION
    ========================================================

    Node 1
       ↓
    Node 2
       ↓
    Node 3

    This is the baseline used for performance comparison.
    ========================================================
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
            $nodes as $category => $node
        ) {

            $nodeStart =
                microtime(true);


            try {

                /*
                ------------------------------------------------
                Connect
                ------------------------------------------------
                */

                $connection =
                    $this->nodeManager
                    ->connect($node);


                /*
                ------------------------------------------------
                Execute normal synchronous query
                ------------------------------------------------
                */

                $query =
                    $connection->query(
                        $sql
                    );


                if (!$query) {

                    $results[$category] = [

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


                /*
                ------------------------------------------------
                Fetch rows
                ------------------------------------------------
                */

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


                $results[$category] = [

                    "category" =>
                        $category,

                    "data" =>
                        $rows,

                    "time" =>
                        $nodeTime

                ];


                $query->free();


                $connection->close();

            } catch (
                Throwable $e
            ) {

                $results[$category] = [

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


        /*
        ====================================================
        TOTAL SEQUENTIAL TIME
        ====================================================
        */

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


        /*
        ====================================================
        PERFORMANCE METADATA
        ====================================================
        */

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
    */

    public function executeWithPerformance($sql)
    {
        return
            $this->executeAsync(
                $sql
            );
    }
}