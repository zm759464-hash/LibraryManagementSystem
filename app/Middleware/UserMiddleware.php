
<?php



class UserMiddleware
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
            Check whether user is logged in
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
            User permission

            Normal users:
                role = user

            Global users:
                global_role = global_user

            Local admins are also logged in users,
            so they are not blocked here.
        */



        $user =
            $_SESSION["user"];



        $isUser =
            (
                isset($user["role"])
                &&
                $user["role"] === "user"
            );



        $isGlobalUser =
            (
                isset($user["global_role"])
                &&
                $user["global_role"] === "global_user"
            );



        $isLocalAdmin =
            (
                isset($user["local_role"])
                &&
                $user["local_role"] === "local_admin"
            );



        if (
            !$isUser
            &&
            !$isGlobalUser
            &&
            !$isLocalAdmin
        ) {

            die("Access Denied");
        }
    }
}
