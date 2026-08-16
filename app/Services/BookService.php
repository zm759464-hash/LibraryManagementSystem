<?php

require_once
    __DIR__ . "/../Interfaces/BookRepositoryInterface.php";


class BookService
{
    /*
        ========================================================
        Properties
        ========================================================
    */

    private BookRepositoryInterface $repository;


    /*
        ========================================================
        Constructor
        ========================================================
    */

    public function __construct(
        BookRepositoryInterface $repository
    ) {

        $this->repository =
            $repository;
    }


    /*
        ========================================================
        GET ALL BOOKS
        ========================================================

        Responsibility:

            Service
                ↓
            Repository
                ↓
            Database
    */

    public function getAllBooks()
    {
        $result =
            $this->repository
            ->getAllBooks();


        /*
            Safety fallback
        */

        if ($result === false) {

            return [];
        }


        return $result;
    }


    /*
        ========================================================
        ADD BOOK
        ========================================================
    */

    public function addBook(
        $title,
        $author,
        $category,
        $availableCopies
    ) {

        /*
            Clean input
        */

        $title =
            trim(
                (string)$title
            );


        $author =
            trim(
                (string)$author
            );


        $category =
            trim(
                (string)$category
            );


        $availableCopies =
            (int)$availableCopies;


        /*
            Validate required fields
        */

        if (
            $title === "" ||
            $author === "" ||
            $category === ""
        ) {

            return false;
        }


        /*
            Available copies
            cannot be negative.
        */

        if (
            $availableCopies < 0
        ) {

            $availableCopies = 0;
        }


        /*
            Prepare repository data
        */

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


        /*
            Create book
        */

        return
            $this->repository
            ->create(
                $data
            );
    }


    /*
        ========================================================
        UPDATE BOOK
        ========================================================
    */

    public function updateBook(
        $id,
        $title,
        $author,
        $category,
        $copies
    ) {

        /*
            Clean input
        */

        $id =
            trim(
                (string)$id
            );


        $title =
            trim(
                (string)$title
            );


        $author =
            trim(
                (string)$author
            );


        $category =
            trim(
                (string)$category
            );


        $copies =
            (int)$copies;


        /*
            Validate
        */

        if (
            $id === "" ||
            $title === "" ||
            $author === "" ||
            $category === ""
        ) {

            return false;
        }


        /*
            Prevent negative stock
        */

        if (
            $copies < 0
        ) {

            $copies = 0;
        }


        /*
            Prepare update data
        */

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


        /*
            Update repository
        */

        return
            $this->repository
            ->update(
                $id,
                $data
            );
    }


    /*
        ========================================================
        DELETE BOOK
        ========================================================
    */

    public function deleteBook(
        $id
    ) {

        $id =
            trim(
                (string)$id
            );


        if (
            $id === ""
        ) {

            return false;
        }


        return
            $this->repository
            ->delete(
                $id
            );
    }


    /*
        ========================================================
        FIND BOOK
        ========================================================
    */

    public function findBook(
        $id
    ) {

        $id =
            trim(
                (string)$id
            );


        if (
            $id === ""
        ) {

            return null;
        }


        return
            $this->repository
            ->findById(
                $id
            );
    }


    /*
        ========================================================
        BORROW BOOK
        ========================================================

        This method is kept for compatibility
        with existing BookController code.

        Distributed stock operation is handled
        by BookRepository.
    */

    public function borrowBook(
        $globalBookId
    ) {

        $globalBookId =
            trim(
                (string)$globalBookId
            );


        if (
            $globalBookId === ""
        ) {

            return false;
        }


        return
            $this->repository
            ->borrowBook(
                $globalBookId
            );
    }
}