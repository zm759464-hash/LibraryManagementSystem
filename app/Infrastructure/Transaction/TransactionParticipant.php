
<?php

/*
    ============================================================
    TransactionParticipant
    ============================================================

    Responsible for one distributed database node.

    Example:

        library_node1
        library_node2
        library_node3

    Transaction Flow:

        Phase 1  -> PREPARE
        Phase 2  -> UPDATE
        Phase 3  -> FINAL COMMIT

        Failure -> ROLLBACK
*/

class TransactionParticipant
{
    private $node;

    private $db;

    private $prepared = false;

    private $updated = false;


    /*
        ========================================================
        Constructor
        ========================================================
    */

    public function __construct($node)
    {
        $this->node = $node;

        $this->db = new mysqli(
            "localhost",
            "root",
            "",
            $node
        );

        if ($this->db->connect_error) {

            throw new Exception(
                "Cannot connect to node: " . $node
            );
        }

        /*
            Make sure transactions use InnoDB behavior.
        */

        $this->db->set_charset("utf8mb4");
    }


    /*
        ========================================================
        PHASE 1
        PREPARE
        ========================================================

        Check:

        1. Book exists
        2. available_copies > 0
        3. Start transaction
        4. Lock target row

    */

    public function prepare($bookId)
    {
        try {

            /*
                Start transaction
            */

            $this->db->begin_transaction();


            /*
                Lock the book row.

                FOR UPDATE provides pessimistic row locking.
            */

            $sql = "
                SELECT
                    global_book_id,
                    available_copies
                FROM books
                WHERE global_book_id = ?
                FOR UPDATE
            ";

            $stmt = $this->db->prepare($sql);

            if (!$stmt) {
                throw new Exception(
                    "Prepare SQL failed"
                );
            }


            $stmt->bind_param(
                "s",
                $bookId
            );

            $stmt->execute();


            $result =
                $stmt->get_result();


            /*
                Book does not exist
            */

            if ($result->num_rows === 0) {

                $stmt->close();

                $this->db->rollback();

                return false;
            }


            $book =
                $result->fetch_assoc();


            $stmt->close();


            /*
                No available copy
            */

            if (
                (int)$book["available_copies"] <= 0
            ) {

                $this->db->rollback();

                return false;
            }


            /*
                PREPARE successful
            */

            $this->prepared = true;

            return true;
        } catch (Exception $e) {

            $this->rollback();

            return false;
        }
    }


    /*
        ========================================================
        PHASE 2
        UPDATE
        ========================================================

        Decrease available_copies by 1.

        The row was already locked during PREPARE.
    */

    public function commit($bookId)
    {
        if (!$this->prepared) {
            return false;
        }


        try {

            $sql = "
                UPDATE books
                SET available_copies =
                    available_copies - 1
                WHERE global_book_id = ?
                AND available_copies > 0
            ";


            $stmt =
                $this->db->prepare($sql);


            if (!$stmt) {
                return false;
            }


            $stmt->bind_param(
                "s",
                $bookId
            );


            $stmt->execute();


            /*
                Exactly one row should be updated.
            */

            if ($stmt->affected_rows !== 1) {

                $stmt->close();

                return false;
            }


            $stmt->close();


            $this->updated = true;

            return true;
        } catch (Exception $e) {

            return false;
        }
    }


    /*
        ========================================================
        PHASE 3
        FINAL COMMIT
        ========================================================

        Permanently commit transaction.
    */

    public function finalizeCommit()
    {
        if (
            !$this->prepared ||
            !$this->updated
        ) {

            return false;
        }


        try {

            $result =
                $this->db->commit();


            if (!$result) {
                return false;
            }


            $this->prepared = false;
            $this->updated = false;


            return true;
        } catch (Exception $e) {

            return false;
        }
    }


    /*
        ========================================================
        ROLLBACK
        ========================================================
    */

    public function rollback()
    {
        try {

            if (
                $this->db &&
                $this->db->thread_id
            ) {

                $this->db->rollback();
            }
        } catch (Exception $e) {

            /*
                Ignore rollback exception.
            */
        }

        $this->prepared = false;
        $this->updated = false;
    }


    /*
        ========================================================
        CLOSE CONNECTION
        ========================================================
    */

    public function close()
    {
        if ($this->db) {

            $this->db->close();

            $this->db = null;
        }
    }


    /*
        ========================================================
        Get Node Name
        ========================================================
    */

    public function getNode()
    {
        return $this->node;
    }
}
