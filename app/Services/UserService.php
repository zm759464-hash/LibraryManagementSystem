<?php


require_once
    "../app/Repositories/UserRepository.php";



class UserService
{


    private $repository;



    public function __construct()
    {

        $this->repository =
            new UserRepository();
    }




    public function allUsers()
    {

        return
            $this->repository
            ->getAllUsers();
    }




    public function add(
        $name,
        $email,
        $password,
        $role
    ) {

        // မူရင်း codes ဖွဲ့စည်းပုံမပျက်စေဘဲ password ကို hash လုပ်ပေးလိုက်ခြင်း
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        return
            $this->repository
            ->addUser(
                $name,
                $email,
                $hashedPassword,
                $role
            );
    }


    public function create(
        $username,
        $password,
        $name,
        $email,
        $role
    ) {

        // မူရင်း codes ဖွဲ့စည်းပုံမပျက်စေဘဲ password ကို hash လုပ်ပေးလိုက်ခြင်း
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        return
            $this->repository
            ->createUser(
                $username,
                $hashedPassword,
                $name,
                $email,
                $role
            );
    }

    public function delete($id)
    {

        return
            $this->repository
            ->deleteUser($id);
    }




    public function update(
        $id,
        $username,
        $name,
        $email,
        $role,
        $profileImage = null
    ) {


        return
            $this->repository
            ->updateUser(

                $id,

                $username,

                $name,

                $email,

                $role,

                $profileImage

            );
    }


    public function changePassword($id, $newPassword)
    {
        $hashed = password_hash($newPassword, PASSWORD_BCRYPT);

        return $this->repository->updatePassword($id, $hashed);
    }


    public function find($id)
    {

        return
            $this->repository
            ->getUserById($id);
    }
}
