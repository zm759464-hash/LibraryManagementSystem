<?php


interface BookRepositoryInterface
{


    public function getAllBooks();



    public function findById(
        $id
    );



    public function create(
        $data
    );



    public function update(
        $id,
        $data
    );



    public function delete(
        $id
    );

    public function borrowBook(
        $globalBookId
    );
}
