
<?php


require_once
    "../app/Repositories/HoldRepository.php";


class HoldService
{


    private $repository;




    /*
        Constructor

        Repository Dependency
    */
    public function __construct()
    {


        $this->repository =
            new HoldRepository();
    }




    /*
        User Create Hold
    */
    public function create(
        $userId,
        $bookId
    ) {


        return
            $this->repository
            ->create(

                $userId,

                $bookId

            );
    }




    /*
        User View Own Holds
    */
    public function myHolds(
        $userId
    ) {


        return
            $this->repository
            ->myHolds(
                $userId
            );
    }




    /*
        Admin View All Holds
    */
    public function all()
    {


        return
            $this->repository
            ->all();
    }




    /*
        Admin Approve / Reject Hold
    */
    public function update(
        $id,
        $status
    ) {


        /*
            Only allow valid statuses
        */

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
