
<?php

require_once "../app/Services/DistributedQueryService.php";


class DashboardController
{
    /*
    ========================================================
    PERFORMANCE DASHBOARD
    ========================================================

    Real-time distributed database performance data.

    Data source:
        DistributedQueryService

    Measures:
        - Total distributed books
        - Sequential query time
        - Parallel query time
        - Performance improvement
        - Node status
        - Node book count
        - Node execution time
    */


    public function index()
    {
        /*
        ====================================================
        Get REAL performance data
        ====================================================
        */

        $service =
            new DistributedQueryService();


        try {

            $performance =
                $service->getPerformance();
        } catch (Throwable $e) {

            /*
            If performance query fails,
            show safe dashboard error.
            */

            echo "
                <div style='
                    margin:40px;
                    padding:25px;
                    background:#fff0f0;
                    border:1px solid #ffb3b3;
                    border-radius:15px;
                    font-family:Arial;
                    color:#8b1e1e;
                '>

                    <h2>
                        ⚠️ Performance Dashboard Error
                    </h2>

                    <p>
                        Unable to collect distributed
                        database performance data.
                    </p>

                </div>
            ";

            return;
        }


        /*
        ====================================================
        Extract REAL values
        ====================================================
        */

        $totalBooks =
            (int)($performance["total_books"] ?? 0);


        $sequentialTime =
            (float)($performance["sequential_time"] ?? 0);


        $parallelTime =
            (float)($performance["parallel_time"] ?? 0);


        $improvement =
            (float)($performance["improvement"] ?? 0);


        $parallelFaster =
            (bool)($performance["parallel_faster"] ?? false);


        $nodes =
            $performance["nodes"] ?? [];


        /*
        ====================================================
        Format values
        ====================================================
        */

        $sequentialDisplay =
            number_format(
                $sequentialTime,
                2
            );


        $parallelDisplay =
            number_format(
                $parallelTime,
                2
            );


        $improvementDisplay =
            number_format(
                max(0, $improvement),
                1
            );


        /*
        ====================================================
        Count active nodes
        ====================================================
        */

        $activeNodes = 0;


        foreach ($nodes as $node) {

            if (
                isset($node["status"]) &&
                $node["status"] === "ONLINE"
            ) {

                $activeNodes++;
            }
        }


        $totalNodes =
            count($nodes);


        /*
        ====================================================
        Performance message
        ====================================================
        */

        if ($parallelFaster) {

            $performanceMessage =
                "Parallel Processing is "
                . $improvementDisplay
                . "% faster";
        } elseif (
            $parallelTime == $sequentialTime
        ) {

            $performanceMessage =
                "Both methods have similar performance";
        } else {

            $performanceMessage =
                "Sequential Processing is currently faster";
        }


        /*
        ====================================================
        HTML OUTPUT
        ====================================================
        */

        echo "

        <link rel='stylesheet'
              href='assets/css/dashboard.css'>


        <div class='performance-page'>


            <!-- =========================================
                 HEADER
            ========================================== -->

            <div class='performance-header'>

                <div>

                    <div class='live-label'>
                        ⚡ LIVE SYSTEM MONITOR
                    </div>

                    <h1>
                        🌐 Distributed Performance Dashboard
                    </h1>

                    <p>
                        Advanced Database System Monitoring
                    </p>

                </div>


                <div class='system-status'>
                    <span class='status-dot'></span>
                    SYSTEM ACTIVE
                </div>

            </div>



            <!-- =========================================
                 REAL-TIME STAT CARDS
            ========================================== -->

            <div class='performance-stats'>


                <div class='performance-stat books-stat'>

                    <div class='stat-icon'>
                        📚
                    </div>

                    <div class='stat-content'>

                        <span>
                            Total Books
                        </span>

                        <strong>
                            {$totalBooks}
                        </strong>

                        <small>
                            LIVE
                        </small>

                    </div>

                </div>



                <div class='performance-stat parallel-stat'>

                    <div class='stat-icon'>
                        🚀
                    </div>

                    <div class='stat-content'>

                        <span>
                            Parallel Time
                        </span>

                        <strong>
                            {$parallelDisplay}
                            <em>ms</em>
                        </strong>

                        <small>
                            REAL EXECUTION
                        </small>

                    </div>

                </div>



                <div class='performance-stat sequential-stat'>

                    <div class='stat-icon'>
                        🐢
                    </div>

                    <div class='stat-content'>

                        <span>
                            Sequential Time
                        </span>

                        <strong>
                            {$sequentialDisplay}
                            <em>ms</em>
                        </strong>

                        <small>
                            REAL EXECUTION
                        </small>

                    </div>

                </div>



                <div class='performance-stat node-stat'>

                    <div class='stat-icon'>
                        🌐
                    </div>

                    <div class='stat-content'>

                        <span>
                            Active Nodes
                        </span>

                        <strong>
                            {$activeNodes}
                            /
                            {$totalNodes}
                        </strong>

                        <small>
                            ONLINE
                        </small>

                    </div>

                </div>


            </div>



            <!-- =========================================
                 SEQUENTIAL VS PARALLEL
            ========================================== -->

            <section class='comparison-panel'>

                <div class='section-heading'>

                    <div>

                        <span class='section-label'>
                            REAL-TIME EXECUTION
                        </span>

                        <h2>
                            ⚡ Sequential vs Parallel Processing
                        </h2>

                        <p>
                            Actual query execution time
                            across the distributed database cluster.
                        </p>

                    </div>

                    <div class='executing-badge'>
                        <span></span>
                        EXECUTING
                    </div>

                </div>



                <div class='execution-comparison'>


                    <!-- SEQUENTIAL -->

                    <div class='execution-card sequential-card'>

                        <div class='execution-top'>

                            <div class='execution-title'>

                                <span class='execution-icon'>
                                    🐢
                                </span>

                                <div>

                                    <strong>
                                        Sequential Query
                                    </strong>

                                    <small>
                                        Node 1
                                        →
                                        Node 2
                                        →
                                        Node 3
                                    </small>

                                </div>

                            </div>

                            <div class='execution-time'>
                                {$sequentialDisplay}
                                <span>ms</span>
                            </div>

                        </div>


                        <div class='wave-container sequential-wave'>

                            <div class='wave-line'></div>
                            <div class='wave-line'></div>
                            <div class='wave-line'></div>

                        </div>


                        <div class='execution-footer'>

                            <span>
                                🐢 Processes nodes one by one
                            </span>

                            <strong>
                                SEQUENTIAL
                            </strong>

                        </div>

                    </div>



                    <!-- PARALLEL -->

                    <div class='execution-card parallel-card'>

                        <div class='execution-top'>

                            <div class='execution-title'>

                                <span class='execution-icon'>
                                    🚀
                                </span>

                                <div>

                                    <strong>
                                        Parallel Query
                                    </strong>

                                    <small>
                                        Node 1
                                        ║
                                        Node 2
                                        ║
                                        Node 3
                                    </small>

                                </div>

                            </div>

                            <div class='execution-time'>
                                {$parallelDisplay}
                                <span>ms</span>
                            </div>

                        </div>


                        <div class='wave-container parallel-wave'>

                            <div class='wave-line'></div>
                            <div class='wave-line'></div>
                            <div class='wave-line'></div>

                        </div>


                        <div class='execution-footer'>

                            <span>
                                🚀 Processes nodes simultaneously
                            </span>

                            <strong>
                                PARALLEL
                            </strong>

                        </div>

                    </div>


                </div>



                <!-- RESULT -->

                <div class='performance-result'>

                    <div class='result-icon'>
                        🚀
                    </div>

                    <div class='result-text'>

                        <span>
                            PERFORMANCE IMPROVEMENT
                        </span>

                        <strong>
                            {$improvementDisplay}%
                        </strong>

                        <small>
                            {$performanceMessage}
                        </small>

                    </div>


                    <div class='result-comparison'>

                        Sequential:

                        <strong>
                            {$sequentialDisplay} ms
                        </strong>

                        <span>
                            VS
                        </span>

                        Parallel:

                        <strong>
                            {$parallelDisplay} ms
                        </strong>

                    </div>

                </div>

            </section>



            <!-- =========================================
                 DISTRIBUTED NODES
            ========================================== -->

            <section class='nodes-section'>

                <div class='section-heading'>

                    <div>

                        <span class='section-label'>
                            DISTRIBUTED DATABASE
                        </span>

                        <h2>
                            🌐 Database Cluster Nodes
                        </h2>

                    </div>

                    <div class='cluster-status'>

                        <span class='status-dot'></span>

                        {$activeNodes}
                        /
                        {$totalNodes}
                        ONLINE

                    </div>

                </div>



                <div class='nodes-grid'>
        ";


        /*
        ====================================================
        Render each real node
        ====================================================
        */

        foreach ($nodes as $node) {

            $nodeName =
                htmlspecialchars(
                    $node["node"] ?? "Unknown"
                );


            $category =
                htmlspecialchars(
                    $node["category"] ?? "Unknown"
                );


            $bookCount =
                (int)(
                    $node["books"] ?? 0
                );


            $nodeTime =
                number_format(
                    (float)(
                        $node["time"] ?? 0
                    ),
                    2
                );


            $status =
                $node["status"] ?? "OFFLINE";


            $isOnline =
                $status === "ONLINE";


            $statusClass =
                $isOnline
                ? "online"
                : "offline";


            $statusIcon =
                $isOnline
                ? "🟢"
                : "🔴";


            /*
            Node icon
            */

            if (
                stripos(
                    $category,
                    "Technology"
                ) !== false
            ) {

                $nodeIcon = "💻";
            } elseif (
                stripos(
                    $category,
                    "Science"
                ) !== false
            ) {

                $nodeIcon = "🔬";
            } else {

                $nodeIcon = "📖";
            }


            echo "

                <div class='node-card {$statusClass}'>

                    <div class='node-card-top'>

                        <div class='node-icon'>
                            {$nodeIcon}
                        </div>

                        <div class='node-status'>
                            {$statusIcon}
                            {$status}
                        </div>

                    </div>


                    <h3>
                        {$nodeName}
                    </h3>


                    <p class='node-category'>
                        {$category} Books
                    </p>


                    <div class='node-details'>

                        <div>

                            <span>
                                BOOKS
                            </span>

                            <strong>
                                {$bookCount}
                            </strong>

                        </div>


                        <div>

                            <span>
                                QUERY TIME
                            </span>

                            <strong>
                                {$nodeTime}
                                <small>ms</small>
                            </strong>

                        </div>

                    </div>


                    <div class='node-progress'>

                        <div></div>

                    </div>


                    <span class='node-active'>
                        {$status}
                        SHARD
                    </span>

                </div>

            ";
        }


        echo "

                </div>

            </section>



            <!-- =========================================
                 ARCHITECTURE
            ========================================== -->

            <section class='architecture-section'>

                <div class='section-heading'>

                    <div>

                        <span class='section-label'>
                            SYSTEM ARCHITECTURE
                        </span>

                        <h2>
                            🏗 Distributed Query Pipeline
                        </h2>

                    </div>

                </div>


                <div class='pipeline'>

                    <div class='pipeline-step'>
                        <span>👤</span>
                        <strong>User Request</strong>
                    </div>

                    <div class='pipeline-arrow'>
                        →
                    </div>

                    <div class='pipeline-step'>
                        <span>🎯</span>
                        <strong>Query Coordinator</strong>
                    </div>

                    <div class='pipeline-arrow'>
                        →
                    </div>

                    <div class='pipeline-step active-step'>
                        <span>⚡</span>
                        <strong>Parallel Execution</strong>
                    </div>

                    <div class='pipeline-arrow'>
                        →
                    </div>

                    <div class='pipeline-step'>
                        <span>🔗</span>
                        <strong>Merge Results</strong>
                    </div>

                    <div class='pipeline-arrow'>
                        →
                    </div>

                    <div class='pipeline-step'>
                        <span>✅</span>
                        <strong>Final Response</strong>
                    </div>

                </div>

            </section>



            <!-- =========================================
                 ADVANCED DATABASE FEATURES
            ========================================== -->

            <section class='features-section'>

                <span class='section-label'>
                    ADVANCED DATABASE
                </span>

                <h2>
                    🎯 System Features
                </h2>


                <div class='features-grid'>

                    <div class='feature-card'>
                        <span>⚡</span>
                        <h3>
                            Parallel Query Processing
                        </h3>
                        <p>
                            Queries execute across
                            multiple database nodes
                            simultaneously.
                        </p>
                    </div>


                    <div class='feature-card'>
                        <span>🌐</span>
                        <h3>
                            Distributed Data
                        </h3>
                        <p>
                            Books are horizontally
                            partitioned across
                            Technology, Science
                            and Fiction nodes.
                        </p>
                    </div>


                    <div class='feature-card'>
                        <span>📈</span>
                        <h3>
                            Horizontal Scaling
                        </h3>
                        <p>
                            Additional nodes can be
                            added to distribute
                            database workload.
                        </p>
                    </div>


                    <div class='feature-card'>
                        <span>🛡️</span>
                        <h3>
                            Fault Isolation
                        </h3>
                        <p>
                            Failure of one shard does
                            not require the entire
                            distributed system to stop.
                        </p>
                    </div>

                </div>

            </section>



            <!-- =========================================
                 FOOTER
            ========================================== -->

            <div class='performance-footer'>

                🌐 Distributed Library Management System

                <span>•</span>

                Advanced Database Project

                <span>•</span>

                <span class='footer-active'>
                    ● SYSTEM ACTIVE
                </span>

            </div>



            <a class='back-dashboard'
               href='?url=AdminController/dashboard'>

                ← Back to Admin Dashboard

            </a>


        </div>

        ";
    }
}
