
<?php

/*
    ============================================================
    TransactionCoordinator
    ============================================================

    Coordinates distributed transaction participants.

    Phase 1:
        PREPARE

    Phase 2:
        UPDATE

    Phase 3:
        FINAL COMMIT

    Failure:
        ROLLBACK
*/


require_once
    __DIR__ . "/TransactionParticipant.php";


class TransactionCoordinator
{
    private $participants = [];


    /*
        ========================================================
        Add Transaction Participant
        ========================================================
    */

    public function addParticipant($node)
    {
        try {

            $this->participants[] =
                new TransactionParticipant(
                    $node
                );

            return true;
        } catch (Exception $e) {

            return false;
        }
    }


    /*
        ========================================================
        Execute Distributed Transaction
        ========================================================
    */

    public function execute($bookId)
    {
        /*
            No participant
        */

        if (
            empty($this->participants)
        ) {

            return [
                "status" =>
                "ABORT",

                "message" =>
                "No Transaction Participant"
            ];
        }


        /*
            ====================================================
            PHASE 1 : PREPARE
            ====================================================
        */

        foreach (
            $this->participants
            as $participant
        ) {

            if (
                !$participant
                    ->prepare($bookId)
            ) {

                $this->rollback();

                $this->close();

                return [
                    "status" =>
                    "ABORT",

                    "message" =>
                    "Prepare Phase Failed"
                ];
            }
        }


        /*
            ====================================================
            PHASE 2 : UPDATE
            ====================================================
        */

        foreach (
            $this->participants
            as $participant
        ) {

            if (
                !$participant
                    ->commit($bookId)
            ) {

                $this->rollback();

                $this->close();

                return [
                    "status" =>
                    "ABORT",

                    "message" =>
                    "Commit Phase Failed"
                ];
            }
        }


        /*
            ====================================================
            PHASE 3 : FINAL COMMIT
            ====================================================
        */

        foreach (
            $this->participants
            as $participant
        ) {

            if (
                !$participant
                    ->finalizeCommit()
            ) {

                $this->rollback();

                $this->close();

                return [
                    "status" =>
                    "ABORT",

                    "message" =>
                    "Finalize Commit Failed"
                ];
            }
        }


        /*
            ====================================================
            CLOSE CONNECTIONS
            ====================================================
        */

        $this->close();


        return [
            "status" =>
            "COMMIT",

            "message" =>
            "Transaction Successful"
        ];
    }


    /*
        ========================================================
        Rollback All Participants
        ========================================================
    */

    private function rollback()
    {
        foreach (
            $this->participants
            as $participant
        ) {

            $participant->rollback();
        }
    }


    /*
        ========================================================
        Close All Participants
        ========================================================
    */

    private function close()
    {
        foreach (
            $this->participants
            as $participant
        ) {

            $participant->close();
        }
    }
}
