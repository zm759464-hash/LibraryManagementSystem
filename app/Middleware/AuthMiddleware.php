<?php


class AuthMiddleware
{


    public static function check()
    {


        session_start();



        if (!isset($_SESSION["user"])) {

            header(
                "Location:?url=AuthController/login"
            );

            exit;
        }
    }
}
