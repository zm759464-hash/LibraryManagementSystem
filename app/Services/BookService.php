<?php


require_once
    "../app/Interfaces/BookRepositoryInterface.php";



class BookService
{


    private BookRepositoryInterface $repository;



    /*
        Dependency Injection
    */
    public function __construct(
        BookRepositoryInterface $repository
    ) {

        $this->repository = $repository;
    }





    /*
        Get All Books
    */
    public function getAllBooks()
    {

        return
            $this->repository
            ->getAllBooks();
    }






    /*
        Add New Distributed Book
    */
    public function addBook(
        $title,
        $author,
        $category,
        $availableCopies
    ) {


        $data = [

            "title" =>
            $title,


            "author" =>
            $author,


            "category" =>
            $category,


            "available_copies" =>
            $availableCopies

        ];



        return
            $this->repository
            ->create($data);
    }







    /*
        Update Book
    */
    public function updateBook(
        $id,
        $title,
        $author,
        $category,
        $copies
    ) {


        $data = [

            "title" =>
            $title,


            "author" =>
            $author,


            "category" =>
            $category,


            "available_copies" =>
            $copies

        ];



        return
            $this->repository
            ->update(

                $id,

                $data

            );
    }

    /*
        Delete Book
    */
    public function deleteBook(
        $id
    ) {


        return
            $this->repository
            ->delete($id);
    }







    /*
        Find Single Book
    */
    public function findBook(
        $id
    ) {


        return
            $this->repository
            ->findById($id);
    }


    public function borrowBook($globalBookId)
    {

        return
            $this->repository
            ->borrowBook(
                $globalBookId
            );
    }
}
