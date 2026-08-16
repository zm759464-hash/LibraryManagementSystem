<?php

require_once
    __DIR__ . "/../Core/Database.php";


class BorrowRepository
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
        Transaction Control
        ========================================================
    */

    public function beginTransaction()
    {
        return
            $this->db
            ->begin_transaction();
    }


    public function commit()
    {
        return
            $this->db
            ->commit();
    }


    public function rollback()
    {
        return
            $this->db
            ->rollback();
    }


    /*
        ========================================================
        Get Borrow Settings
        ========================================================
    */

    public function getBorrowDays()
    {
        $sql = "
            SELECT
                borrow_days

            FROM borrow_settings

            WHERE id = 1

            LIMIT 1
        ";


        $stmt =
            $this->db
            ->prepare($sql);


        if (!$stmt) {

            return 5;
        }


        $stmt->execute();


        $result =
            $stmt->get_result();


        $row =
            $result->fetch_assoc();


        $stmt->close();


        $days =
            (int)(
                $row["borrow_days"]
                ?? 5
            );


        if ($days < 1) {

            $days = 5;
        }


        if ($days > 365) {

            $days = 365;
        }


        return $days;
    }


    /*
        ========================================================
        Update Borrow Settings
        ========================================================
    */

    public function updateBorrowDays(
        $days
    ) {

        $days =
            (int)$days;


        if (
            $days < 1 ||
            $days > 365
        ) {

            return false;
        }


        /*
            ----------------------------------------------------
            Check whether row exists
            ----------------------------------------------------
        */

        $checkSql = "
            SELECT
                id

            FROM borrow_settings

            WHERE id = 1

            LIMIT 1
        ";


        $check =
            $this->db
            ->query($checkSql);


        if (
            !$check
        ) {

            return false;
        }


        /*
            ----------------------------------------------------
            Insert if row does not exist
            ----------------------------------------------------
        */

        if (
            $check->num_rows === 0
        ) {

            $insertSql = "
                INSERT INTO borrow_settings
                (
                    id,
                    borrow_days
                )
                VALUES
                (
                    1,
                    ?
                )
            ";


            $stmt =
                $this->db
                ->prepare($insertSql);


            if (!$stmt) {

                return false;
            }


            $stmt->bind_param(
                "i",
                $days
            );


            $result =
                $stmt->execute();


            $stmt->close();


            return $result;
        }


        /*
            ----------------------------------------------------
            Update existing setting
            ----------------------------------------------------
        */

        $sql = "
            UPDATE borrow_settings

            SET
                borrow_days = ?

            WHERE id = 1
        ";


        $stmt =
            $this->db
            ->prepare($sql);


        if (!$stmt) {

            return false;
        }


        $stmt->bind_param(
            "i",
            $days
        );


        $result =
            $stmt->execute();


        $stmt->close();


        return $result;
    }


    /*
        ========================================================
        Insert Borrow History
        ========================================================
    */

    public function createHistory(
        $userId,
        $bookId,
        $borrowDate = null,
        $dueDate = null
    ) {

        if (
            empty($userId) ||
            !is_numeric($userId)
        ) {

            return false;
        }


        $bookId =
            trim($bookId);


        if ($bookId === "") {

            return false;
        }


        /*
            ----------------------------------------------------
            Explicit dates
            ----------------------------------------------------
        */

        if (
            !empty($borrowDate) &&
            !empty($dueDate)
        ) {

            $sql = "
                INSERT INTO borrow_history
                (
                    user_id,
                    global_book_id,
                    borrow_date,
                    due_date,
                    status
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    'borrowed'
                )
            ";


            $stmt =
                $this->db
                ->prepare($sql);


            if (!$stmt) {

                return false;
            }


            $stmt->bind_param(
                "isss",
                $userId,
                $bookId,
                $borrowDate,
                $dueDate
            );


            $result =
                $stmt->execute();


            if (!$result) {

                error_log(
                    "BorrowRepository::createHistory error: "
                    . $stmt->error
                );
            }


            $stmt->close();


            return $result;
        }


        /*
            ----------------------------------------------------
            Database-driven fallback
            ----------------------------------------------------
        */

        $borrowDays =
            $this->getBorrowDays();


        $sql = "
            INSERT INTO borrow_history
            (
                user_id,
                global_book_id,
                borrow_date,
                due_date,
                status
            )
            VALUES
            (
                ?,
                ?,
                NOW(),
                DATE_ADD(
                    NOW(),
                    INTERVAL {$borrowDays} DAY
                ),
                'borrowed'
            )
        ";


        $stmt =
            $this->db
            ->prepare($sql);


        if (!$stmt) {

            return false;
        }


        $stmt->bind_param(
            "is",
            $userId,
            $bookId
        );


        $result =
            $stmt->execute();


        $stmt->close();


        return $result;
    }


    /*
        ========================================================
        User Borrow History
        ========================================================
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

                borrow_history.due_date,

                borrow_history.status,

                users.name AS username

            FROM borrow_history

            JOIN users
                ON users.id =
                   borrow_history.user_id

            WHERE
                borrow_history.user_id = ?

            ORDER BY
                borrow_history.id DESC
        ";


        $stmt =
            $this->db
            ->prepare($sql);


        if (!$stmt) {

            return [];
        }


        $stmt->bind_param(
            "i",
            $userId
        );


        $stmt->execute();


        $result =
            $stmt->get_result();


        $books =
            $this->getDistributedBooks();


        $rows = [];


        while (
            $row =
            $result->fetch_assoc()
        ) {

            $bookId =
                $row["global_book_id"];


            if (
                isset(
                    $books[$bookId]
                )
            ) {

                $row["title"] =
                    $books[$bookId]["title"];

                $row["author"] =
                    $books[$bookId]["author"];

                $row["category"] =
                    $books[$bookId]["category"];

                $row["node"] =
                    $books[$bookId]["node"];

            } else {

                $row["title"] =
                    "Unknown";

                $row["author"] =
                    "Unknown";

                $row["category"] =
                    "Unknown";

                $row["node"] =
                    "Unknown";
            }


            $rows[] =
                $row;
        }


        $stmt->close();


        return $rows;
    }


    /*
        ========================================================
        Admin All Borrow History
        ========================================================
    */

    public function getAllHistory()
    {
        $sql = "
            SELECT

                borrow_history.id,

                users.name AS username,

                users.email,

                borrow_history.global_book_id,

                borrow_history.borrow_date,

                borrow_history.return_date,

                borrow_history.due_date,

                borrow_history.status

            FROM borrow_history

            JOIN users
                ON users.id =
                   borrow_history.user_id

            ORDER BY
                borrow_history.id DESC
        ";


        $historyResult =
            $this->db
            ->query($sql);


        if (!$historyResult) {

            return [];
        }


        $books =
            $this->getDistributedBooks();


        $allHistory = [];


        while (
            $history =
            $historyResult->fetch_assoc()
        ) {

            $globalBookId =
                $history["global_book_id"];


            $history["title"] =
                "-";


            $history["author"] =
                "-";


            $history["category"] =
                "-";


            $history["node"] =
                "-";


            if (
                isset(
                    $books[$globalBookId]
                )
            ) {

                $history["title"] =
                    $books[$globalBookId]["title"];

                $history["author"] =
                    $books[$globalBookId]["author"];

                $history["category"] =
                    $books[$globalBookId]["category"];

                $history["node"] =
                    $books[$globalBookId]["node"];
            }


            $allHistory[] =
                $history;
        }


        $historyResult->free();


        return $allHistory;
    }


    /*
        ========================================================
        Get Distributed Books
        ========================================================
    */

    private function getDistributedBooks()
    {
        $books = [];


        $nodes = [

            "library_node1",

            "library_node2",

            "library_node3"
        ];


        foreach (
            $nodes as $node
        ) {

            $conn =
                $this->getNodeConnection(
                    $node
                );


            if (
                !$conn ||
                $conn->connect_error
            ) {

                continue;
            }


            $sql = "
                SELECT
                    global_book_id,
                    title,
                    author,
                    category

                FROM books
            ";


            $result =
                $conn
                ->query($sql);


            if ($result) {

                while (
                    $book =
                    $result->fetch_assoc()
                ) {

                    $bookId =
                        $book["global_book_id"];


                    $books[$bookId] = [

                        "title" =>
                            $book["title"],

                        "author" =>
                            $book["author"],

                        "category" =>
                            $book["category"],

                        "node" =>
                            $node
                    ];
                }


                $result->free();
            }


            $conn->close();
        }


        return $books;
    }


    /*
        ========================================================
        Get Node Connection
        ========================================================
    */

    private function getNodeConnection(
        $nodeName
    ) {

        $configPath =
            dirname(__DIR__) .
            "/Config/nodes.php";


        if (
            !file_exists($configPath)
        ) {

            return null;
        }


        $nodes =
            require $configPath;


        $nodeKey =
            $this->getNodeKey(
                $nodeName
            );


        if (
            $nodeKey === "" ||
            !isset(
                $nodes[$nodeKey]
            )
        ) {

            return null;
        }


        $config =
            $nodes[$nodeKey];


        try {

            mysqli_report(
                MYSQLI_REPORT_OFF
            );


            $connection =
                new mysqli(

                    $config["host"],

                    $config["user"],

                    $config["password"],

                    $config["database"],

                    $config["port"]
                );


            if (
                $connection->connect_errno
            ) {

                return null;
            }


            $connection->set_charset(
                "utf8mb4"
            );


            return $connection;


        } catch (
            Throwable $e
        ) {

            return null;
        }
    }


    /*
        ========================================================
        Database Name -> Node Config Key
        ========================================================
    */

    private function getNodeKey(
        $nodeName
    ) {

        switch ($nodeName) {

            case "library_node1":

                return "node1";


            case "library_node2":

                return "node2";


            case "library_node3":

                return "node3";


            default:

                return "";
        }
    }


    /*
        ========================================================
        Get Upcoming Deadlines
        ========================================================
    */

    public function getUpcomingDeadlines(
        $daysLeft = 1
    ) {

        $daysLeft =
            (int)$daysLeft;


        if ($daysLeft < 0) {

            $daysLeft = 1;
        }


        $sql = "
            SELECT

                borrow_history.id,

                borrow_history.user_id,

                borrow_history.global_book_id,

                borrow_history.due_date,

                users.name AS username

            FROM borrow_history

            JOIN users
                ON users.id =
                   borrow_history.user_id

            WHERE
                borrow_history.status = 'borrowed'

            AND DATEDIFF(
                borrow_history.due_date,
                NOW()
            ) = ?
        ";


        $stmt =
            $this->db
            ->prepare($sql);


        if (!$stmt) {

            return [];
        }


        $stmt->bind_param(
            "i",
            $daysLeft
        );


        $stmt->execute();


        $result =
            $stmt->get_result();


        $rows = [];


        $books =
            $this->getDistributedBooks();


        while (
            $row =
            $result->fetch_assoc()
        ) {

            $bookId =
                $row["global_book_id"];


            if (
                isset(
                    $books[$bookId]
                )
            ) {

                $row["book_title"] =
                    $books[$bookId]["title"];

            } else {

                $row["book_title"] =
                    "Unknown";
            }


            $rows[] =
                $row;
        }


        $stmt->close();


        return $rows;
    }
}