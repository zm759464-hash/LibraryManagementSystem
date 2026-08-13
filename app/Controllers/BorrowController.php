<?php

require_once
    __DIR__ . "/../Services/BorrowTransactionService.php";

require_once
    __DIR__ . "/../Services/BorrowService.php";

require_once
    __DIR__ . "/../Middleware/AuthMiddleware.php";

require_once
    __DIR__ . "/../Middleware/UserMiddleware.php";




class BorrowController
{
    /*
        ========================================================
        User Borrow Book
        ========================================================

        Distributed Transaction
        Two Phase Commit based flow.
    */

    public function borrow($id)
    {
        AuthMiddleware::check();

        UserMiddleware::check();


        /*
            Check logged-in user
        */

        if (
            !isset($_SESSION["user"])
        ) {

            header(
                "Location:?url=AuthController/login"
            );

            exit;
        }


        $user =
            $_SESSION["user"];


        /*
            Clean Global Book ID
        */

        $id =
            trim($id);


        if ($id === "") {

            echo "
                <h2>Borrow Failed</h2>

                <p>
                    Invalid Book ID.
                </p>

                <br>

                <a href='?url=UserController/books'>
                    Back Books
                </a>
            ";

            return;
        }


        /*
            ====================================================
            Distributed Transaction
            ====================================================
        */

        $transaction =
            new BorrowTransactionService();


        $result =
            $transaction->borrow($id);


        /*
            ====================================================
            COMMIT
            ====================================================
        */

        if (
            isset($result["status"]) &&
            $result["status"] === "COMMIT"
        ) {

            /*
                Save Borrow History
            */

            $service =
                new BorrowService();


            $historyResult =
                $service->createHistory(
                    $user["id"],
                    $id
                );


            /*
                History failed
            */

            if (!$historyResult) {

                echo "
                    <h2>
                        Borrow History Failed
                    </h2>

                    <hr>

                    <p>
                        Book stock was committed,
                        but borrow history could not
                        be saved.
                    </p>

                    <p>
                        Book ID:
                        " .
                    htmlspecialchars(
                        $id,
                        ENT_QUOTES,
                        "UTF-8"
                    ) .
                    "
                    </p>

                    <br>

                    <a href='?url=UserController/books'>
                        Back Books
                    </a>
                ";

                return;
            }


            /*
                Successful
            */

            echo "
                <h2>
                    Borrow Successful
                </h2>

                <hr>

                <h3>
                    Transaction Status:
                    COMMIT
                </h3>

                <p>
                    Book ID:
                    " .
                htmlspecialchars(
                    $id,
                    ENT_QUOTES,
                    "UTF-8"
                ) .
                "
                </p>

                <br>

                <a href='?url=UserController/books'>
                    Back Books
                </a>
            ";
        } else {

            /*
                =================================================
                ABORT
                =================================================
            */

            $message =
                isset($result["message"])
                ? $result["message"]
                : "Unknown transaction error";


            echo "
                <h2>
                    Borrow Failed
                </h2>

                <hr>

                <h3>
                    Transaction Status:
                    ABORT
                </h3>

                <p>
                    " .
                htmlspecialchars(
                    $message,
                    ENT_QUOTES,
                    "UTF-8"
                ) .
                "
                </p>

                <p>
                    Book ID:
                    " .
                htmlspecialchars(
                    $id,
                    ENT_QUOTES,
                    "UTF-8"
                ) .
                "
                </p>

                <br>

                <a href='?url=UserController/books'>
                    Back Books
                </a>
            ";
        }
    }


    /*
        ========================================================
        User Borrow History
        ========================================================
    */

    /*
        ========================================================
        User Borrow History
        ========================================================
    */

    public function history()
    {
        AuthMiddleware::check();

        UserMiddleware::check();


        if (
            !isset($_SESSION["user"])
        ) {

            header(
                "Location:?url=AuthController/login"
            );

            exit;
        }


        $user =
            $_SESSION["user"];


        $service =
            new BorrowService();


        /*
            IMPORTANT:
            Use $data consistently.
        */

        $data =
            $service->history(
                $user["id"]
            );


        /*
            Prevent foreach warning
        */

        if (!is_array($data)) {
            $data = [];
        }


        /*
            ========================================================
            Load Dedicated View File with Explicit Data Variable
            ========================================================
            Controller ထဲတွင် HTML များ စုပြုံရေးသားခြင်းကို ဖျက်ပစ်ပြီး 
            ၎င်းနှင့် သက်ဆိုင်သော သီးသန့် View ဖိုင်ကိုသာ ခေါ်ယူအသုံးပြုစေခြင်း။
            
            Intelophense Warning ကျော်ရန် $data အား ပြန်လည်သက်မှတ်ပေးခြင်း။
        */

        $GLOBALS['data'] = $data;
        require
            __DIR__ . "/../Views/user/borrow_history.php";
    }




    /*
        ========================================================
        Admin View All Borrow History
        ========================================================
    */

    public function all()
    {
        AuthMiddleware::check();


        $service =
            new BorrowService();


        $data =
            $service->all();


        if (!is_array($data)) {
            $data = [];
        }


        echo "
            <h1>
                All Borrow History
            </h1>

            <br>

            <a href='?url=AdminController/dashboard'>
                Back Dashboard
            </a>

            <hr>

            <table
                border='1'
                cellpadding='10'
            >

            <tr>
                <th>User</th>
                <th>Email</th>
                <th>Book ID</th>
                <th>Title</th>
                <th>Category</th>
                <th>Node</th>
                <th>Borrow Date</th>
                <th>Return Date</th>
                <th>Status</th>
            </tr>
        ";


        $found = false;


        foreach ($data as $row) {

            $found = true;


            echo "
                <tr>

                <td>
                    " .
                (
                    !empty($row["username"])
                    ? htmlspecialchars(
                        $row["username"],
                        ENT_QUOTES,
                        "UTF-8"
                    )
                    : "-"
                ) .
                "
                </td>

                <td>
                    " .
                (
                    !empty($row["email"])
                    ? htmlspecialchars(
                        $row["email"],
                        ENT_QUOTES,
                        "UTF-8"
                    )
                    : "-"
                ) .
                "
                </td>

                <td>
                    " .
                (
                    !empty($row["global_book_id"])
                    ? htmlspecialchars(
                        $row["global_book_id"],
                        ENT_QUOTES,
                        "UTF-8"
                    )
                    : "-"
                ) .
                "
                </td>

                <td>
                    " .
                (
                    !empty($row["title"])
                    ? htmlspecialchars(
                        $row["title"],
                        ENT_QUOTES,
                        "UTF-8"
                    )
                    : "-"
                ) .
                "
                </td>

                <td>
                    " .
                (
                    !empty($row["category"])
                    ? htmlspecialchars(
                        $row["category"],
                        ENT_QUOTES,
                        "UTF-8"
                    )
                    : "-"
                ) .
                "
                </td>

                <td>
                    " .
                (
                    !empty($row["node"])
                    ? htmlspecialchars(
                        $row["node"],
                        ENT_QUOTES,
                        "UTF-8"
                    )
                    : "-"
                ) .
                "
                </td>

                <td>
                    " .
                (
                    !empty($row["borrow_date"])
                    ? htmlspecialchars(
                        $row["borrow_date"],
                        ENT_QUOTES,
                        "UTF-8"
                    )
                    : "-"
                ) .
                "
                </td>

                <td>
                    " .
                (
                    !empty($row["return_date"])
                    ? htmlspecialchars(
                        $row["return_date"],
                        ENT_QUOTES,
                        "UTF-8"
                    )
                    : "-"
                ) .
                "
                </td>

                <td>
                    " .
                (
                    !empty($row["status"])
                    ? htmlspecialchars(
                        $row["status"],
                        ENT_QUOTES,
                        "UTF-8"
                    )
                    : "-"
                ) .
                "
                </td>

                </tr>
            ";
        }


        if (!$found) {

            echo "
                <tr>
                    <td colspan='9'>
                        No Borrow History
                    </td>
                </tr>
            ";
        }


        echo "
            </table>
        ";
    }


    /*
        ========================================================
        Admin Pending / Active Borrow Requests
        ========================================================
    */

    public function pending()
    {
        AuthMiddleware::check();


        $service =
            new BorrowService();


        $data =
            $service->all();


        if (!is_array($data)) {
            $data = [];
        }


        echo "
            <h1>
                Borrow Requests
            </h1>

            <p>
                Currently borrowed books
            </p>

            <a href='?url=AdminController/dashboard'>
                Back Dashboard
            </a>

            <hr>

            <table
                border='1'
                cellpadding='10'
            >

            <tr>
                <th>User</th>
                <th>Email</th>
                <th>Book ID</th>
                <th>Title</th>
                <th>Category</th>
                <th>Node</th>
                <th>Borrow Date</th>
                <th>Status</th>
            </tr>
        ";


        $found = false;


        foreach ($data as $row) {

            /*
                Show only active borrowed books
            */

            if (
                !isset($row["status"]) ||
                $row["status"] !== "borrowed"
            ) {

                continue;
            }


            $found = true;


            echo "
                <tr>

                <td>
                    " .
                (
                    !empty($row["username"])
                    ? htmlspecialchars(
                        $row["username"],
                        ENT_QUOTES,
                        "UTF-8"
                    )
                    : "-"
                ) .
                "
                </td>

                <td>
                    " .
                (
                    !empty($row["email"])
                    ? htmlspecialchars(
                        $row["email"],
                        ENT_QUOTES,
                        "UTF-8"
                    )
                    : "-"
                ) .
                "
                </td>

                <td>
                    " .
                (
                    !empty($row["global_book_id"])
                    ? htmlspecialchars(
                        $row["global_book_id"],
                        ENT_QUOTES,
                        "UTF-8"
                    )
                    : "-"
                ) .
                "
                </td>

                <td>
                    " .
                (
                    !empty($row["title"])
                    ? htmlspecialchars(
                        $row["title"],
                        ENT_QUOTES,
                        "UTF-8"
                    )
                    : "-"
                ) .
                "
                </td>

                <td>
                    " .
                (
                    !empty($row["category"])
                    ? htmlspecialchars(
                        $row["category"],
                        ENT_QUOTES,
                        "UTF-8"
                    )
                    : "-"
                ) .
                "
                </td>

                <td>
                    " .
                (
                    !empty($row["node"])
                    ? htmlspecialchars(
                        $row["node"],
                        ENT_QUOTES,
                        "UTF-8"
                    )
                    : "-"
                ) .
                "
                </td>

                <td>
                    " .
                (
                    !empty($row["borrow_date"])
                    ? htmlspecialchars(
                        $row["borrow_date"],
                        ENT_QUOTES,
                        "UTF-8"
                    )
                    : "-"
                ) .
                "
                </td>

                <td>
                    " .
                (
                    !empty($row["status"])
                    ? htmlspecialchars(
                        $row["status"],
                        ENT_QUOTES,
                        "UTF-8"
                    )
                    : "-"
                ) .
                "
                </td>

                </tr>
            ";
        }


        if (!$found) {

            echo "
                <tr>
                    <td colspan='8'>
                        No Active Borrow Requests
                    </td>
                </tr>
            ";
        }


        echo "
            </table>
        ";
    }
}
