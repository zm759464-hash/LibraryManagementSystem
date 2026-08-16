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
    ============================================================
    USER CREATE HOLD
    ============================================================

    Business Rule:

        A Hold Request can only exist when:

            available_copies = 0

        Therefore:

            Available 3 copies
                -> Hold NOT allowed

            Available 1 copy
                -> Hold NOT allowed

            Out of stock
                -> Hold allowed

    This repository also prevents:

        pending hold
        approved hold

    from being duplicated for the same user/book.
    ============================================================
    */

    public function create(
        $userId,
        $bookId
    ) {

        /*
        --------------------------------------------------------
        Validate User ID
        --------------------------------------------------------
        */

        if (
            empty($userId) ||
            !is_numeric($userId)
        ) {

            return [
                "success" => false,
                "reason" => "invalid_user"
            ];
        }


        /*
        --------------------------------------------------------
        Validate Global Book ID
        --------------------------------------------------------
        */

        $bookId =
            trim((string)$bookId);


        if ($bookId === "") {

            return [
                "success" => false,
                "reason" => "invalid_book"
            ];
        }



        /*
        ========================================================
        CHECK DUPLICATE ACTIVE HOLD
        ========================================================

        Active statuses:

            pending
            approved

        A user cannot have more than one active
        hold request for the same book.
        ========================================================
        */

        $checkSql = "

            SELECT
                id,
                status

            FROM holds

            WHERE user_id = ?

            AND global_book_id = ?

            AND status IN (
                'pending',
                'approved'
            )

            LIMIT 1

        ";


        $checkStmt =
            $this->db->prepare(
                $checkSql
            );


        if (!$checkStmt) {

            return [
                "success" => false,
                "reason" => "database_error"
            ];
        }


        $checkStmt->bind_param(
            "is",
            $userId,
            $bookId
        );


        if (!$checkStmt->execute()) {

            $checkStmt->close();

            return [
                "success" => false,
                "reason" => "database_error"
            ];
        }


        $existing =
            $checkStmt->get_result();


        /*
        --------------------------------------------------------
        Existing active hold
        --------------------------------------------------------
        */

        if (
            $existing &&
            $existing->num_rows > 0
        ) {

            $checkStmt->close();

            return [
                "success" => false,
                "reason" => "duplicate_hold"
            ];
        }


        $checkStmt->close();



        /*
        ========================================================
        CREATE HOLD REQUEST
        ========================================================
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

            return [
                "success" => false,
                "reason" => "database_error"
            ];
        }


        $stmt->bind_param(
            "is",
            $userId,
            $bookId
        );


        if (
            !$stmt->execute()
        ) {

            $stmt->close();

            return [
                "success" => false,
                "reason" => "database_error"
            ];
        }


        $stmt->close();


        /*
        --------------------------------------------------------
        Successfully created
        --------------------------------------------------------
        */

        return [
            "success" => true,
            "reason" => "created"
        ];
    }



    /*
    ============================================================
    USER VIEW OWN HOLDS
    ============================================================
    */

    public function myHolds(
        $userId
    ) {

        if (
            empty($userId) ||
            !is_numeric($userId)
        ) {

            return false;
        }


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


        if (
            !$stmt->execute()
        ) {

            $stmt->close();

            return false;
        }


        $result =
            $stmt->get_result();


        return $result;
    }



    /*
    ============================================================
    ADMIN VIEW ALL HOLDS
    ============================================================

    Hold information:

        library_main

    Book information:

        Distributed Nodes

        Technology -> library_node1
        Science    -> library_node2
        Fiction    -> library_node3

    The Controller resolves distributed book information.
    ============================================================
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
    ============================================================
    ADMIN APPROVE / REJECT HOLD
    ============================================================
    */

    public function updateStatus(
        $id,
        $status
    ) {

        /*
        --------------------------------------------------------
        Only valid statuses
        --------------------------------------------------------
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
        --------------------------------------------------------
        Only pending requests can be changed
        --------------------------------------------------------
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


        if (
            !$stmt->execute()
        ) {

            $stmt->close();

            return false;
        }


        $affected =
            $stmt->affected_rows;


        $stmt->close();


        return
            $affected > 0;
    }
}