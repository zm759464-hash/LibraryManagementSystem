
<?php


require_once
    "../app/Services/ReturnService.php";


require_once
    "../app/Middleware/AuthMiddleware.php";


require_once
    "../app/Middleware/UserMiddleware.php";


class ReturnController
{


    public function returnBook($id)
    {


        AuthMiddleware::check();


        UserMiddleware::check();


        $user =
            $_SESSION["user"];


        $service =
            new ReturnService();


        $result =
            $service->returnBook(

                $id,

                $user["id"]

            );


        if ($result) {
            $_SESSION['flash_success'] = 'Book returned successfully.';
        } else {
            $_SESSION['flash_error'] = 'Return failed. Please try again.';
        }

        header('Location:?url=BorrowController/history');
        exit;
    }







    /*
        User Return History
    */


    public function history()
    {


        AuthMiddleware::check();


        UserMiddleware::check();


        $user =
            $_SESSION["user"];


        $service =
            new ReturnService();


        $history =
            $service->history(

                $user["id"]

            );


        echo "

        <h1>
        Return History
        </h1>


        <a href='?url=UserController/dashboard'>
        Back Dashboard
        </a>


        <hr>


        <table border='1'
        cellpadding='10'>


        <tr>

        <th>
        Book ID
        </th>


        <th>
        Borrow Date
        </th>


        <th>
        Return Date
        </th>


        <th>
        Status
        </th>

        </tr>

        ";


        if (
            $history &&
            $history->num_rows > 0
        ) {


            while (
                $row =
                $history->fetch_assoc()
            ) {


                echo "

                <tr>

                <td>
                {$row['global_book_id']}
                </td>


                <td>
                {$row['borrow_date']}
                </td>


                <td>
                {$row['return_date']}
                </td>


                <td>
                {$row['status']}
                </td>

                </tr>

                ";
            }
        } else {


            echo "

            <tr>

            <td colspan='4'>
            No Return History
            </td>

            </tr>

            ";
        }


        echo "

        </table>

        ";
    }
}
