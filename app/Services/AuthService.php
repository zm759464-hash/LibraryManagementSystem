<?php


require_once "../app/Core/Database.php";


class AuthService
{

    private $db;


    public function __construct()
    {

        $database =
            new Database();

        $this->db =
            $database->getConnection();
    }



    public function login(
        $email,
        $password
    ) {


        $sql = "

        SELECT *

        FROM users

        WHERE email='$email'
        OR username='$email'

        ";


        $result =
            $this->db->query($sql);



        if ($result->num_rows == 1) {


            $user =
                $result->fetch_assoc();



            if (
                password_verify(
                    $password,
                    $user["password"]
                )
            ) {

                return $user;
            }
        }



        return false;
    }




    public function register(
        $username,
        $name,
        $email,
        $password,
        $role
    ) {


        // Password Length

        if (
            strlen($password) < 8 ||
            strlen($password) > 16
        ) {

            return "Password must be 8-16 characters";
        }



        // Capital Letter

        if (!preg_match('/[A-Z]/', $password)) {

            return "Password needs capital letter";
        }



        // Small Letter

        if (!preg_match('/[a-z]/', $password)) {

            return "Password needs small letter";
        }



        // Digit

        if (!preg_match('/[0-9]/', $password)) {

            return "Password needs digit";
        }



        // Special Character

        if (!preg_match('/[\W]/', $password)) {

            return "Password needs special character";
        }



        // Duplicate Check


        $check = $this->db->query(

            "
    SELECT *
    FROM users

    WHERE username='$username'
    OR email='$email'
    "

        );



        if ($check->num_rows > 0) {

            return "Username or Email already exists";
        }




        // Hash Password


        $hash =
            password_hash(
                $password,
                PASSWORD_DEFAULT
            );




        if ($role == "admin") {

            $localRole =
                "local_admin";

            $globalRole =
                NULL;
        } else {

            $localRole =
                NULL;

            $globalRole =
                "global_user";
        }




        $sql = "


    INSERT INTO users

    (

        username,

        name,

        email,

        password,

        role,

        local_role,

        global_role

    )


    VALUES

    (

        '$username',

        '$name',

        '$email',

        '$hash',

        '$role',

        '$localRole',

        '$globalRole'

    )


    ";



        if ($this->db->query($sql)) {

            return true;
        }


        return false;
    }
}
