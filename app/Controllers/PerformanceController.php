<?php


require_once
    "../app/Services/Performance/PerformanceService.php";



class PerformanceController
{


    public function index()
    {


        $service =
            new PerformanceService();



        $data =
            $service->measure();




        echo "

        <h1>
        Distributed Database Performance Dashboard
        </h1>


        <hr>


        <table border='1'
        cellpadding='10'>


        <tr>

        <th>
        Node
        </th>

        <th>
        Status
        </th>

        <th>
        Books
        </th>

        <th>
        Response Time(ms)
        </th>


        </tr>


        ";



        foreach (
            [
                "library_node1",
                "library_node2",
                "library_node3"
            ]

            as $node
        ) {


            echo "

            <tr>


            <td>
            $node
            </td>


            <td>
            {$data[$node]['status']}
            </td>


            <td>
            {$data[$node]['books']}
            </td>


            <td>
            {$data[$node]['time']}
            </td>


            </tr>

            ";
        }



        echo "

        </table>


        <br>


        <h3>

        Total Query Time:

        {$data['total_time']} ms

        </h3>


        ";
    }
}
