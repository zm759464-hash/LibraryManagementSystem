<?php

class ParallelSearchService
{
    public function search($keyword)
    {
        $nodes = require "../app/Config/nodes.php";

        $allBooks = [];

        foreach ($nodes as $nodeName => $config) {

            $connection = new mysqli(
    $config["host"],
    $config["user"],
    $config["password"],
    $config["database"],
    $config["port"]
);

            if ($connection->connect_error) {
                continue;
            }

            $keyword = $connection->real_escape_string($keyword);

            $sql = "
                SELECT
                    global_book_id,
                    title,
                    author,
                    category,
                    available_copies
                FROM books
                WHERE title LIKE '%$keyword%'
                OR author LIKE '%$keyword%'
            ";

            $result = $connection->query($sql);

            if ($result) {

                while ($row = $result->fetch_assoc()) {

                    $row["node"] = $nodeName;

                    $allBooks[] = $row;
                }
            }

            $connection->close();
        }

        return $allBooks;
    }
}
