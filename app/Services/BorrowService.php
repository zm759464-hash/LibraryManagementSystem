<?php

require_once
    __DIR__ . "/../Repositories/BorrowRepository.php";

require_once
    __DIR__ . "/../Repositories/BookRepository.php";


class BorrowService
{
    private $borrowRepository;

    private $bookRepository;


    public function __construct()
    {
        /*
            Repository Layer

            BorrowRepository
            -> borrow_history

            BookRepository
            -> distributed book nodes
        */

        $this->borrowRepository =
            new BorrowRepository();


        $this->bookRepository =
            new BookRepository();
    }


    /*
        ========================================================
        Distributed Borrow Transaction
        ========================================================
    */

    public function borrow(
        $userId,
        $bookId
    ) {
        try {

            /*
                ------------------------------------------------
                Step 1
                Update distributed node stock
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
                Step 2
                Save borrow history
                ------------------------------------------------
            */

            $history =
                $this->createHistory(
                    $userId,
                    $bookId
                );


            if (!$history) {
                return false;
            }


            return true;
        } catch (Exception $e) {

            return false;
        }
    }


    /*
        ========================================================
        Insert Borrow History
        ========================================================
    */

    public function createHistory(
        $userId,
        $bookId
    ) {

        /*
            Validate user ID
        */

        if (
            empty($userId) ||
            !is_numeric($userId)
        ) {
            return false;
        }


        /*
            Validate Global Book ID
        */

        $bookId =
            trim($bookId);


        if ($bookId === "") {
            return false;
        }


        /*
            ----------------------------------------------------
            Calculate 5 Days Borrow Limit
            ----------------------------------------------------
            borrow_date = Current time
            due_date    = Current time + 5 days
        */

        $borrowDate = date('Y-m-d H:i:s');
        $dueDate = date('Y-m-d H:i:s', strtotime('+5 days'));


        /*
            Insert into borrow_history with dates
        */

        return
            $this->borrowRepository
            ->createHistory(
                (int)$userId,
                $bookId,
                $borrowDate,
                $dueDate
            );
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
        Admin View All Borrow History
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
            $this->borrowRepository
            ->getActiveBorrowCount();
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
            $this->borrowRepository
            ->getNotifications(
                $limit
            );
    }


    /*
        ========================================================
        4th Day Return Reminder Notification (New Feature)
        ========================================================
        This function checks for active borrows where due_date is 
        exactly 1 day away (4th day of borrowing) and sends a notification.
    */

    /*
        ========================================================
        4th Day Return Reminder Notification (Updated)
        ========================================================
    */

    public function checkAndSendReminders()
    {
        try {
            // အပ်ရန် ၁ ရက်အလို ဖြစ်နေသော စာရင်းများကို ဆွဲထုတ်ခြင်း
            $upcomingDeadlines = $this->borrowRepository->getUpcomingDeadlines(1);

            if (empty($upcomingDeadlines)) {
                return true;
            }

            foreach ($upcomingDeadlines as $borrow) {
                $userId = $borrow['user_id'];

                // User ရဲ့ အမည်ကိုပါ Noti ထဲမှာ ထည့်ပြရန်အတွက် သုံးစွဲသူအမည်ကို ဆွဲထုတ်ခြင်း
                $username = "အသုံးပြုသူ (ID: #{$userId})";
                if (isset($borrow['username'])) {
                    $username = $borrow['username'];
                }

                $bookTitle = $borrow['book_title'] ?? 'စာအုပ်';

                $title = "စာအုပ်ပြန်အပ်ရန် သတိပေးချက် (၄ ရက်မြောက်နေ့)";
                $message = "{$username} ငှားရမ်းထားသော '{$bookTitle}' စာအုပ်သည် မနက်ဖြန်တွင် ၅ ရက်ပြည့်တော့မည် ဖြစ်သဖြင့် ပြန်လည်အပ်နှံပေးရန် အကြောင်းကြားစာ ပို့ထားရန် လိုအပ်ပါသည်။";
                $type = "Reminder";
                $icon = "⚠️";

                // စနစ်ထဲသို့ Noti အဖြစ် ထည့်သွင်းခြင်း
                $this->borrowRepository->sendNotification($userId, $title, $message, $type, $icon);
            }

            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
