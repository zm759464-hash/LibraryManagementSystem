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
        Distributed Transaction Control
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
        Insert Borrow History
        ========================================================
    */

    /*
        ========================================================
        Insert Borrow History (Database-Driven Date Calculation)
        ========================================================
    */

    public function createHistory(
        $userId,
        $bookId,
        $borrowDate = null,
        $dueDate = null
    ) {
        // 🛠️ ပြင်ဆင်ချက်: PHP ဘက်က လွဲချော်မှုမရှိစေရန် MySQL ရဲ့ NOW() နှင့် DATE_ADD() ကို တိုက်ရိုက်သုံး၍ ၅ ရက်တွက်ခိုင်းခြင်း
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
                DATE_ADD(NOW(), INTERVAL 5 DAY),
                'borrowed'
            )
        ";


        $stmt =
            $this->db
            ->prepare($sql);


        if (!$stmt) {
            return false;
        }


        // user_id (i) နှင့် bookId (s) သာ တိုက်ရိုက် bind လုပ်ရန် လိုအပ်တော့သည်
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

            WHERE borrow_history.user_id = ?

            ORDER BY borrow_history.id DESC
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


        /*
            ----------------------------------------------------
            Get books from all distributed nodes
            ----------------------------------------------------
        */

        $books =
            $this->getDistributedBooks();


        $rows = [];


        while (
            $row =
            $result->fetch_assoc()
        ) {
            $bookId =
                $row["global_book_id"];


            /*
                ------------------------------------------------
                Match global_book_id
                with distributed node book
                ------------------------------------------------
            */

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

                /*
                    Book may have been deleted
                    from distributed node.
                */

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
        Admin View All Borrow History
        ========================================================
    */

    public function getAllHistory()
    {
        /*
            ----------------------------------------------------
            Step 1
            Get borrow history from main database
            ----------------------------------------------------
        */

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

            ORDER BY borrow_history.id DESC
        ";


        $historyResult =
            $this->db
            ->query($sql);


        if (!$historyResult) {
            return [];
        }


        /*
            ----------------------------------------------------
            Step 2
            Get distributed books
            ----------------------------------------------------
        */

        $books =
            $this->getDistributedBooks();


        /*
            ----------------------------------------------------
            Step 3
            Merge history + book information
            ----------------------------------------------------
        */

        $allHistory = [];


        while (
            $history =
            $historyResult->fetch_assoc()
        ) {
            $globalBookId =
                $history["global_book_id"];


            /*
                Default values
            */

            $history["title"] =
                "-";


            $history["author"] =
                "-";


            $history["category"] =
                "-";


            $history["node"] =
                "-";


            /*
                Match distributed book
            */

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


        return $allHistory;
    }


    /*
        ========================================================
        Get Books From All Distributed Nodes
        ========================================================
    */

    /*
        ========================================================
        Get Books From All Distributed Nodes (Updated Fix)
        ========================================================
    */

    /*
        ========================================================
        Get Books From All Distributed Nodes (Fix For Unknown)
        ========================================================
    */

    private function getDistributedBooks()
    {
        $books = [];

        /*
            ----------------------------------------------------
            Fetch and merge books from all distributed nodes
            ----------------------------------------------------
        */

        $nodes = [
            'library_node1',
            'library_node2',
            'library_node3'
        ];


        foreach ($nodes as $node) {

            $conn =
                $this->getNodeConnection(
                    $node
                );


            if (
                $conn &&
                !$conn->connect_error
            ) {

                $sql = "
                    SELECT 
                        global_book_id, 
                        title, 
                        author, 
                        category 
                    FROM books
                ";


                $result =
                    $conn->query($sql);


                if ($result) {

                    while (
                        $book =
                        $result->fetch_assoc()
                    ) {

                        $bookId =
                            $book["global_book_id"];


                        $books[$bookId] = [
                            "title"    => $book["title"],
                            "author"   => $book["author"],
                            "category" => $book["category"],
                            "node"     => $node
                        ];
                    }

                    $result->free();
                }

                $conn->close();
            }
        }


        return $books;
    }


    /*
        ========================================================
        Get Node Connection Helper
        ========================================================
    */

    private function getNodeConnection(
        $nodeName
    ) {
        /*
            Create direct connection to distributed shards
        */
        $config = [
            'host' => '127.0.0.1',
            'user' => 'root',
            'pass' => '', // လူကြီးမင်း MySQL Password ရှိပါက ထည့်ပေးရန် (ဥပမာ - '123456')
        ];

        return new mysqli(
            $config['host'],
            $config['user'],
            $config['pass'],
            $nodeName
        );
    }




    /*
        ========================================================
        Admin Notifications
        ========================================================
    */


    public function getActiveBorrowCount()
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM borrow_history
            WHERE status = 'borrowed'
        ";

        $result = $this->db->query($sql);

        if (!$result) {
            return 0;
        }

        $row = $result->fetch_assoc();

        return (int)($row["total"] ?? 0);
    }


    /*
        ========================================================
        Get Upcoming Deadlines (New Method)
        ========================================================
        Find active borrows where due_date is exactly $daysLeft away.
    */

    public function getUpcomingDeadlines($daysLeft = 1)
    {
        $sql = "
        SELECT
            borrow_history.id,
            borrow_history.user_id,
            borrow_history.global_book_id
        FROM borrow_history
        WHERE borrow_history.status = 'borrowed'
        AND DATEDIFF(borrow_history.due_date, NOW()) = ?
    ";

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("i", $daysLeft);
        $stmt->execute();

        $result = $stmt->get_result();

        $rows = [];

        $books = $this->getDistributedBooks();

        while ($row = $result->fetch_assoc()) {
            $bookId = $row["global_book_id"];

            if (isset($books[$bookId])) {
                $row["book_title"] = $books[$bookId]["title"];
            }

            $rows[] = $row;
        }

        $stmt->close();

        return $rows;
    }


    /*========================================================
    Insert Notification (New Method)
========================================================*/

    /*
        ========================================================
        Insert Notification (Updated for Global Admin Noti)
        ========================================================
    */

    public function sendNotification($userId, $title, $message, $type, $icon)
    {
        // မူရင်း notifications table မှာ user_id မပါတဲ့အတွက် query ကို ပြန်လည်ညှိနှိုင်းထားပါသည်
        $sql = "
            INSERT INTO notifications 
            (type, title, message, is_read, created_at) 
            VALUES (?, ?, ?, 0, NOW())
        ";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return false;
        }

        // $type, $title, $message ကို bind လုပ်ပေးခြင်း
        $stmt->bind_param("sss", $type, $title, $message);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    /*
        ========================================================
        Get Recent Borrow Notifications
        ========================================================
    */
    public function getNotifications($limit = 8)
    {
        $sql = "
            SELECT id, type, title, message, is_read, created_at
            FROM notifications
            ORDER BY id DESC
            LIMIT ?
        ";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        $stmt->close();
        return $rows;
    }
}
