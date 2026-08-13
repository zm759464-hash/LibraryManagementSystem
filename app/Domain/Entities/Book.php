<?php


class Book
{


    private $id;

    private $globalBookId;

    private $title;

    private $author;

    private $category;

    private $availableCopies;



    public function __construct(

        $globalBookId,

        $title,

        $author,

        $category,

        $availableCopies

    ) {


        $this->globalBookId =
            $globalBookId;


        $this->title =
            $title;


        $this->author =
            $author;


        $this->category =
            $category;


        $this->availableCopies =
            $availableCopies;
    }




    public function getTitle()
    {

        return $this->title;
    }



    public function getCategory()
    {

        return $this->category;
    }



    public function getCopies()
    {

        return $this->availableCopies;
    }
}
