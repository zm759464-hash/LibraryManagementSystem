<?php


require_once
    "TransactionParticipant.php";



class TransactionCoordinator
{


    private $participants = [];




    public function addParticipant($node)
    {


        $this->participants[] =
            new TransactionParticipant(
                $node
            );
    }







    public function execute(
        $globalBookId
    ) {


        /*
            Phase 1
            PREPARE
        */


        foreach (
            $this->participants
            as $participant
        ) {


            $result =
                $participant
                ->prepare(
                    $globalBookId
                );


            if (!$result) {

                return [

                    "status" => "ABORT",

                    "message" =>
                    "Prepare Failed"

                ];
            }
        }







        /*
            Phase 2
            COMMIT
        */


        foreach (
            $this->participants
            as $participant
        ) {


            $participant
                ->commit(
                    $globalBookId
                );
        }






        return [

            "status" => "COMMIT",

            "message" =>
            "Transaction Successful"

        ];
    }
}
