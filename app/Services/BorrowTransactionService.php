<?php

require_once
    __DIR__ .
    "/../Infrastructure/Transaction/TransactionCoordinator.php";


class BorrowTransactionService
{
    /*
        ========================================================
        Distributed Borrow Transaction
        ========================================================

        Global Book ID

        TECH-xxxx
            -> library_node1

        SCI-xxxx
            -> library_node2

        FIC-xxxx
            -> library_node3


        Flow:

            Controller
                 ↓
            BorrowTransactionService
                 ↓
            TransactionCoordinator
                 ↓
            Target Distributed Node
    */


    public function borrow(
        $globalBookId
    ) {

        /*
            ----------------------------------------------------
            Clean input
            ----------------------------------------------------
        */

        $globalBookId =
            trim($globalBookId);


        /*
            ----------------------------------------------------
            Validate input
            ----------------------------------------------------
        */

        if (
            $globalBookId === ""
        ) {

            return [

                "status" =>
                    "ABORT",

                "message" =>
                    "Global Book ID is required"
            ];
        }


        /*
            ----------------------------------------------------
            Normalize ID
            ----------------------------------------------------
        */

        $globalBookId =
            strtoupper(
                $globalBookId
            );


        /*
            ----------------------------------------------------
            Resolve shard
            ----------------------------------------------------
        */

        $node =
            $this->resolveNode(
                $globalBookId
            );


        if (!$node) {

            return [

                "status" =>
                    "ABORT",

                "message" =>
                    "Invalid Global Book ID: "
                    . htmlspecialchars(
                        $globalBookId,
                        ENT_QUOTES,
                        "UTF-8"
                    )
            ];
        }


        /*
            ====================================================
            Transaction Coordinator
            ====================================================
        */

        $coordinator =
            new TransactionCoordinator();


        /*
            ----------------------------------------------------
            Register target participant
            ----------------------------------------------------
        */

        $coordinator
            ->addParticipant(
                $node
            );


        /*
            ====================================================
            Execute Transaction
            ====================================================
        */

        try {

            $result =
                $coordinator
                ->execute(
                    $globalBookId
                );


            /*
                ------------------------------------------------
                Validate result
                ------------------------------------------------
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


            /*
                ------------------------------------------------
                Normalize status
                ------------------------------------------------
            */

            if (
                !isset(
                    $result["status"]
                )
            ) {

                $result["status"] =
                    "ABORT";
            }


            return $result;


        } catch (
            Throwable $e
        ) {

            error_log(
                "BorrowTransactionService::borrow Error: "
                . $e->getMessage()
            );


            return [

                "status" =>
                    "ABORT",

                "message" =>
                    "Distributed transaction failed: "
                    . $e->getMessage()
            ];
        }
    }


    /*
        ========================================================
        Resolve Distributed Node
        ========================================================
    */

    private function resolveNode(
        $globalBookId
    ) {

        $globalBookId =
            strtoupper(
                trim($globalBookId)
            );


        /*
            ----------------------------------------------------
            Technology
            ----------------------------------------------------
        */

        if (
            str_starts_with(
                $globalBookId,
                "TECH-"
            )
        ) {

            return "library_node1";
        }


        /*
            ----------------------------------------------------
            Science
            ----------------------------------------------------
        */

        if (
            str_starts_with(
                $globalBookId,
                "SCI-"
            )
        ) {

            return "library_node2";
        }


        /*
            ----------------------------------------------------
            Fiction
            ----------------------------------------------------
        */

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
}