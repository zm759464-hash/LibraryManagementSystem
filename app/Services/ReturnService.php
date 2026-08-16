<?php

require_once
    __DIR__ . "/../Repositories/ReturnRepository.php";

require_once
    __DIR__ . "/../Repositories/BookRepository.php";

require_once
    __DIR__ . "/../Repositories/UserRepository.php";

require_once
    __DIR__ . "/NotificationService.php";


class ReturnService
{
    /*
        ========================================================
        Properties
        ========================================================
    */

    private $returnRepository;

    private $bookRepository;

    private $userRepository;

    private $notificationService;


    /*
        ========================================================
        Constructor
        ========================================================
    */

    public function __construct()
    {
        $this->returnRepository =
            new ReturnRepository();

        $this->bookRepository =
            new BookRepository();

        $this->userRepository =
            new UserRepository();

        $this->notificationService =
            new NotificationService();
    }


    /*
        ========================================================
        Return Book
        ========================================================

        Steps:

            1. Increase available copies
            2. Update borrow history
            3. Create notification

        Notification is handled by
        NotificationService.

        BorrowRepository is NOT responsible
        for notifications.
    */

    public function returnBook(
        $bookId,
        $userId
    ) {
        /*
            ====================================================
            Validate User ID
            ====================================================
        */

        if (
            empty($userId) ||
            !is_numeric($userId)
        ) {
            return false;
        }


        /*
            ====================================================
            Validate Book ID
            ====================================================
        */

        $bookId =
            trim($bookId);


        if ($bookId === "") {
            return false;
        }


        $userId =
            (int)$userId;


        /*
            ====================================================
            Step 1
            Increase Available Copies
            ====================================================
        */

        $stock =
            $this->bookRepository
            ->returnBook(
                $bookId
            );


        if (!$stock) {
            return false;
        }


        /*
            ====================================================
            Step 2
            Update Borrow History
            ====================================================
        */

        $statusUpdated =
            $this->returnRepository
            ->updateBorrowStatus(
                $bookId,
                $userId
            );


        /*
            If history update fails,
            try to restore stock.
        */

        if (!$statusUpdated) {

            /*
                Compensation action

                The stock was already increased,
                so decrease it again by borrowing
                the book back.

                This is not a perfect distributed
                transaction, but prevents the
                obvious stock/history mismatch.
            */

            $this->bookRepository
                ->borrowBook(
                    $bookId
                );

            return false;
        }


        /*
            ====================================================
            Step 3
            Get User Information
            ====================================================
        */

        $user =
            $this->userRepository
            ->getUserById(
                $userId
            );


        $username =
            "User";


        if (
            is_array($user)
        ) {

            $username =
                $user["name"]
                ?? $user["username"]
                ?? "User";
        }


        /*
            ====================================================
            Notification
            ====================================================
        */

        $type =
            "Return";


        $title =
            "Book Returned";


        $message =
            "{$username} returned book {$bookId}.";


        /*
            NotificationService
            handles database INSERT.
        */

        try {

            $notificationCreated =
                $this->notificationService
                ->create(
                    $type,
                    $title,
                    $message
                );


            /*
                Notification failure should NOT
                make the successful return fail.

                The book has already been returned
                and history has already been updated.
            */

            if (!$notificationCreated) {

                error_log(
                    "ReturnService: "
                    . "Failed to create return notification "
                    . "for user ID: "
                    . $userId
                    . ", book ID: "
                    . $bookId
                );
            }

        } catch (
            Throwable $e
        ) {

            error_log(
                "ReturnService notification error: "
                . $e->getMessage()
            );
        }


        /*
            ====================================================
            Return Success
            ====================================================
        */

        return true;
    }


    /*
        ========================================================
        User Return History
        ========================================================
    */

    public function history(
        $userId
    ) {
        /*
            Validate User ID
        */

        if (
            empty($userId) ||
            !is_numeric($userId)
        ) {
            return [];
        }


        return
            $this->returnRepository
            ->getHistory(
                (int)$userId
            );
    }
}