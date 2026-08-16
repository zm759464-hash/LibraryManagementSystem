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
    */

    public function borrow($id)
    {
        AuthMiddleware::check();

        UserMiddleware::check();


        /*
            ----------------------------------------------------
            Check logged-in user
            ----------------------------------------------------
        */

        if (!isset($_SESSION["user"])) {

            header(
                "Location:?url=AuthController/login"
            );

            exit;
        }


        $user =
            $_SESSION["user"];


        /*
            ----------------------------------------------------
            Clean Book ID
            ----------------------------------------------------
        */

        $id =
            trim($id);


        if ($id === "") {

            $error = [
                "title" =>
                    "Borrow Failed",

                "message" =>
                    "Invalid Book ID."
            ];


            require
                __DIR__ .
                "/../Views/user/borrow_error.php";

            return;
        }


        /*
            ====================================================
            Distributed Transaction
            ====================================================
        */

        $transaction =
            new BorrowTransactionService();


        $transactionResult =
            $transaction->borrow(
                $id
            );


        /*
            ====================================================
            Transaction ABORT
            ====================================================
        */

        if (
            !isset(
                $transactionResult["status"]
            ) ||
            $transactionResult["status"] !== "COMMIT"
        ) {

            $message =
                $transactionResult["message"]
                ??
                "Unable to borrow this book.";


            $error = [

                "title" =>
                    "Borrow Failed",

                "message" =>
                    $message,

                "book_id" =>
                    $id
            ];


            require
                __DIR__ .
                "/../Views/user/borrow_error.php";

            return;
        }


        /*
            ====================================================
            Save Borrow History
            ====================================================

            Distributed node transaction succeeded.

            Now save the central borrow history.
        */

        $service =
            new BorrowService();


        $historyResult =
            $service->createHistory(
                $user["id"],
                $id
            );


        /*
            ====================================================
            History Failed
            ====================================================
        */

        if (
            $historyResult === false
        ) {

            $error = [

                "title" =>
                    "Borrow History Failed",

                "message" =>
                    "The book transaction was completed, "
                    . "but the borrow history could not be saved.",

                "book_id" =>
                    $id
            ];


            require
                __DIR__ .
                "/../Views/user/borrow_error.php";

            return;
        }


        /*
            ====================================================
            Borrow Information
            ====================================================
        */

        $borrowDate =
            $historyResult["borrow_date"]
            ??
            date("Y-m-d H:i:s");


        $dueDate =
            $historyResult["due_date"]
            ??
            "";


        $loanDays =
            $historyResult["loan_days"]
            ??
            $service->getBorrowDays();


        /*
            ====================================================
            Successful Borrow
            ====================================================
        */

        $data = [

            "book_id" =>
                $id,

            "borrow_date" =>
                date(
                    "M d, Y",
                    strtotime($borrowDate)
                ),

            "due_date" =>
                (
                    $dueDate !== ""
                    ? date(
                        "M d, Y",
                        strtotime($dueDate)
                    )
                    : "-"
                ),

            "loan_days" =>
                $loanDays
        ];


        /*
            ----------------------------------------------------
            Load success view
            ----------------------------------------------------
        */

        require
            __DIR__ .
            "/../Views/user/borrow_success.php";

        return;
    }


    /*
        ========================================================
        User Borrow History
        ========================================================
    */

    public function history()
    {
        AuthMiddleware::check();

        UserMiddleware::check();


        if (!isset($_SESSION["user"])) {

            header(
                "Location:?url=AuthController/login"
            );

            exit;
        }


        $user =
            $_SESSION["user"];


        $service =
            new BorrowService();


        $data =
            $service->history(
                $user["id"]
            );


        if (!is_array($data)) {

            $data = [];
        }


        $GLOBALS["data"] =
            $data;


        require
            __DIR__ .
            "/../Views/user/borrow_history.php";
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
                <th>Due Date</th>
                <th>Return Date</th>
                <th>Status</th>
            </tr>
        ";


        $found = false;


        foreach ($data as $row) {

            $found = true;


            echo "
                <tr>

                <td>"
                .
                $this->escape(
                    $row["username"] ?? "-"
                )
                .
                "</td>

                <td>"
                .
                $this->escape(
                    $row["email"] ?? "-"
                )
                .
                "</td>

                <td>"
                .
                $this->escape(
                    $row["global_book_id"] ?? "-"
                )
                .
                "</td>

                <td>"
                .
                $this->escape(
                    $row["title"] ?? "-"
                )
                .
                "</td>

                <td>"
                .
                $this->escape(
                    $row["category"] ?? "-"
                )
                .
                "</td>

                <td>"
                .
                $this->escape(
                    $row["node"] ?? "-"
                )
                .
                "</td>

                <td>"
                .
                $this->escape(
                    $row["borrow_date"] ?? "-"
                )
                .
                "</td>

                <td>"
                .
                $this->escape(
                    $row["due_date"] ?? "-"
                )
                .
                "</td>

                <td>"
                .
                $this->escape(
                    $row["return_date"] ?? "-"
                )
                .
                "</td>

                <td>"
                .
                $this->escape(
                    $row["status"] ?? "-"
                )
                .
                "</td>

                </tr>
            ";
        }


        if (!$found) {

            echo "
                <tr>
                    <td colspan='10'>
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
                <th>Due Date</th>
                <th>Status</th>
            </tr>
        ";


        $found = false;


        foreach ($data as $row) {

            if (
                !isset($row["status"]) ||
                $row["status"] !== "borrowed"
            ) {

                continue;
            }


            $found = true;


            echo "
                <tr>

                <td>"
                .
                $this->escape(
                    $row["username"] ?? "-"
                )
                .
                "</td>

                <td>"
                .
                $this->escape(
                    $row["email"] ?? "-"
                )
                .
                "</td>

                <td>"
                .
                $this->escape(
                    $row["global_book_id"] ?? "-"
                )
                .
                "</td>

                <td>"
                .
                $this->escape(
                    $row["title"] ?? "-"
                )
                .
                "</td>

                <td>"
                .
                $this->escape(
                    $row["category"] ?? "-"
                )
                .
                "</td>

                <td>"
                .
                $this->escape(
                    $row["node"] ?? "-"
                )
                .
                "</td>

                <td>"
                .
                $this->escape(
                    $row["borrow_date"] ?? "-"
                )
                .
                "</td>

                <td>"
                .
                $this->escape(
                    $row["due_date"] ?? "-"
                )
                .
                "</td>

                <td>"
                .
                $this->escape(
                    $row["status"] ?? "-"
                )
                .
                "</td>

                </tr>
            ";
        }


        if (!$found) {

            echo "
                <tr>
                    <td colspan='9'>
                        No Active Borrow Requests
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
        HTML Escape Helper
        ========================================================
    */

    private function escape($value)
    {
        return htmlspecialchars(
            (string)$value,
            ENT_QUOTES,
            "UTF-8"
        );
    }
}