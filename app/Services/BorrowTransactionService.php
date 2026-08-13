<?php

require_once
    __DIR__ . "/../Infrastructure/Transaction/TransactionCoordinator.php";


class BorrowTransactionService
{
    /*
        ========================================================
        Distributed Borrow Transaction
        ========================================================

        Global Book ID -> Distributed Node

        TECH-xxxx -> library_node1
        SCI-xxxx  -> library_node2
        FIC-xxxx  -> library_node3

        Two Phase Commit:

        Phase 1
            PREPARE

        Phase 2
            COMMIT / ABORT
    */


    public function borrow(
        $globalBookId
    ) {
        /*
            Clean input
        */

        $globalBookId =
            trim($globalBookId);


        /*
            Empty ID check
        */

        if ($globalBookId === "") {

            return [
                "status" =>
                "ABORT",

                "message" =>
                "Global Book ID is required"
            ];
        }


        /*
            Resolve target shard
        */

        $node =
            $this->resolveNode(
                $globalBookId
            );


        /*
            Invalid Global ID
        */

        if (!$node) {

            return [
                "status" =>
                "ABORT",

                "message" =>
                "Invalid Global Book ID: " .
                    htmlspecialchars(
                        $globalBookId
                    )
            ];
        }


        /*
            ====================================================
            Create Transaction Coordinator
            ====================================================
        */

        $coordinator =
            new TransactionCoordinator();


        /*
            ====================================================
            Register Participant
            ====================================================

            Only the target shard participates
            because the book belongs to one shard.
        */

        $coordinator
            ->addParticipant(
                $node
            );


        /*
            ====================================================
            Execute Two Phase Commit
            ====================================================

            Phase 1:
                PREPARE

            Phase 2:
                COMMIT

            If prepare fails:
                ABORT

            If all prepare operations succeed:
                COMMIT
        */

        try {

            $result =
                $coordinator
                ->execute(
                    $globalBookId
                );


            /*
                Make sure coordinator returned
                a valid result.
            */

            if (
                !is_array($result)
            ) {

                return [
                    "status" =>
                    "ABORT",

                    "message" =>
                    "Invalid transaction coordinator response"
                ];
            }


            return $result;
        } catch (
            Throwable $e
        ) {

            /*
                Prevent PHP fatal error
                from reaching the user.
            */

            return [
                "status" =>
                "ABORT",

                "message" =>
                "Distributed transaction failed: " .
                    $e->getMessage()
            ];
        }
    }


    /*
        ========================================================
        Sharding Resolver
        ========================================================

        Global Book ID determines the shard.

        TECH-xxxxx
            -> Technology
            -> library_node1

        SCI-xxxxx
            -> Science
            -> library_node2

        FIC-xxxxx
            -> Fiction
            -> library_node3
    */

    private function resolveNode(
        $globalBookId
    ) {
        /*
            Convert to uppercase
            so IDs are case-insensitive.
        */

        $globalBookId =
            strtoupper(
                trim($globalBookId)
            );


        /*
            Check exact prefix
        */

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


        /*
            Invalid prefix
        */

        return null;
    }
}
