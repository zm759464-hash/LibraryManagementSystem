<?php

require_once
    __DIR__ . "/../Core/Database.php";

require_once
    __DIR__ . "/../Interfaces/BookRepositoryInterface.php";

require_once
    __DIR__ . "/../Infrastructure/Distributed/NodeManager.php";


class BookRepository implements BookRepositoryInterface
{
    private $db;

    private $nodeManager;


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


        $this->nodeManager =
            new NodeManager();
    }


    /*
        ========================================================
        Get All Books
        ========================================================
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

        switch (
            strtolower(
                trim($category)
            )
        ) {

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


        return
            $prefix
            . "-"
            . str_replace(
                ".",
                "",
                microtime(true)
            )
            . rand(
                100,
                999
            );
    }


    /*
        ========================================================
        Create Book
        ========================================================
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
                $data["available_copies"]
                ?? 0
            );


        /*
            ----------------------------------------------------
            Resolve node
            ----------------------------------------------------
        */

        $node =
            $this->resolveNode(
                $category
            );


        if (!$node) {

            return false;
        }


        /*
            ----------------------------------------------------
            Generate Global ID
            ----------------------------------------------------
        */

        $globalId =
            $this->generateBookId(
                $category
            );


        if (!$globalId) {

            return false;
        }


        /*
            ----------------------------------------------------
            Connect target node
            ----------------------------------------------------
        */

        try {

            $conn =
                $this->getNodeConnection(
                    $node
                );

        } catch (
            Throwable $e
        ) {

            error_log(
                "BookRepository::create node error: "
                . $e->getMessage()
            );

            return false;
        }


        if (
            !$conn ||
            $conn->connect_error
        ) {

            return false;
        }


        /*
            ----------------------------------------------------
            Insert
            ----------------------------------------------------
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
            $conn
            ->prepare($sql);


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


        if (!$result) {

            error_log(
                "BookRepository::create error: "
                . $stmt->error
            );
        }


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
            strtoupper(
                trim($id)
            );


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
                $data["available_copies"]
                ?? 0
            );


        /*
            ----------------------------------------------------
            Resolve shard
            ----------------------------------------------------
        */

        $node =
            $this->getNodeDatabase(
                $id
            );


        if (!$node) {

            return false;
        }


        /*
            ----------------------------------------------------
            Validate category against shard
            ----------------------------------------------------
        */

        $expectedCategory =
            $this->getExpectedCategory(
                $id
            );


        if (
            $expectedCategory === null
            ||
            strcasecmp(
                $category,
                $expectedCategory
            ) !== 0
        ) {

            return false;
        }


        /*
            ----------------------------------------------------
            Connect
            ----------------------------------------------------
        */

        try {

            $conn =
                $this->getNodeConnection(
                    $node
                );

        } catch (
            Throwable $e
        ) {

            return false;
        }


        if (
            !$conn ||
            $conn->connect_error
        ) {

            return false;
        }


        /*
            ----------------------------------------------------
            Update
            ----------------------------------------------------
        */

        $sql = "
            UPDATE books

            SET
                title = ?,
                author = ?,
                category = ?,
                available_copies = ?

            WHERE
                global_book_id = ?
        ";


        $stmt =
            $conn
            ->prepare($sql);


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
            strtoupper(
                trim($id)
            );


        $node =
            $this->getNodeDatabase(
                $id
            );


        if (!$node) {

            return false;
        }


        try {

            $conn =
                $this->getNodeConnection(
                    $node
                );

        } catch (
            Throwable $e
        ) {

            return false;
        }


        if (
            !$conn ||
            $conn->connect_error
        ) {

            return false;
        }


        $sql = "
            DELETE FROM books
            WHERE global_book_id = ?
        ";


        $stmt =
            $conn
            ->prepare($sql);


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
            strtoupper(
                trim($id)
            );


        $node =
            $this->getNodeDatabase(
                $id
            );


        if (!$node) {

            return null;
        }


        try {

            $conn =
                $this->getNodeConnection(
                    $node
                );

        } catch (
            Throwable $e
        ) {

            return null;
        }


        if (
            !$conn ||
            $conn->connect_error
        ) {

            return null;
        }


        $sql = "
            SELECT *
            FROM books
            WHERE global_book_id = ?
            LIMIT 1
        ";


        $stmt =
            $conn
            ->prepare($sql);


        if (!$stmt) {

            $conn->close();

            return null;
        }


        $stmt->bind_param(
            "s",
            $id
        );


        $stmt->execute();


        $result =
            $stmt->get_result();


        $book =
            $result->fetch_assoc();


        $stmt->close();

        $conn->close();


        return $book ?: null;
    }


    /*
        ========================================================
        Borrow Book
        ========================================================

        Pessimistic Lock

        SELECT ... FOR UPDATE

        available_copies > 0 ဖြစ်မှသာ borrow ခွင့်ပြုမယ်။
    */

    public function borrowBook(
        $globalBookId
    ) {

        $globalBookId =
            strtoupper(
                trim($globalBookId)
            );


        $node =
            $this->getNodeDatabase(
                $globalBookId
            );


        if (!$node) {

            return false;
        }


        try {

            $conn =
                $this->getNodeConnection(
                    $node
                );

        } catch (
            Throwable $e
        ) {

            return false;
        }


        if (
            !$conn ||
            $conn->connect_error
        ) {

            return false;
        }


        /*
            ----------------------------------------------------
            Start transaction
            ----------------------------------------------------
        */

        $conn->begin_transaction();


        try {

            /*
                ------------------------------------------------
                Lock book row
                ------------------------------------------------
            */

            $selectSql = "
                SELECT
                    available_copies

                FROM books

                WHERE
                    global_book_id = ?

                FOR UPDATE
            ";


            $selectStmt =
                $conn
                ->prepare($selectSql);


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
                ------------------------------------------------
                Book not found
                ------------------------------------------------
            */

            if (!$book) {

                $conn->rollback();

                $conn->close();

                return false;
            }


            /*
                ------------------------------------------------
                No stock
                ------------------------------------------------
            */

            if (
                (int)$book["available_copies"] <= 0
            ) {

                $conn->rollback();

                $conn->close();

                return false;
            }


            /*
                ------------------------------------------------
                Decrease stock
                ------------------------------------------------
            */

            $updateSql = "
                UPDATE books

                SET
                    available_copies =
                    available_copies - 1

                WHERE
                    global_book_id = ?
            ";


            $updateStmt =
                $conn
                ->prepare($updateSql);


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
                ------------------------------------------------
                Commit node transaction
                ------------------------------------------------
            */

            $conn->commit();

            $conn->close();


            return true;


        } catch (
            Throwable $e
        ) {

            $conn->rollback();

            $conn->close();


            error_log(
                "BookRepository::borrowBook error: "
                . $e->getMessage()
            );


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
            strtoupper(
                trim($globalBookId)
            );


        $node =
            $this->getNodeDatabase(
                $globalBookId
            );


        if (!$node) {

            return false;
        }


        try {

            $conn =
                $this->getNodeConnection(
                    $node
                );

        } catch (
            Throwable $e
        ) {

            return false;
        }


        if (
            !$conn ||
            $conn->connect_error
        ) {

            return false;
        }


        $sql = "
            UPDATE books

            SET
                available_copies =
                available_copies + 1

            WHERE
                global_book_id = ?
        ";


        $stmt =
            $conn
            ->prepare($sql);


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

        switch (
            strtolower(
                trim($category)
            )
        ) {

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


    /*
        ========================================================
        Get Expected Category
        ========================================================
    */

    private function getExpectedCategory(
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

            return "Technology";
        }


        if (
            str_starts_with(
                $globalBookId,
                "SCI-"
            )
        ) {

            return "Science";
        }


        if (
            str_starts_with(
                $globalBookId,
                "FIC-"
            )
        ) {

            return "Fiction";
        }


        return null;
    }


    /*
        ========================================================
        Get Node Connection
        ========================================================
    */

    private function getNodeConnection(
        $database
    ) {

        $nodes =
            $this->nodeManager
            ->getAllNodes();


        foreach (
            $nodes as $node
        ) {

            if (
                isset(
                    $node["database"]
                ) &&
                $node["database"] === $database
            ) {

                return
                    $this->nodeManager
                    ->connect(
                        $node
                    );
            }
        }


        throw new Exception(
            "Unknown distributed node: "
            . $database
        );
    }
}