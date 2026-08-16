<?php

require_once
    "../app/Repositories/HoldRepository.php";

require_once
    "../app/Services/DistributedQueryService.php";


class HoldService
{
    private $repository;

    private $distributedService;


    /*
        ========================================================
        Constructor
        ========================================================
    */

    public function __construct()
    {
        $this->repository =
            new HoldRepository();

        $this->distributedService =
            new DistributedQueryService();
    }


    /*
        ========================================================
        User Create Hold
        ========================================================
        
        IMPORTANT RULE:

        Hold is allowed ONLY when:
            available_copies = 0

        If copies are available:
            Hold is NOT allowed.
    */

    public function create(
        $userId,
        $bookId
    ) {

        /*
            ----------------------------------------------------
            Validate User
            ----------------------------------------------------
        */

        if (
            empty($userId) ||
            !is_numeric($userId)
        ) {
            return [
                "success" => false,
                "reason" => "invalid_user"
            ];
        }


        /*
            ----------------------------------------------------
            Validate Book ID
            ----------------------------------------------------
        */

        $bookId =
            trim((string)$bookId);


        if ($bookId === "") {

            return [
                "success" => false,
                "reason" => "invalid_book"
            ];
        }


        /*
            ====================================================
            CHECK BOOK FROM DISTRIBUTED NODES
            ====================================================
        */

        try {

            $books =
                (array)
                $this->distributedService
                    ->getAllBooks();

        } catch (Exception $e) {

            return [
                "success" => false,
                "reason" => "book_lookup_failed"
            ];
        }


        /*
            ----------------------------------------------------
            Find requested book
            ----------------------------------------------------
        */

        $foundBook = null;


        foreach ($books as $book) {

            $distributedBookId =
                $book["global_book_id"]
                ?? $book["global_id"]
                ?? $book["book_id"]
                ?? $book["id"]
                ?? "";


            if (
                (string)$distributedBookId ===
                (string)$bookId
            ) {

                $foundBook = $book;

                break;
            }
        }


        /*
            ----------------------------------------------------
            Book not found
            ----------------------------------------------------
        */

        if ($foundBook === null) {

            return [
                "success" => false,
                "reason" => "book_not_found"
            ];
        }


        /*
            ====================================================
            CHECK AVAILABLE COPIES
            ====================================================
        */

        $available =
            (int)(
                $foundBook["available_copies"]
                ?? 0
            );


        /*
            ----------------------------------------------------
            IMPORTANT:
            If book is available, HOLD is NOT allowed.
            ----------------------------------------------------
        */

        if ($available > 0) {

            return [
                "success" => false,
                "reason" => "book_available",
                "available" => $available,
                "book" => $foundBook
            ];
        }


        /*
            ====================================================
            BOOK IS OUT OF STOCK
            Now allow HoldRepository to check duplicate hold.
            ====================================================
        */

        $result =
            $this->repository
                ->create(
                    (int)$userId,
                    $bookId
                );


        /*
            ----------------------------------------------------
            Repository returns structured result
            ----------------------------------------------------
        */

        if (is_array($result)) {

            return $result;
        }


        /*
            Backward compatibility
        */

        if ($result === true) {

            return [
                "success" => true,
                "reason" => "created"
            ];
        }


        return [
            "success" => false,
            "reason" => "database_error"
        ];
    }


    /*
        ========================================================
        User View Own Holds
        ========================================================
    */

    public function myHolds(
        $userId
    ) {

        if (
            empty($userId) ||
            !is_numeric($userId)
        ) {
            return [];
        }


        return
            $this->repository
                ->myHolds(
                    (int)$userId
                );
    }


    /*
        ========================================================
        Admin View All Holds
        ========================================================
    */

    public function all()
    {
        return
            $this->repository
                ->all();
    }


    /*
        ========================================================
        Admin Approve / Reject Hold
        ========================================================
    */

    public function update(
        $id,
        $status
    ) {

        if (
            !in_array(
                $status,
                [
                    "approved",
                    "rejected"
                ],
                true
            )
        ) {

            return false;
        }


        return
            $this->repository
                ->updateStatus(
                    $id,
                    $status
                );
    }
}