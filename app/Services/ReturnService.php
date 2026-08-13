
<?php


require_once
    "../app/Repositories/ReturnRepository.php";


require_once
    "../app/Repositories/BookRepository.php";


require_once
    "../app/Repositories/BorrowRepository.php";


require_once
    "../app/Repositories/UserRepository.php";


class ReturnService
{


    private $returnRepository;


    private $bookRepository;


    private $borrowRepository;


    private $userRepository;




    public function __construct()
    {


        $this->returnRepository =
            new ReturnRepository();


        $this->bookRepository =
            new BookRepository();


        $this->borrowRepository =
            new BorrowRepository();


        $this->userRepository =
            new UserRepository();
    }







    /*
        Return Book

        1. Increase Stock
        2. Update History
    */


    public function returnBook(
        $bookId,
        $userId
    ) {


        /*
            Step 1

            Increase Available Copies
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
            Step 2

            Update History
        */


        $statusUpdated =
            $this->returnRepository
            ->updateBorrowStatus(

                $bookId,

                $userId

            );

        if (!$statusUpdated) {
            return false;
        }

        $user =
            $this->userRepository
            ->getUserById($userId);

        $username =
            $user['name'] ?? $user['username'] ?? 'User';

        $title = 'Book Returned by User';
        $message = "{$username} returned book {$bookId}.";
        $type = 'Return';
        $icon = '↩️';

        $this->borrowRepository->sendNotification(
            $userId,
            $title,
            $message,
            $type,
            $icon
        );

        return true;
    }







    /*
        User Return History

        Get returned books
        belonging to current user
    */


    public function history(
        $userId
    ) {


        return
            $this->returnRepository
            ->getHistory(
                $userId
            );
    }
}
