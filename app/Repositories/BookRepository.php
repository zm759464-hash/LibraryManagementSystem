<?php

require_once
    __DIR__ . "/../Core/Database.php";

require_once
    __DIR__ . "/../Interfaces/BookRepositoryInterface.php";


class BookRepository implements BookRepositoryInterface
{
    private $db;


    /*
        ========================================================
        Constructor
        ========================================================
    */

    public function __construct()
    {
        $database =
            new Database();


        $this->db =
            $database->getConnection();
    }


    /*
        ========================================================
        Main Database Books
        ========================================================

        Global View
    */

    public function getAllBooks()
    {
        $sql = "
            SELECT *
            FROM books
            ORDER BY id DESC
        ";


        return
            $this->db
            ->query($sql);
    }


    /*
        ========================================================
        Resolve Distributed Node
        ========================================================

        TECH-xxxx -> library_node1
        SCI-xxxx  -> library_node2
        FIC-xxxx  -> library_node3
    */

    private function getNodeDatabase(
        $globalBookId
    ) {
        $globalBookId =
            strtoupper(
                trim($globalBookId)
            );


        if (
            str_starts_with(
                $globalBookId,
                "TECH-"
            )
        ) {
            return "library_node1";
        }


        if (
            str_starts_with(
                $globalBookId,
                "SCI-"
            )
        ) {
            return "library_node2";
        }


        if (
            str_starts_with(
                $globalBookId,
                "FIC-"
            )
        ) {
            return "library_node3";
        }


        return null;
    }


    /*
        ========================================================
        Generate Global Book ID
        ========================================================
    */

    private function generateBookId(
        $category
    ) {
        switch (strtolower(
                trim($category)
            )) {

            case "technology":

                $prefix =
                    "TECH";

                break;


            case "science":

                $prefix =
                    "SCI";

                break;


            case "fiction":

                $prefix =
                    "FIC";

                break;


            default:

                return null;
        }


        /*
            microtime + random number

            This reduces the possibility
            of duplicate Global IDs.
        */

        return
            $prefix .
            "-" .
            str_replace(
                ".",
                "",
                microtime(true)
            ) .
            rand(100, 999);
    }


    /*
        ========================================================
        Create Book
        ========================================================

        Category
            ↓
        Distributed Node

        Technology
            -> library_node1

        Science
            -> library_node2

        Fiction
            -> library_node3
    */

    public function create(
        $data
    ) {
        $title =
            trim(
                $data["title"] ?? ""
            );


        $author =
            trim(
                $data["author"] ?? ""
            );


        $category =
            trim(
                $data["category"] ?? ""
            );


        $available =
            (int)(
                $data["available_copies"] ?? 0
            );


        /*
            Resolve node
        */

        switch ($category) {

            case "Technology":

                $node =
                    "library_node1";

                break;


            case "Science":

                $node =
                    "library_node2";

                break;


            case "Fiction":

                $node =
                    "library_node3";

                break;


            default:

                return false;
        }


        /*
            Generate unique Global ID
        */

        $globalId =
            $this->generateBookId(
                $category
            );


        if (!$globalId) {
            return false;
        }


        /*
            Connect to target node
        */

        $conn =
            $this->getNodeConnection(
                $node
            );


        if (
            $conn->connect_error
        ) {
            return false;
        }


        /*
            Insert Book
        */

        $sql = "
            INSERT INTO books
            (
                global_book_id,
                title,
                author,
                category,
                available_copies
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                ?
            )
        ";


        $stmt =
            $conn->prepare(
                $sql
            );


        if (!$stmt) {

            $conn->close();

            return false;
        }


        $stmt->bind_param(
            "ssssi",
            $globalId,
            $title,
            $author,
            $category,
            $available
        );


        $result =
            $stmt->execute();


        $stmt->close();

        $conn->close();


        return $result;
    }


    /*
        ========================================================
        Update Distributed Book
        ========================================================
    */

    public function update(
        $id,
        $data
    ) {
        $id =
            trim($id);


        $title =
            trim(
                $data["title"] ?? ""
            );


        $author =
            trim(
                $data["author"] ?? ""
            );


        $category =
            trim(
                $data["category"] ?? ""
            );


        $copies =
            (int)(
                $data["available_copies"] ?? 0
            );


        /*
            Find current node
        */

        $node =
            $this->getNodeDatabase(
                $id
            );


        if (!$node) {
            return false;
        }


        /*
            Connect to node
        */

        $conn =
            $this->getNodeConnection(
                $node
            );


        if (
            $conn->connect_error
        ) {
            return false;
        }


        /*
            Update Book
        */

        $sql = "
            UPDATE books

            SET
                title = ?,
                author = ?,
                category = ?,
                available_copies = ?

            WHERE global_book_id = ?
        ";


        $stmt =
            $conn->prepare(
                $sql
            );


        if (!$stmt) {

            $conn->close();

            return false;
        }


        $stmt->bind_param(
            "sssis",
            $title,
            $author,
            $category,
            $copies,
            $id
        );


        $result =
            $stmt->execute();


        $stmt->close();

        $conn->close();


        return $result;
    }


    /*
        ========================================================
        Delete Distributed Book
        ========================================================
    */

    public function delete(
        $id
    ) {
        $id =
            trim($id);


        /*
            Find distributed node
        */

        $node =
            $this->getNodeDatabase(
                $id
            );


        if (!$node) {
            return false;
        }


        /*
            Connect to node
        */

        $conn =
            $this->getNodeConnection(
                $node
            );


        if (
            $conn->connect_error
        ) {
            return false;
        }


        /*
            Delete book
        */

        $sql = "
            DELETE FROM books
            WHERE global_book_id = ?
        ";


        $stmt =
            $conn->prepare(
                $sql
            );


        if (!$stmt) {

            $conn->close();

            return false;
        }


        $stmt->bind_param(
            "s",
            $id
        );


        $stmt->execute();


        $success =
            $stmt->affected_rows > 0;


        $stmt->close();

        $conn->close();


        return $success;
    }


    /*
        ========================================================
        Find Single Book
        ========================================================
    */

    public function findById(
        $id
    ) {
        $id =
            trim($id);


        $node =
            $this->getNodeDatabase(
                $id
            );


        if (!$node) {
            return null;
        }


        $conn =
            $this->getNodeConnection(
                $node
            );


        if (
            $conn->connect_error
        ) {
            return null;
        }


        $stmt =
            $conn->prepare(
                "
                SELECT *
                FROM books
                WHERE global_book_id = ?
                "
            );


        if (!$stmt) {

            $conn->close();

            return null;
        }


        $stmt->bind_param(
            "s",
            $id
        );


        $stmt->execute();


        $book =
            $stmt
            ->get_result()
            ->fetch_assoc();


        $stmt->close();

        $conn->close();


        return $book;
    }


    /*
        ========================================================
        Node Connection
        ========================================================
    */

    private function getNodeConnection(
        $database
    ) {
        return new mysqli(
            "localhost",
            "root",
            "",
            $database
        );
    }


    /*
        ========================================================
        Borrow Book
        ========================================================

        Distributed Node

        available_copies - 1

        Only borrow when:
            available_copies > 0

        This prevents:
            available_copies < 0
    */

    public function borrowBook(
        $globalBookId
    ) {
        $globalBookId =
            trim(
                $globalBookId
            );


        /*
            Resolve correct shard
        */

        $node =
            $this->getNodeDatabase(
                $globalBookId
            );


        if (!$node) {
            return false;
        }


        /*
            Connect to target node
        */

        $conn =
            $this->getNodeConnection(
                $node
            );


        if (
            $conn->connect_error
        ) {
            return false;
        }


        /*
            Start transaction on
            the target node.
        */

        $conn->begin_transaction();


        try {

            /*
                Pessimistic row locking

                SELECT ... FOR UPDATE

                This locks the specific book row
                while checking stock.
            */

            $selectSql = "
                SELECT
                    available_copies

                FROM books

                WHERE global_book_id = ?

                FOR UPDATE
            ";


            $selectStmt =
                $conn->prepare(
                    $selectSql
                );


            if (!$selectStmt) {

                $conn->rollback();

                $conn->close();

                return false;
            }


            $selectStmt->bind_param(
                "s",
                $globalBookId
            );


            $selectStmt->execute();


            $result =
                $selectStmt
                ->get_result();


            $book =
                $result
                ->fetch_assoc();


            $selectStmt->close();


            /*
                Book does not exist
            */

            if (!$book) {

                $conn->rollback();

                $conn->close();

                return false;
            }


            /*
                No available copy
            */

            if (
                (int)$book["available_copies"] <= 0
            ) {

                $conn->rollback();

                $conn->close();

                return false;
            }


            /*
                Update stock
            */

            $updateSql = "
                UPDATE books

                SET
                    available_copies =
                    available_copies - 1

                WHERE global_book_id = ?
            ";


            $updateStmt =
                $conn->prepare(
                    $updateSql
                );


            if (!$updateStmt) {

                $conn->rollback();

                $conn->close();

                return false;
            }


            $updateStmt->bind_param(
                "s",
                $globalBookId
            );


            $updateStmt->execute();


            /*
                Make sure row was updated
            */

            if (
                $updateStmt->affected_rows <= 0
            ) {

                $updateStmt->close();

                $conn->rollback();

                $conn->close();

                return false;
            }


            $updateStmt->close();


            /*
                Commit node transaction
            */

            $conn->commit();


            $conn->close();


            return true;
        } catch (
            Throwable $e
        ) {

            /*
                Rollback if anything fails
            */

            $conn->rollback();

            $conn->close();


            return false;
        }
    }


    /*
        ========================================================
        Return Book
        ========================================================
    */

    public function returnBook(
        $globalBookId
    ) {
        $globalBookId =
            trim(
                $globalBookId
            );


        /*
            Find target node
        */

        $node =
            $this->getNodeDatabase(
                $globalBookId
            );


        if (!$node) {
            return false;
        }


        /*
            Connect
        */

        $conn =
            $this->getNodeConnection(
                $node
            );


        if (
            $conn->connect_error
        ) {
            return false;
        }


        /*
            Increase available copies
        */

        $sql = "
            UPDATE books

            SET
                available_copies =
                available_copies + 1

            WHERE global_book_id = ?
        ";


        $stmt =
            $conn->prepare(
                $sql
            );


        if (!$stmt) {

            $conn->close();

            return false;
        }


        $stmt->bind_param(
            "s",
            $globalBookId
        );


        $stmt->execute();


        $success =
            $stmt->affected_rows > 0;


        $stmt->close();

        $conn->close();


        return $success;
    }


    /*
        ========================================================
        Resolve Node By Category
        ========================================================
    */

    private function resolveNode(
        $category
    ) {
        switch (strtolower(
                trim($category)
            )) {

            case "technology":

                return "library_node1";


            case "science":

                return "library_node2";


            case "fiction":

                return "library_node3";


            default:

                return null;
        }
    }
}
