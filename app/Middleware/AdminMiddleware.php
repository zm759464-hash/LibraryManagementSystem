
<?php



class AdminMiddleware
{



    public static function check()
    {



        /*
            Start session only if
            session is not already active
        */



        if (
            session_status()
            === PHP_SESSION_NONE
        ) {

            session_start();
        }



        /*
            Check login
        */



        if (
            !isset($_SESSION["user"])
        ) {

            header(
                "Location:?url=AuthController/login"
            );

            exit;
        }



        /*
            Local Admin Permission
        */



        if (
            !isset(
                $_SESSION["user"]["local_role"]
            )
            ||
            $_SESSION["user"]["local_role"]
            !== "local_admin"
        ) {

            die("Access Denied");
        }
    }
}
