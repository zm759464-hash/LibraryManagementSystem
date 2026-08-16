<?php

require_once
    __DIR__ . "/../Repositories/BorrowRepository.php";

require_once
    __DIR__ . "/../Repositories/BookRepository.php";

require_once
    __DIR__ . "/NotificationService.php";


class BorrowService
{
    private $borrowRepository;

    private $bookRepository;

    private $notificationService;


    /*
        ========================================================
        Constructor
        ========================================================
    */

    public function __construct()
    {
        $this->borrowRepository =
            new BorrowRepository();


        $this->bookRepository =
            new BookRepository();


        $this->notificationService =
            new NotificationService();
    }


    /*
        ========================================================
        Distributed Borrow
        ========================================================

        ဒီ method ကို future use အတွက်ထားထားပါတယ်။

        Current controller မှာ
        BorrowTransactionService ကို တိုက်ရိုက်သုံးပါတယ်။

        Business layer မှာ borrow flow တစ်ခုလုံးကို
        စုစည်းချင်ရင် ဒီ method ကိုသုံးနိုင်ပါတယ်။
    */

    public function borrow(
        $userId,
        $bookId
    ) {

        try {

            if (
                empty($userId) ||
                !is_numeric($userId)
            ) {

                return false;
            }


            $bookId =
                trim($bookId);


            if ($bookId === "") {

                return false;
            }


            /*
                ------------------------------------------------
                Update distributed stock
                ------------------------------------------------
            */

            $stock =
                $this->bookRepository
                ->borrowBook(
                    $bookId
                );


            if (!$stock) {

                return false;
            }


            /*
                ------------------------------------------------
                Save history
                ------------------------------------------------
            */

            $history =
                $this->createHistory(
                    $userId,
                    $bookId
                );


            if ($history === false) {

                return false;
            }


            return $history;


        } catch (
            Throwable $e
        ) {

            error_log(
                "BorrowService::borrow Error: "
                . $e->getMessage()
            );

            return false;
        }
    }


    /*
        ========================================================
        Get Borrow Days
        ========================================================
    */

    public function getBorrowDays()
    {
        $days =
            $this->borrowRepository
            ->getBorrowDays();


        if (
            !is_numeric($days) ||
            (int)$days <= 0
        ) {

            return 5;
        }


        return (int)$days;
    }


    /*
        ========================================================
        Calculate Borrow Dates
        ========================================================
    */

    public function calculateBorrowDates()
    {
        $loanDays =
            $this->getBorrowDays();


        $borrowDate =
            new DateTime();


        $dueDate =
            clone $borrowDate;


        $dueDate->modify(
            "+" . $loanDays . " days"
        );


        return [

            "borrow_date" =>
                $borrowDate
                ->format(
                    "Y-m-d H:i:s"
                ),

            "due_date" =>
                $dueDate
                ->format(
                    "Y-m-d H:i:s"
                ),

            "loan_days" =>
                $loanDays
        ];
    }


    /*
        ========================================================
        Create Borrow History
        ========================================================
    */

    public function createHistory(
        $userId,
        $bookId
    ) {

        try {

            /*
                ------------------------------------------------
                Validate User ID
                ------------------------------------------------
            */

            if (
                empty($userId) ||
                !is_numeric($userId)
            ) {

                return false;
            }


            /*
                ------------------------------------------------
                Validate Book ID
                ------------------------------------------------
            */

            $bookId =
                trim($bookId);


            if ($bookId === "") {

                return false;
            }


            /*
                ------------------------------------------------
                Calculate dates
                ------------------------------------------------
            */

            $dates =
                $this->calculateBorrowDates();


            /*
                ------------------------------------------------
                Save history
                ------------------------------------------------
            */

            $saved =
                $this->borrowRepository
                ->createHistory(
                    (int)$userId,
                    $bookId,
                    $dates["borrow_date"],
                    $dates["due_date"]
                );


            if (!$saved) {

                return false;
            }


            /*
                ------------------------------------------------
                Return complete borrow information
                ------------------------------------------------
            */

            return [

                "borrow_date" =>
                    $dates["borrow_date"],

                "due_date" =>
                    $dates["due_date"],

                "loan_days" =>
                    $dates["loan_days"]
            ];


        } catch (
            Throwable $e
        ) {

            error_log(
                "BorrowService::createHistory Error: "
                . $e->getMessage()
            );

            return false;
        }
    }


    /*
        ========================================================
        User Borrow History
        ========================================================
    */

    public function history(
        $userId
    ) {

        if (
            empty($userId) ||
            !is_numeric($userId)
        ) {

            return [];
        }


        return
            $this->borrowRepository
            ->getHistory(
                (int)$userId
            );
    }


    /*
        ========================================================
        Admin All Borrow History
        ========================================================
    */

    public function all()
    {
        return
            $this->borrowRepository
            ->getAllHistory();
    }


    /*
        ========================================================
        Notification Count
        ========================================================
    */

    public function notificationCount()
    {
        return
            $this->notificationService
            ->getUnreadCount();
    }


    /*
        ========================================================
        Notifications
        ========================================================
    */

    public function notifications(
        $limit = 8
    ) {

        return
            $this->notificationService
            ->getRecent(
                (int)$limit
            );
    }


    /*
        ========================================================
        Check And Send Return Reminders
        ========================================================
    */

    public function checkAndSendReminders()
    {
        try {

            /*
                ------------------------------------------------
                Get books due tomorrow
                ------------------------------------------------
            */

            $upcomingDeadlines =
                $this->borrowRepository
                ->getUpcomingDeadlines(
                    1
                );


            if (
                empty($upcomingDeadlines)
            ) {

                return true;
            }


            /*
                ------------------------------------------------
                Create notification
                ------------------------------------------------
            */

            foreach (
                $upcomingDeadlines
                as $borrow
            ) {

                $userId =
                    $borrow["user_id"]
                    ?? 0;


                $username =
                    $borrow["username"]
                    ?? "";


                $bookTitle =
                    $borrow["book_title"]
                    ?? "စာအုပ်";


                $dueDate =
                    $borrow["due_date"]
                    ?? "";


                $this->notificationService
                    ->createBorrowReminder(
                        $userId,
                        $username,
                        $bookTitle,
                        $dueDate
                    );
            }


            return true;


        } catch (
            Throwable $e
        ) {

            error_log(
                "BorrowService::checkAndSendReminders Error: "
                . $e->getMessage()
            );

            return false;
        }
    }
}