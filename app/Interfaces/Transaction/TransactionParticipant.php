<?php


class TransactionParticipant
{


    private $connection;


    private $database;



    public function __construct(
        $database
    ) {


        $this->database =
            $database;


        $this->connection =
            new mysqli(

                "localhost",
                "root",
                "",
                $database

            );


        if (
            $this->connection->connect_error
        ) {

            die("Database Connection Failed : "
                . $this->connection->connect_error);
        }
    }






    /*
        Find whether this node owns the book
    */

    private function isOwnerNode(
        $bookId
    ) {


        if (
            str_starts_with(
                $bookId,
                "TECH"
            )
            &&
            $this->database ==
            "library_node1"
        ) {

            return true;
        }



        if (
            str_starts_with(
                $bookId,
                "SCI"
            )
            &&
            $this->database ==
            "library_node2"
        ) {

            return true;
        }



        if (
            str_starts_with(
                $bookId,
                "FIC"
            )
            &&
            $this->database ==
            "library_node3"
        ) {

            return true;
        }



        return false;
    }








    /*
        Phase 1
        PREPARE
    */


    public function prepare(
        $globalBookId
    ) {



        /*
            Other Nodes Skip
        */


        if (
            !$this->isOwnerNode(
                $globalBookId
            )
        ) {

            return true;
        }






        $stmt =
            $this->connection->prepare(

                "
            SELECT available_copies

            FROM books

            WHERE global_book_id=?

            "

            );



        $stmt->bind_param(

            "s",

            $globalBookId

        );



        $stmt->execute();



        $result =
            $stmt->get_result();





        if (
            $result->num_rows == 0
        ) {

            return false;
        }




        $book =
            $result->fetch_assoc();





        if (
            $book["available_copies"] <= 0
        ) {

            return false;
        }




        return true;
    }









    /*
        Phase 2
        COMMIT
    */


    public function commit(
        $globalBookId
    ) {



        /*
            Other Nodes Skip
        */


        if (
            !$this->isOwnerNode(
                $globalBookId
            )
        ) {

            return true;
        }






        $stmt =
            $this->connection->prepare(

                "
            UPDATE books

            SET available_copies =
            available_copies - 1

            WHERE global_book_id=?

            AND available_copies > 0

            "

            );





        $stmt->bind_param(

            "s",

            $globalBookId

        );





        return
            $stmt->execute();
    }








    /*
        Rollback
    */


    public function rollback()
    {


        $this->connection->rollback();
    }
}
