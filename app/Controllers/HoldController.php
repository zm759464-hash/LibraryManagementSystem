<?php

require_once "../app/Services/HoldService.php";
require_once "../app/Services/DistributedQueryService.php";
require_once "../app/Middleware/AuthMiddleware.php";
require_once "../app/Middleware/AdminMiddleware.php";


class HoldController
{

    /*
        User Create Hold Request
    */
    public function create($bookId)
    {
        AuthMiddleware::check();

        $user = $_SESSION["user"] ?? null;

        $service = new HoldService();

        $result = $service->create(
            $user["id"],
            $bookId
        );

        if ($result) {
            echo "
        <h2>
        Hold Request Sent
        </h2>

        <p>
        Your hold request has been submitted successfully.
        </p>

        <a href='?url=HoldController/myholds'>
        My Holds
        </a>

        <br><br>

        <a href='?url=UserController/books'>
        Back Books
        </a>

        ";
        } else {
            echo "
        <h2>
        Hold Request Not Sent
        </h2>

        <p>
        You already have an active hold request for this book.
        </p>

        <a href='?url=HoldController/myholds'>
        My Holds
        </a>

        <br><br>

        <a href='?url=UserController/books'>
        Back Books
        </a>

        ";
        }
    }


    /*
        User View Holds
    */
    public function myholds()
    {
        AuthMiddleware::check();

        $user = $_SESSION["user"] ?? null;

        $service = new HoldService();
        $data = $service->myHolds($user["id"] ?? 0);

        // fallback lookup from distributed nodes
        $distributedService = new DistributedQueryService();
        $distributedBooks = (array) $distributedService->getAllBooks();

        echo "
        <h1>
        My Hold Requests
        </h1>

        <a href='?url=UserController/dashboard'>
        Back Dashboard
        </a>

        <hr>

        <table border='1' cellpadding='10' cellspacing='0'>
        <tr>
            <th>Book ID</th>
            <th>Title</th>
            <th>Author</th>
            <th>Category</th>
            <th>Status</th>
        </tr>
        ";

        if ($data && $data->num_rows > 0) {
            while ($row = $data->fetch_assoc()) {
                $globalBookId = $row['global_book_id'] ?? '';

                $title = $row['title'] ?? '';
                $author = $row['author'] ?? '';
                $category = $row['category'] ?? '';

                if (empty($title) || empty($author) || empty($category)) {
                    foreach ($distributedBooks as $b) {
                        $bId = $b['global_id'] ?? $b['global_book_id'] ?? $b['book_id'] ?? $b['id'] ?? '';
                        if ((string)$bId === (string)$globalBookId) {
                            $title = $title ?: ($b['title'] ?? '');
                            $author = $author ?: ($b['author'] ?? '');
                            $category = $category ?: ($b['category'] ?? '');
                            break;
                        }
                    }
                }

                echo "
                <tr>
                    <td>" . htmlspecialchars($globalBookId ?: '-') . "</td>
                    <td>" . htmlspecialchars($title ?: 'Unknown') . "</td>
                    <td>" . htmlspecialchars($author ?: 'Unknown') . "</td>
                    <td>" . htmlspecialchars($category ?: 'Unknown') . "</td>
                    <td>" . htmlspecialchars($row['status'] ?? '-') . "</td>
                </tr>
                ";
            }
        } else {
            echo "
            <tr>
                <td colspan='5'>No Hold Requests Found</td>
            </tr>
            ";
        }

        echo "
        </table>
        ";
    }


    /*
        Admin View All Hold Requests
    */
    /*
    ========================================================
    Admin View All Hold Requests
    ========================================================

    Hold data:
        library_main

    Book data:
        Distributed Nodes
        Technology -> library_node1
        Science    -> library_node2
        Fiction    -> library_node3
*/
public function index()
{
    AuthMiddleware::check();

    AdminMiddleware::check();

    $service =
        new HoldService();

    $data =
        $service->all();


    /*
        Get all books from distributed nodes

        This is the source of truth for:

            title
            author
            category
            available_copies
    */
    $distributedService =
        new DistributedQueryService();

    $distributedBooks =
        (array) $distributedService->getAllBooks();


    /*
        Create quick lookup table

        Example:

        $bookMap["TECH-1786154906"]
        $bookMap["SCI-1786101455"]
        $bookMap["FIC-1786101504"]
    */

    $bookMap = [];


    foreach ($distributedBooks as $book) {

        $bookId =
            $book["global_book_id"]
            ?? $book["global_id"]
            ?? $book["book_id"]
            ?? $book["id"]
            ?? null;


        if ($bookId !== null) {

            $bookMap[
                (string) $bookId
            ] = $book;
        }
    }


    echo "

    <link rel='stylesheet'
          href='/assets/css/style.css'>


    <div style='padding:30px;'>


        <h1>
            Hold Requests
        </h1>


        <a href='?url=AdminController/dashboard'>
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


            /*
                Hold Book ID
            */

            $globalBookId =
                trim(
                    $row["global_book_id"]
                    ?? ""
                );


            /*
                Default values
            */

            $title =
                "Unknown";

            $category =
                "Unknown";


            /*
                Resolve book from
                distributed nodes
            */

            if (
                isset(
                    $bookMap[$globalBookId]
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


            /*
                Escape output
            */

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


            /*
                Pending request
            */

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
        Admin Approve / Reject Hold
    */
    public function update($id, $status)
    {
        AuthMiddleware::check();
        AdminMiddleware::check();

        $service = new HoldService();
        $ok = $service->update($id, $status);

        header("Location:?url=HoldController/index");
        exit;
    }
}
