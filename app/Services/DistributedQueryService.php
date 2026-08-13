<?php

require_once
    "../app/Infrastructure/Distributed/ParallelExecutor.php";


class DistributedQueryService
{
    private $executor;


    /*
    ========================================================
    CONSTRUCTOR
    ========================================================
    */

    public function __construct()
    {
        $this->executor =
            new ParallelExecutor();
    }


    /*
    ========================================================
    GET ALL BOOKS
    ========================================================

    Distributed Book List

    Node 1 → Technology
    Node 2 → Science
    Node 3 → Fiction
    ========================================================
    */

    public function getAllBooks()
    {
        $sql = "
            SELECT *
            FROM books
        ";


        $results =
            $this->executor
            ->execute($sql);


        $books = [];


        foreach (
            $results as $node => $value
        ) {

            /*
            Ignore performance information.
            */

            if (
                $node === "_performance"
            ) {
                continue;
            }


            if (
                !isset($value["data"])
            ) {
                continue;
            }


            foreach (
                $value["data"] as $book
            ) {

                $book["node"] =
                    $node;


                $book["node_category"] =
                    $value["category"] ?? "";


                $books[] =
                    $book;
            }
        }


        return $books;
    }


    /*
    ========================================================
    SEARCH BOOKS
    ========================================================

    Search across all distributed nodes.

    Uses actual parallel query execution.

    Node 1 ║
    Node 2 ║
    Node 3 ║
    ========================================================
    */

    public function searchBooks($keyword)
    {
        $keyword =
            trim($keyword);


        /*
        Escape search keyword.

        This prevents special SQL characters
        from breaking the query.
        */

        $safeKeyword =
            addslashes($keyword);


        $sql = "
            SELECT *
            FROM books
            WHERE
                title LIKE '%$safeKeyword%'
                OR author LIKE '%$safeKeyword%'
                OR category LIKE '%$safeKeyword%'
        ";


        /*
        Execute actual parallel query.
        */

        $performance =
            $this->executor
            ->executeWithPerformance($sql);


        $books = [];


        /*
        ====================================================
        COLLECT RESULTS FROM ALL NODES
        ====================================================
        */

        foreach (
            $performance["results"]
            as $node => $value
        ) {

            /*
            Ignore performance metadata.
            */

            if (
                $node === "_performance"
            ) {
                continue;
            }


            if (
                !isset($value["data"])
            ) {
                continue;
            }


            foreach (
                $value["data"] as $book
            ) {

                $book["node"] =
                    $node;


                $book["node_category"] =
                    $value["category"] ?? "";


                $books[] =
                    $book;
            }
        }


        return [

            "books" =>
            $books,

            "parallel_time" =>
            $performance["total_time"] ?? 0,

            "method" =>
            "parallel",

            "keyword" =>
            $keyword,

            "node_results" =>
            $performance["results"] ?? []
        ];
    }


    /*
    ========================================================
    PERFORMANCE BENCHMARK
    ========================================================

    The SAME SQL query is executed using:

        1. Sequential execution
        2. Parallel execution

    Then actual execution times
    are compared.
    ========================================================
    */

    public function getPerformance()
    {
        $sql = "
            SELECT *
            FROM books
        ";


        /*
        ====================================================
        SEQUENTIAL EXECUTION
        ====================================================
        */

        $sequential =
            $this->executor
            ->executeSequential($sql);


        $sequentialTime =
            $sequential["_performance"]["total_time"]
            ?? 0;


        /*
        ====================================================
        PARALLEL EXECUTION
        ====================================================
        */

        $parallel =
            $this->executor
            ->executeWithPerformance($sql);


        $parallelTime =
            $parallel["total_time"]
            ?? 0;


        /*
        ====================================================
        PERFORMANCE IMPROVEMENT
        ====================================================
        */

        $improvement =
            0;


        if (
            $sequentialTime > 0
        ) {

            $improvement =
                (
                    (
                        $sequentialTime -
                        $parallelTime
                    )
                    /
                    $sequentialTime
                )
                * 100;


            $improvement =
                round(
                    $improvement,
                    1
                );
        }


        /*
        Determine which method
        is actually faster.
        */

        $parallelFaster =
            $parallelTime <
            $sequentialTime;


        /*
        ====================================================
        NODE PERFORMANCE INFORMATION
        ====================================================
        */

        $nodes = [];


        foreach (
            $parallel["results"]
            as $node => $value
        ) {

            /*
            Ignore performance metadata.
            */

            if (
                $node === "_performance"
            ) {
                continue;
            }


            $nodeBooks =
                $value["data"] ?? [];


            $nodes[] = [

                "node" =>
                $node,

                "category" =>
                $value["category"] ?? "",

                "books" =>
                count($nodeBooks),

                "time" =>
                $value["time"] ?? 0,

                "status" => (
                    isset($value["error"])
                    ? "OFFLINE"
                    : "ONLINE"
                )
            ];
        }


        /*
        ====================================================
        TOTAL DISTRIBUTED BOOKS
        ====================================================
        */

        $totalBooks =
            $this->countBooks(
                $parallel
            );


        /*
        ====================================================
        RETURN COMPLETE PERFORMANCE DATA
        ====================================================
        */

        return [

            /*
            Actual sequential time.
            */

            "sequential_time" =>
            $sequentialTime,


            /*
            Actual parallel time.
            */

            "parallel_time" =>
            $parallelTime,


            /*
            Calculated improvement.
            */

            "improvement" =>
            $improvement,


            /*
            TRUE when parallel is
            actually faster.
            */

            "parallel_faster" =>
            $parallelFaster,


            /*
            Distributed node information.
            */

            "nodes" =>
            $nodes,


            /*
            Total books across all shards.
            */

            "total_books" =>
            $totalBooks,


            /*
            Raw sequential execution.
            */

            "sequential_results" =>
            $sequential,


            /*
            Raw parallel execution.
            */

            "parallel_results" =>
            $parallel
        ];
    }


    /*
    ========================================================
    COUNT BOOKS
    ========================================================
    */

    private function countBooks($results)
    {
        $total =
            0;


        /*
        If complete parallel response
        was passed.
        */

        if (
            isset(
                $results["results"]
            )
        ) {

            $results =
                $results["results"];
        }


        foreach (
            $results as $node => $value
        ) {

            /*
            Ignore performance information.
            */

            if (
                $node === "_performance"
            ) {
                continue;
            }


            if (
                isset(
                    $value["data"]
                )
            ) {

                $total +=
                    count(
                        $value["data"]
                    );
            }
        }


        return $total;
    }


    /*
    ========================================================
    PERFORMANCE SUMMARY
    ========================================================

    Used by Dashboard widgets.
    ========================================================
    */

    public function getPerformanceSummary()
    {
        $performance =
            $this->getPerformance();


        return [

            "total_books" =>
            $performance["total_books"],

            "parallel_time" =>
            $performance["parallel_time"],

            "sequential_time" =>
            $performance["sequential_time"],

            "improvement" =>
            $performance["improvement"],

            "parallel_faster" =>
            $performance["parallel_faster"],

            "nodes" =>
            $performance["nodes"]
        ];
    }
}
