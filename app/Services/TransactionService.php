<?php


require_once
    "../app/Infrastructure/Transaction/TransactionCoordinator.php";



class TransactionService
{


    public function borrowTransaction(
        $bookId
    ) {


        $coordinator =
            new TransactionCoordinator();



        $coordinator
            ->addParticipant(
                "library_node1"
            );


        $coordinator
            ->addParticipant(
                "library_node2"
            );


        $coordinator
            ->addParticipant(
                "library_node3"
            );



        return

            $coordinator
            ->execute(
                $bookId
            );
    }
}
