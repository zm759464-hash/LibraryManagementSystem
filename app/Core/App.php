<?php


class App
{


    protected $controller = "AuthController";


    protected $method = "login";


    protected $params = [];





    public function __construct()
    {


        $url =
            $this->parseUrl();




        /*
            Controller Resolve
        */


        if (
            isset($url[0]) &&
            $url[0] != ""
        ) {


            $controllerFile =
                "../app/Controllers/"
                .
                $url[0]
                .
                ".php";



            if (
                file_exists($controllerFile)
            ) {


                $this->controller =
                    $url[0];


                unset($url[0]);
            } else {

                die("Controller "
                    .
                    $url[0]
                    .
                    " not found");
            }
        }






        /*
            Load Controller
        */


        require_once
            "../app/Controllers/"
            .
            $this->controller
            .
            ".php";





        $controller =
            new $this->controller();








        /*
            Resolve Method
        */



        if (
            isset($url[1]) &&
            $url[1] != ""
        ) {


            if (
                method_exists(
                    $controller,
                    $url[1]
                )
            ) {


                $this->method =
                    $url[1];


                unset($url[1]);
            } else {


                die("Method "
                    .
                    $url[1]
                    .
                    " not found");
            }
        }








        /*
            Parameters
        */



        $this->params =
            $url
            ?
            array_values($url)
            :
            [];








        /*
            Execute Controller Method
        */



        call_user_func_array(

            [
                $controller,
                $this->method
            ],

            $this->params

        );
    }









    private function parseUrl()
    {


        if (
            isset($_GET['url'])
        ) {


            return explode(

                '/',

                filter_var(

                    rtrim(
                        $_GET['url'],
                        '/'
                    ),

                    FILTER_SANITIZE_URL

                )

            );
        }



        return [];
    }
}
