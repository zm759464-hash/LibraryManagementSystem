<?php


require_once
    "../app/Core/Database.php";


class HoldRepository
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
        User Create Hold

        Prevent duplicate active hold requests
    */
    public function create(
        $userId,
        $bookId
    ) {


        /*
            Check existing active hold

            pending / approved
            cannot create another hold
        */

        $checkSql = "

        SELECT
            id,
            status

        FROM holds

        WHERE user_id = ?

        AND global_book_id = ?

        AND status IN ('pending', 'approved')

        LIMIT 1

        ";


        $checkStmt =
            $this->db->prepare(
                $checkSql
            );


        if (!$checkStmt) {

            return false;
        }


        $checkStmt->bind_param(
            "is",
            $userId,
            $bookId
        );


        $checkStmt->execute();


        $existing =
            $checkStmt->get_result();


        if (
            $existing &&
            $existing->num_rows > 0
        ) {

            return false;
        }




        /*
            Create new hold request
        */

        $sql = "

        INSERT INTO holds
        (
            user_id,
            global_book_id,
            status
        )

        VALUES
        (
            ?,
            ?,
            'pending'
        )

        ";


        $stmt =
            $this->db->prepare(
                $sql
            );


        if (!$stmt) {

            return false;
        }


        $stmt->bind_param(
            "is",
            $userId,
            $bookId
        );


        return
            $stmt->execute();
    }




    /*
        User View Own Holds

        Show newest request first
    */
    public function myHolds(
        $userId
    ) {


        $sql = "

        SELECT

            holds.id,

            holds.global_book_id,

            holds.status,

            holds.created_at,

            books.title,

            books.author,

            books.category

        FROM holds

        LEFT JOIN books

            ON books.global_book_id =
            holds.global_book_id

        WHERE holds.user_id = ?

        ORDER BY holds.id DESC

        ";


        $stmt =
            $this->db->prepare(
                $sql
            );


        if (!$stmt) {

            return false;
        }


        $stmt->bind_param(
            "i",
            $userId
        );


        $stmt->execute();


        return
            $stmt->get_result();
    }




    /*
        Admin View All Holds

        Show all hold requests
    */
    /*
    ========================================================
    Admin View All Holds
    ========================================================

    Hold information comes from library_main.

    Book information is resolved separately
    from distributed nodes.
*/
public function all()
{
    $sql = "

    SELECT

        holds.id,

        holds.user_id,

        holds.global_book_id,

        holds.status,

        holds.created_at,

        users.name,

        users.email

    FROM holds

    JOIN users

        ON users.id =
        holds.user_id

    ORDER BY holds.id DESC

    ";

    $result =
        $this->db->query(
            $sql
        );

    return $result;
}




    /*
        Admin Response

        Approve / Reject

        Only pending request can
        be changed
    */
    public function updateStatus(
        $id,
        $status
    ) {


        /*
            Allow only valid status
        */

        if (
            !in_array(
                $status,
                [
                    "approved",
                    "rejected"
                ],
                true
            )
        ) {

            return false;
        }




        /*
            Update only pending request
        */

        $sql = "

        UPDATE holds

        SET
            status = ?

        WHERE id = ?

        AND status = 'pending'

        ";


        $stmt =
            $this->db->prepare(
                $sql
            );


        if (!$stmt) {

            return false;
        }


        $stmt->bind_param(
            "si",
            $status,
            $id
        );


        $stmt->execute();


        return
            $stmt->affected_rows > 0;
    }
}
