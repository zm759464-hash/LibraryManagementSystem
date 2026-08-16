<?php


require_once "../app/Services/HoldService.php";
require_once "../app/Services/DistributedQueryService.php";
require_once "../app/Middleware/AuthMiddleware.php";
require_once "../app/Middleware/AdminMiddleware.php";


class HoldController
{

    /*
        ========================================================
        USER CREATE HOLD
        ========================================================
    */

    public function create(
        $bookId
    ) {

        AuthMiddleware::check();


        $user =
            $_SESSION["user"]
            ?? null;


        if (!$user) {

            header(
                "Location:?url=LoginController/login"
            );

            exit;
        }


        $service =
            new HoldService();


        $result =
            $service->create(
                $user["id"],
                $bookId
            );


        /*
            ====================================================
            SUCCESS
            ====================================================
        */

        if (
            is_array($result) &&
            ($result["success"] ?? false)
        ) {

            echo "

            <link
                rel='stylesheet'
                href='/assets/css/book.css'
            >


            <div class='hold-result-page'>

                <div class='hold-result-card hold-success'>

                    <div class='hold-result-icon'>
                        ✓
                    </div>

                    <div class='hold-result-badge'>
                        HOLD REQUEST SUBMITTED
                    </div>

                    <h1>
                        Hold Request Sent
                    </h1>

                    <p>
                        This book is currently out of stock.
                        Your hold request has been successfully
                        submitted.
                    </p>

                    <div class='hold-result-info'>

                        <span>
                            📚
                        </span>

                        <div>

                            <strong>
                                You will be notified
                            </strong>

                            <small>
                                when the book becomes available.
                            </small>

                        </div>

                    </div>

                    <div class='hold-result-actions'>

                        <a
                            href='?url=HoldController/myholds'
                            class='hold-primary-btn'
                        >
                            📋 My Holds
                        </a>

                        <a
                            href='?url=UserController/books'
                            class='hold-secondary-btn'
                        >
                            ← Back to Books
                        </a>

                    </div>

                </div>

            </div>

            ";

            return;
        }


        /*
            ====================================================
            GET FAILURE REASON
            ====================================================
        */

        $reason =
            is_array($result)
            ? ($result["reason"] ?? "unknown")
            : "unknown";


        /*
            ====================================================
            DEFAULT ERROR DATA
            ====================================================
        */

        $errorTitle =
            "Hold Request Not Sent";

        $errorMessage =
            "We could not submit your hold request.";

        $errorIcon =
            "⚠️";


        /*
            ====================================================
            BOOK IS AVAILABLE
            ====================================================
        */

        if (
            $reason === "book_available"
        ) {

            $available =
                (int)(
                    $result["available"]
                    ?? 0
                );


            $errorIcon =
                "📚";

            $errorTitle =
                "Hold Is Not Available";

            $errorMessage =
                "This book still has "
                . $available
                . " "
                . (
                    $available === 1
                    ? "copy"
                    : "copies"
                )
                . " available.";

        }


        /*
            ====================================================
            DUPLICATE HOLD
            ====================================================
        */

        elseif (
            $reason === "duplicate_hold"
        ) {

            $errorIcon =
                "🔖";

            $errorTitle =
                "Hold Already Exists";

            $errorMessage =
                "You already have an active hold request "
                . "for this book. You don't need to submit "
                . "another request.";
        }


        /*
            ====================================================
            BOOK NOT FOUND
            ====================================================
        */

        elseif (
            $reason === "book_not_found"
        ) {

            $errorIcon =
                "🔎";

            $errorTitle =
                "Book Not Found";

            $errorMessage =
                "The requested book could not be found "
                . "in the distributed library system.";
        }


        /*
            ====================================================
            INVALID BOOK
            ====================================================
        */

        elseif (
            $reason === "invalid_book"
        ) {

            $errorIcon =
                "⚠️";

            $errorTitle =
                "Invalid Book";

            $errorMessage =
                "The requested book ID is invalid.";
        }


        /*
            ====================================================
            DATABASE / SYSTEM ERROR
            ====================================================
        */

        elseif (
            $reason === "database_error" ||
            $reason === "book_lookup_failed"
        ) {

            $errorIcon =
                "⚙️";

            $errorTitle =
                "System Error";

            $errorMessage =
                "Something went wrong while processing "
                . "your hold request. Please try again.";
        }


        /*
            ====================================================
            ERROR PAGE
            ====================================================
        */

        echo "

        <link
            rel='stylesheet'
            href='/assets/css/book.css'
        >


        <div class='hold-result-page'>

            <div class='hold-result-card hold-error'>

                <div class='hold-result-icon'>
                    {$errorIcon}
                </div>

                <div class='hold-result-badge'>
                    HOLD REQUEST
                </div>

                <h1>
                    "
                    . htmlspecialchars(
                        $errorTitle
                    )
                    . "
                </h1>

                <p>
                    "
                    . htmlspecialchars(
                        $errorMessage
                    )
                    . "
                </p>

                <div class='hold-result-actions'>


                    ";

        /*
            ----------------------------------------------------
            If duplicate hold
            show My Holds
            ----------------------------------------------------
        */

        if (
            $reason === "duplicate_hold"
        ) {

            echo "

                    <a
                        href='?url=HoldController/myholds'
                        class='hold-primary-btn'
                    >
                        🔖 View My Holds
                    </a>

            ";
        }


        /*
            ----------------------------------------------------
            Back to Books
            ----------------------------------------------------
        */

        echo "

                    <a
                        href='?url=UserController/books'
                        class='hold-secondary-btn'
                    >
                        ← Back to Books
                    </a>

                </div>

            </div>

        </div>

        ";
    }


    /*
        ========================================================
        USER VIEW HOLDS
        ========================================================
    */

    public function myholds()
    {

        AuthMiddleware::check();


        $user =
            $_SESSION["user"]
            ?? null;


        $service =
            new HoldService();


        $data =
            $service->myHolds(
                $user["id"] ?? 0
            );


        /*
            Fallback lookup from distributed nodes
        */

        $distributedService =
            new DistributedQueryService();


        $distributedBooks =
            (array)
            $distributedService
                ->getAllBooks();


        echo "

        <link
            rel='stylesheet'
            href='/assets/css/book.css'
        >


        <div class='my-holds-page'>

            <div class='my-holds-container'>

                <div class='my-holds-header'>

                    <div>

                        <div class='hold-page-badge'>
                            🔖 MY LIBRARY
                        </div>

                        <h1>
                            My Hold Requests
                        </h1>

                        <p>
                            Track books you have requested
                            to reserve.
                        </p>

                    </div>

                    <a
                        href='?url=UserController/dashboard'
                        class='user-back-btn'
                    >
                        ← Dashboard
                    </a>

                </div>

                <div class='my-holds-table-card'>

                    <div class='my-holds-table-wrapper'>

                        <table class='my-holds-table'>

                            <thead>

                                <tr>

                                    <th>Book ID</th>
                                    <th>Book</th>
                                    <th>Author</th>
                                    <th>Category</th>
                                    <th>Status</th>

                                </tr>

                            </thead>

                            <tbody>

        ";


        if (
            $data &&
            $data->num_rows > 0
        ) {

            while (
                $row =
                $data->fetch_assoc()
            ) {

                $globalBookId =
                    $row["global_book_id"]
                    ?? "";


                $title =
                    $row["title"]
                    ?? "";


                $author =
                    $row["author"]
                    ?? "";


                $category =
                    $row["category"]
                    ?? "";


                /*
                    Fallback distributed lookup
                */

                if (
                    empty($title) ||
                    empty($author) ||
                    empty($category)
                ) {

                    foreach (
                        $distributedBooks
                        as $book
                    ) {

                        $bId =
                            $book["global_id"]
                            ?? $book["global_book_id"]
                            ?? $book["book_id"]
                            ?? $book["id"]
                            ?? "";


                        if (
                            (string)$bId ===
                            (string)$globalBookId
                        ) {

                            $title =
                                $title
                                ?: (
                                    $book["title"]
                                    ?? ""
                                );


                            $author =
                                $author
                                ?: (
                                    $book["author"]
                                    ?? ""
                                );


                            $category =
                                $category
                                ?: (
                                    $book["category"]
                                    ?? ""
                                );

                            break;
                        }
                    }
                }


                $status =
                    strtolower(
                        trim(
                            $row["status"]
                            ?? ""
                        )
                    );


                if (
                    $status === "approved"
                ) {

                    $statusClass =
                        "hold-status-approved";

                    $statusIcon =
                        "✓";

                    $statusText =
                        "Approved";

                } elseif (
                    $status === "rejected"
                ) {

                    $statusClass =
                        "hold-status-rejected";

                    $statusIcon =
                        "×";

                    $statusText =
                        "Rejected";

                } else {

                    $statusClass =
                        "hold-status-pending";

                    $statusIcon =
                        "⏳";

                    $statusText =
                        "Pending";
                }


                echo "

                <tr>

                    <td>

                        <span class='hold-book-id'>
                            "
                            . htmlspecialchars(
                                $globalBookId ?: "-"
                            )
                            . "
                        </span>

                    </td>

                    <td>

                        <div class='hold-book-name'>

                            <span>
                                📚
                            </span>

                            <strong>
                                "
                                . htmlspecialchars(
                                    $title ?: "Unknown"
                                )
                                . "
                            </strong>

                        </div>

                    </td>

                    <td>
                        "
                        . htmlspecialchars(
                            $author ?: "Unknown"
                        )
                        . "
                    </td>

                    <td>
                        "
                        . htmlspecialchars(
                            $category ?: "Unknown"
                        )
                        . "
                    </td>

                    <td>

                        <span
                            class='hold-status {$statusClass}'
                        >

                            <span>
                                {$statusIcon}
                            </span>

                            {$statusText}

                        </span>

                    </td>

                </tr>

                ";
            }

        } else {

            echo "

                <tr>

                    <td
                        colspan='5'
                        class='my-holds-empty'
                    >

                        <div>
                            🔖
                        </div>

                        <strong>
                            No Hold Requests
                        </strong>

                        <span>
                            You haven't placed any hold
                            requests yet.
                        </span>

                    </td>

                </tr>

            ";
        }


        echo "

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

        ";
    }


    /*
        ========================================================
        ADMIN VIEW ALL HOLD REQUESTS
        ========================================================
    */

    public function index()
    {

        AuthMiddleware::check();

        AdminMiddleware::check();


        $service =
            new HoldService();


        $data =
            $service->all();


        $distributedService =
            new DistributedQueryService();


        $distributedBooks =
            (array)
            $distributedService
                ->getAllBooks();


        /*
            Create book lookup map
        */

        $bookMap = [];


        foreach (
            $distributedBooks
            as $book
        ) {

            $bookId =
                $book["global_book_id"]
                ?? $book["global_id"]
                ?? $book["book_id"]
                ?? $book["id"]
                ?? null;


            if (
                $bookId !== null
            ) {

                $bookMap[
                    (string)$bookId
                ] = $book;
            }
        }


        echo "

        <link
            rel='stylesheet'
            href='/assets/css/style.css'
        >

        <div style='padding:30px;'>

            <h1>
                Hold Requests
            </h1>

            <a
                href='?url=AdminController/dashboard'
            >
                ← Back Dashboard
            </a>

            <hr>

            <table
                border='1'
                cellpadding='10'
                cellspacing='0'
                width='100%'
            >

            <tr>

                <th>User</th>
                <th>Email</th>
                <th>Book ID</th>
                <th>Title</th>
                <th>Category</th>
                <th>Status</th>
                <th>Action</th>

            </tr>

        ";


        if (
            $data &&
            $data->num_rows > 0
        ) {

            while (
                $row =
                $data->fetch_assoc()
            ) {

                $globalBookId =
                    trim(
                        $row["global_book_id"]
                        ?? ""
                    );


                $title =
                    "Unknown";


                $category =
                    "Unknown";


                if (
                    isset(
                        $bookMap[
                            $globalBookId
                        ]
                    )
                ) {

                    $book =
                        $bookMap[
                            $globalBookId
                        ];


                    $title =
                        $book["title"]
                        ?? "Unknown";


                    $category =
                        $book["category"]
                        ?? "Unknown";
                }


                $userName =
                    htmlspecialchars(
                        $row["name"]
                        ?? "-"
                    );


                $email =
                    htmlspecialchars(
                        $row["email"]
                        ?? "-"
                    );


                $bookId =
                    htmlspecialchars(
                        $globalBookId
                        ?: "-"
                    );


                $title =
                    htmlspecialchars(
                        $title
                    );


                $category =
                    htmlspecialchars(
                        $category
                    );


                $status =
                    htmlspecialchars(
                        $row["status"]
                        ?? "-"
                    );


                $holdId =
                    (int)(
                        $row["id"]
                        ?? 0
                    );


                echo "

                <tr>

                    <td>
                        {$userName}
                    </td>

                    <td>
                        {$email}
                    </td>

                    <td>
                        {$bookId}
                    </td>

                    <td>
                        {$title}
                    </td>

                    <td>
                        {$category}
                    </td>

                    <td>
                        {$status}
                    </td>

                    <td>
                ";


                if (
                    ($row["status"] ?? "")
                    === "pending"
                ) {

                    echo "

                        <a
                            href='?url=HoldController/update/{$holdId}/approved'
                            onclick=\"return confirm('Approve this hold request?')\"
                        >
                            Approve
                        </a>

                        |

                        <a
                            href='?url=HoldController/update/{$holdId}/rejected'
                            onclick=\"return confirm('Reject this hold request?')\"
                        >
                            Reject
                        </a>

                    ";

                } else {

                    echo "

                        <span>
                            No Action
                        </span>

                    ";
                }


                echo "

                    </td>

                </tr>

                ";
            }

        } else {

            echo "

            <tr>

                <td
                    colspan='7'
                    style='text-align:center;'
                >
                    No Hold Requests Found
                </td>

            </tr>

            ";
        }


        echo "

            </table>

        </div>

        ";
    }


    /*
        ========================================================
        ADMIN APPROVE / REJECT
        ========================================================
    */

    public function update(
        $id,
        $status
    ) {

        AuthMiddleware::check();

        AdminMiddleware::check();


        $service =
            new HoldService();


        $service->update(
            $id,
            $status
        );


        header(
            "Location:?url=HoldController/index"
        );

        exit;
    }
}