<?php

require_once "../app/Services/ParallelSearchService.php";


class SearchController
{
    public function index()
    {
        /*
        ========================================================
        START SESSION
        ========================================================
        */

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }


        /*
        ========================================================
        GET CURRENT USER
        ========================================================
        */

        $user = $_SESSION["user"] ?? null;


        /*
        ========================================================
        DETERMINE DASHBOARD
        ========================================================
        */

        $dashboardUrl =
            "?url=UserController/dashboard";


        /*
        Admin Detection
        --------------------------------------------------------
        Support:
        role = admin
        local_role = local_admin
        ========================================================
        */

        if ($user) {

            $role =
                strtolower(
                    trim(
                        (string)($user["role"] ?? "")
                    )
                );

            $localRole =
                strtolower(
                    trim(
                        (string)($user["local_role"] ?? "")
                    )
                );


            if (
                $role === "admin" ||
                $localRole === "local_admin"
            ) {

                $dashboardUrl =
                    "?url=AdminController/dashboard";
            }
        }


        /*
        ========================================================
        SEARCH REQUEST
        ========================================================
        */

        $keyword = trim(
            $_GET["keyword"] ?? ""
        );


        /*
        ========================================================
        SEARCH RESULT
        ========================================================
        */

        $books = [];


        if ($keyword !== "") {

            $service =
                new ParallelSearchService();


            $books =
                $service->search($keyword);
        }


        /*
        ========================================================
        ESCAPE HTML
        ========================================================
        */

        $escape = function ($value) {

            return htmlspecialchars(
                (string)$value,
                ENT_QUOTES,
                "UTF-8"
            );
        };


        /*
        ========================================================
        RESULT COUNT
        ========================================================
        */

        $resultCount =
            count($books);


        /*
        ========================================================
        PAGE
        ========================================================
        */

        echo "

        <link rel='stylesheet'
        href='assets/css/search.css'>


        <div class='search-page'>


            <!-- ==================================================
                 HEADER
            ================================================== -->


            <div class='search-header'>


                <div class='search-header-left'>


                    <div class='search-icon'>
                        🔎
                    </div>


                    <div>


                        <div class='search-eyebrow'>
                            DISTRIBUTED SYSTEM
                        </div>


                        <h1>
                            Parallel Book Search
                        </h1>


                        <p>
                            Search books across all distributed
                            database nodes simultaneously.
                        </p>


                    </div>


                </div>


                <div class='system-status'>


                    <span class='status-dot'></span>


                    SYSTEM ACTIVE


                </div>


            </div>



            <!-- ==================================================
                 SEARCH BOX
            ================================================== -->


            <div class='search-panel'>


                <div class='panel-title'>


                    <div>
                        ⚡
                    </div>


                    <div>


                        <h2>
                            Search Books
                        </h2>


                        <p>
                            Parallel query processing across
                            Technology, Science and Fiction nodes.
                        </p>


                    </div>


                </div>



                <form
                    method='GET'
                    class='search-form'
                >


                    <input
                        type='hidden'
                        name='url'
                        value='SearchController/index'
                    >


                    <div class='search-input-wrapper'>


                        <span class='input-icon'>
                            🔎
                        </span>


                        <input
                            type='text'
                            name='keyword'
                            value='{$escape($keyword)}'
                            placeholder='Search by title, author or category...'
                            autocomplete='off'
                            required
                        >


                    </div>



                    <button
                        type='submit'
                        class='search-button'
                    >


                        <span>
                            ⚡
                        </span>


                        Search in Parallel


                    </button>


                </form>



                <div class='search-hints'>


                    <span>
                        💻 Technology
                    </span>


                    <span>
                        🔬 Science
                    </span>


                    <span>
                        📖 Fiction
                    </span>


                </div>


            </div>



        ";



        /*
        ========================================================
        SEARCH RESULTS
        ========================================================
        */


        if ($keyword !== "") {


            echo "


            <div class='results-section'>


                <div class='results-header'>


                    <div>


                        <div class='results-eyebrow'>
                            SEARCH RESULTS
                        </div>


                        <h2>
                            Results for
                            <span>
                                \"{$escape($keyword)}\"
                            </span>
                        </h2>


                    </div>


                    <div class='result-count'>


                        <strong>
                            {$resultCount}
                        </strong>


                        <span>
                            Books Found
                        </span>


                    </div>


                </div>



                <div class='execution-banner'>


                    <div class='execution-icon'>
                        ⚡
                    </div>


                    <div>


                        <strong>
                            Parallel Query Executed
                        </strong>


                        <p>
                            Search request was distributed
                            across the database cluster.
                        </p>


                    </div>


                    <div class='execution-status'>
                        ● COMPLETED
                    </div>


                </div>



            ";



            /*
            ====================================================
            NO RESULTS
            ====================================================
            */


            if ($resultCount === 0) {


                echo "


                <div class='empty-state'>


                    <div class='empty-icon'>
                        📚
                    </div>


                    <h3>
                        No Books Found
                    </h3>


                    <p>
                        No books matched
                        <strong>
                            \"{$escape($keyword)}\"
                        </strong>.
                    </p>


                    <p class='empty-help'>
                        Try another title, author or category.
                    </p>


                </div>


                ";


            } else {


                /*
                ====================================================
                RESULT TABLE
                ====================================================
                */


                echo "


                <div class='results-table-wrapper'>


                    <table class='results-table'>


                        <thead>


                            <tr>


                                <th>
                                    BOOK
                                </th>


                                <th>
                                    TITLE
                                </th>


                                <th>
                                    AUTHOR
                                </th>


                                <th>
                                    CATEGORY
                                </th>


                                <th>
                                    DATABASE NODE
                                </th>


                                <th>
                                    STATUS
                                </th>


                            </tr>


                        </thead>


                        <tbody>


                ";



                foreach ($books as $book) {


                    $id =
                        $escape(
                            $book["global_book_id"]
                                ?? "-"
                        );


                    $title =
                        $escape(
                            $book["title"]
                                ?? "-"
                        );


                    $author =
                        $escape(
                            $book["author"]
                                ?? "-"
                        );


                    $category =
                        $escape(
                            $book["category"]
                                ?? "-"
                        );


                    $node =
                        $escape(
                            $book["node"]
                                ?? "-"
                        );


                    /*
                    Node Icon
                    */

                    $nodeIcon = "🌐";


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

                    } elseif (
                        stripos(
                            $category,
                            "Fiction"
                        ) !== false
                    ) {

                        $nodeIcon = "📖";
                    }



                    echo "


                    <tr>


                        <td>


                            <div class='book-id'>


                                <span class='book-mini-icon'>
                                    📚
                                </span>


                                {$id}


                            </div>


                        </td>



                        <td>


                            <div class='book-title'>
                                {$title}
                            </div>


                            <div class='book-subtitle'>
                                Library Book
                            </div>


                        </td>



                        <td>


                            <div class='author-name'>
                                ✍️ {$author}
                            </div>


                        </td>



                        <td>


                            <span class='category-badge'>


                                {$nodeIcon}


                                {$category}


                            </span>


                        </td>



                        <td>


                            <div class='node-name'>


                                <span>
                                    🌐
                                </span>


                                {$node}


                            </div>


                        </td>



                        <td>


                            <span class='result-status'>


                                <span class='result-dot'>
                                </span>


                                Found


                            </span>


                        </td>


                    </tr>


                    ";
                }



                echo "


                        </tbody>


                    </table>


                </div>


                ";
            }



            echo "


            </div>


            ";
        }



        /*
        ========================================================
        SEARCH FOOTER
        ========================================================
        */

        echo "


            <div class='search-footer'>


                <a
                    href='{$dashboardUrl}'
                    class='back-button'
                >

                    ← Back to Dashboard

                </a>



                <div class='footer-status'>


                    🌐 Distributed Library System


                    <span>•</span>


                    Parallel Query Processing


                    <span>•</span>


                    <b>● ACTIVE</b>


                </div>


            </div>



        </div>


        ";
    }
}