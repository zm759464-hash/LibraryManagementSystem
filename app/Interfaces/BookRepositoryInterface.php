<?php


interface BookRepositoryInterface
{
    /*
    ========================================================
    GET ALL BOOKS
    ========================================================

    Get all books from the repository.

    In the distributed system, this can be
    implemented by the distributed query layer.
    ========================================================
    */

    public function getAllBooks();


    /*
    ========================================================
    FIND SINGLE BOOK
    ========================================================

    Find one book using Global Book ID.

    Example:
        TECH-xxxx
        SCI-xxxx
        FIC-xxxx
    ========================================================
    */

    public function findById(
        $id
    );


    /*
    ========================================================
    CREATE BOOK
    ========================================================

    Create a new distributed book.

    Category determines the target node:

        Technology -> library_node1
        Science    -> library_node2
        Fiction    -> library_node3
    ========================================================
    */

    public function create(
        $data
    );


    /*
    ========================================================
    UPDATE BOOK
    ========================================================

    Update a distributed book using Global Book ID.
    ========================================================
    */

    public function update(
        $id,
        $data
    );


    /*
    ========================================================
    DELETE BOOK
    ========================================================

    Delete a distributed book using Global Book ID.
    ========================================================
    */

    public function delete(
        $id
    );


    /*
    ========================================================
    BORROW BOOK
    ========================================================

    Borrow a book from its distributed node.

    The repository implementation handles:

        - Node resolution
        - Transaction
        - Row locking
        - Stock checking
        - available_copies - 1
    ========================================================
    */

    public function borrowBook(
        $globalBookId
    );


    /*
    ========================================================
    RETURN BOOK
    ========================================================

    Return a borrowed book to its distributed node.

    The repository implementation handles:

        available_copies + 1
    ========================================================
    */

    public function returnBook(
        $globalBookId
    );
}