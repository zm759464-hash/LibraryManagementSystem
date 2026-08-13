
<?php


require_once
    "../app/Core/Database.php";


class ReturnRepository
{


    private $db;




    public function __construct()
    {


        $database =
            new Database();


        $this->db =
            $database->getConnection();
    }






    /*
        Update Borrow Status

        borrowed
             |
             v
        returned
    */


    public function updateBorrowStatus(
        $bookId,
        $userId
    ) {


        $sql = "
        UPDATE borrow_history
        SET
            status = 'returned',
            return_date = NOW()
        WHERE
            global_book_id = ?
            AND user_id = ?
            AND status = 'borrowed'
        ";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("si", $bookId, $userId);
        $ok = $stmt->execute();

        // Only consider success if a row was actually updated
        $updated = $stmt->affected_rows > 0;

        $stmt->close();

        return ($ok && $updated);
    }







    /*
        User Return History

        Show only returned books
        belonging to current user
    */


    public function getHistory(
        $userId
    ) {


        $sql = "

        SELECT

            borrow_history.id,

            borrow_history.global_book_id,

            borrow_history.borrow_date,

            borrow_history.return_date,

            borrow_history.status

        FROM borrow_history

        WHERE

            borrow_history.user_id = ?

            AND borrow_history.status = 'returned'

        ORDER BY
            borrow_history.id DESC

        ";


        $stmt =
            $this->db->prepare($sql);


        $stmt->bind_param(

            "i",

            $userId

        );


        $stmt->execute();


        return
            $stmt->get_result();
    }
}
