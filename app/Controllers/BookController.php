<?php

/*
    ============================================================
    BOOK CONTROLLER
    ============================================================
*/


/*
    ============================================================
    Services
    ============================================================
*/

require_once
    __DIR__ . "/../Services/DistributedQueryService.php";


require_once
    __DIR__ . "/../Services/BookService.php";


/*
    ============================================================
    Repositories
    ============================================================
*/

require_once
    __DIR__ . "/../Repositories/BookRepository.php";


/*
    ============================================================
    Middleware
    ============================================================
*/

require_once
    __DIR__ . "/../Middleware/AuthMiddleware.php";


require_once
    __DIR__ . "/../Middleware/UserMiddleware.php";


require_once
    __DIR__ . "/../Middleware/AdminMiddleware.php";


class BookController
{


    /*
        ========================================================
        Get Book Service
        ========================================================
    */

    private function getBookService()
    {

        /*
            Repository
        */

        $repository =
            new BookRepository();


        /*
            Service
        */

        return
            new BookService(
                $repository
            );
    }

    /*
        Distributed Book List

        User:
            View Only

        Local Admin:
            View + Manage
    */

    public function index()
    {
        AuthMiddleware::check();

        $service =
            new DistributedQueryService();

        $books =
            $service->getAllBooks();


        $isAdmin =
            isset($_SESSION["user"])
            &&
            isset($_SESSION["user"]["local_role"])
            &&
            $_SESSION["user"]["local_role"]
            === "local_admin";


        echo "

        <link rel='stylesheet'
              href='/assets/css/book.css'>


        <div class='book-page'>


            <div class='book-container'>


                <!-- HEADER -->

                <div class='book-header'>

                    <div>

                        <div class='page-badge'>
                            📚 DISTRIBUTED LIBRARY
                        </div>

                        <h1>
                            Book Collection
                        </h1>

                        <p>
                            Explore books across the distributed library system.
                        </p>

                    </div>


                    <div class='header-actions'>

                        <a
                            href='?url=AdminController/dashboard'
                            class='secondary-btn'
                        >
                            ← Dashboard
                        </a>
        ";


        if ($isAdmin) {

            echo "

                        <a
                            href='?url=BookController/create'
                            class='primary-btn'
                        >
                            <span>＋</span>
                            Add New Book
                        </a>

            ";
        }


        echo "

                    </div>

                </div>


                <!-- STATS -->

                <div class='book-stats'>

                    <div class='book-stat-card'>

                        <div class='stat-icon purple'>
                            📚
                        </div>

                        <div>

                            <div class='stat-label'>
                                TOTAL BOOKS
                            </div>

                            <div class='stat-number'>
                                " . count($books) . "
                            </div>

                        </div>

                    </div>


                    <div class='book-stat-card'>

                        <div class='stat-icon blue'>
                            💻
                        </div>

                        <div>

                            <div class='stat-label'>
                                TECHNOLOGY
                            </div>

                            <div class='stat-number'>
        ";


        $technologyCount = 0;
        $scienceCount = 0;
        $fictionCount = 0;


        foreach ($books as $book) {

            $category =
                strtolower(
                    trim(
                        $book["category"] ?? ""
                    )
                );

            if ($category === "technology") {
                $technologyCount++;
            }

            if ($category === "science") {
                $scienceCount++;
            }

            if ($category === "fiction") {
                $fictionCount++;
            }
        }


        echo $technologyCount . "

                            </div>

                        </div>

                    </div>


                    <div class='book-stat-card'>

                        <div class='stat-icon green'>
                            🔬
                        </div>

                        <div>

                            <div class='stat-label'>
                                SCIENCE
                            </div>

                            <div class='stat-number'>
                                {$scienceCount}
                            </div>

                        </div>

                    </div>


                    <div class='book-stat-card'>

                        <div class='stat-icon orange'>
                            📖
                        </div>

                        <div>

                            <div class='stat-label'>
                                FICTION
                            </div>

                            <div class='stat-number'>
                                {$fictionCount}
                            </div>

                        </div>

                    </div>

                </div>


                <!-- BOOK TABLE -->

                <div class='books-card'>


                    <div class='books-card-header'>

                        <div>

                            <h2>
                                📚 Library Catalog
                            </h2>

                            <p>
                                All books available in the distributed nodes
                            </p>

                        </div>


                        <div class='catalog-badge'>
                            " . count($books) . " Books
                        </div>

                    </div>


                    <div class='table-wrapper'>

                        <table class='books-table'>


                            <thead>

                                <tr>

                                    <th>
                                        BOOK ID
                                    </th>

                                    <th>
                                        BOOK
                                    </th>

                                    <th>
                                        AUTHOR
                                    </th>

                                    <th>
                                        CATEGORY
                                    </th>

                                    <th>
                                        AVAILABLE
                                    </th>
        ";


        if ($isAdmin) {

            echo "

                                    <th>
                                        ACTION
                                    </th>

            ";
        }


        echo "

                                </tr>

                            </thead>


                            <tbody>
        ";


        if (empty($books)) {

            echo "

                                <tr>

                                    <td
                                        colspan='" .
                ($isAdmin ? "6" : "5") .
                "'
                                        class='empty-books'
                                    >

                                        <div class='empty-icon'>
                                            📚
                                        </div>

                                        <h3>
                                            No Books Found
                                        </h3>

                                        <p>
                                            There are currently no books
                                            in the distributed library.
                                        </p>

                                    </td>

                                </tr>

            ";
        } else {


            foreach ($books as $book) {


                $bookId =
                    htmlspecialchars(
                        $book["global_book_id"]
                            ?? ""
                    );


                $title =
                    htmlspecialchars(
                        $book["title"]
                            ?? "Unknown"
                    );


                $author =
                    htmlspecialchars(
                        $book["author"]
                            ?? "Unknown"
                    );


                $category =
                    htmlspecialchars(
                        $book["category"]
                            ?? "Unknown"
                    );


                $available =
                    htmlspecialchars(
                        $book["available_copies"]
                            ?? "0"
                    );


                $categoryClass =
                    strtolower(
                        $book["category"]
                            ?? ""
                    );


                echo "

                                <tr>


                                    <td>

                                        <span class='book-id'>
                                            {$bookId}
                                        </span>

                                    </td>


                                    <td>

                                        <div class='book-title-cell'>

                                            <div class='mini-book-icon'>
                                                📖
                                            </div>

                                            <div>

                                                <strong>
                                                    {$title}
                                                </strong>

                                                <small>
                                                    Library Book
                                                </small>

                                            </div>

                                        </div>

                                    </td>


                                    <td>

                                        <span class='author-name'>
                                            ✍️ {$author}
                                        </span>

                                    </td>


                                    <td>

                                        <span class='category-pill {$categoryClass}'>

                                            ";


                if ($categoryClass === "technology") {
                    echo "💻";
                } elseif ($categoryClass === "science") {
                    echo "🔬";
                } elseif ($categoryClass === "fiction") {
                    echo "📖";
                } else {
                    echo "📚";
                }


                echo "

                                            {$category}

                                        </span>

                                    </td>


                                    <td>

                                        <span class='availability ";

                if ((int)$available > 0) {
                    echo "available";
                } else {
                    echo "unavailable";
                }

                echo "'>

                                            " .
                    ($available > 0
                        ? "● Available"
                        : "● Unavailable"
                    ) .
                    "

                                            <strong>
                                                {$available}
                                            </strong>

                                        </span>

                                    </td>
        ";


                if ($isAdmin) {

                    echo "

                                    <td>

                                        <div class='action-buttons'>


                                            <a
                                                href='?url=BookController/edit/{$bookId}'
                                                class='edit-btn'
                                                title='Edit Book'
                                            >
                                                ✏️
                                            </a>


                                            <a
                                                href='?url=BookController/delete/{$bookId}'
                                                class='delete-btn'
                                                onclick=\"return confirm('Delete Book?')\"
                                                title='Delete Book'
                                            >
                                                🗑️
                                            </a>


                                        </div>

                                    </td>

                    ";
                }


                echo "

                                </tr>

                ";
            }
        }


        echo "

                            </tbody>

                        </table>

                    </div>

                </div>


            </div>

        </div>

        ";
    }



    /*
        Add New Book

        Local Admin Only
    */

    public function create()
    {
        AuthMiddleware::check();

        AdminMiddleware::check();


        if (
            $_SERVER["REQUEST_METHOD"]
            == "POST"
        ) {

            $service =
                $this->getBookService();


            $result =
                $service->addBook(

                    $_POST["title"],

                    $_POST["author"],

                    $_POST["category"],

                    $_POST["available_copies"]

                );


            if ($result) {

                echo "

                <link rel='stylesheet'
                      href='/assets/css/book.css'>


                <div class='book-page'>

                    <div class='form-result-card'>

                        <div class='success-icon'>
                            ✓
                        </div>

                        <h2>
                            Book Added Successfully
                        </h2>

                        <p>
                            The book has been added to the
                            distributed library system.
                        </p>

                        <a
                            href='?url=BookController/index'
                            class='primary-btn'
                        >
                            📚 View Books
                        </a>

                    </div>

                </div>

                ";
            } else {

                echo "

                <link rel='stylesheet'
                      href='/assets/css/book.css'>


                <div class='book-page'>

                    <div class='form-result-card error-card'>

                        <div class='error-icon'>
                            !
                        </div>

                        <h2>
                            Failed To Add Book
                        </h2>

                        <p>
                            Something went wrong while
                            adding the book.
                        </p>

                        <a
                            href='?url=BookController/create'
                            class='primary-btn'
                        >
                            Try Again
                        </a>

                    </div>

                </div>

                ";
            }

            return;
        }


        echo "

        <link rel='stylesheet'
              href='/assets/css/book.css'>


        <div class='book-page'>


            <div class='form-container'>


                <div class='form-top'>

                    <a
                        href='?url=BookController/index'
                        class='back-link'
                    >
                        ← Back to Books
                    </a>

                </div>


                <div class='form-header'>

                    <div class='form-icon'>
                        📚
                    </div>

                    <div>

                        <div class='page-badge'>
                            LIBRARY MANAGEMENT
                        </div>

                        <h1>
                            Add New Book
                        </h1>

                        <p>
                            Add a new book to the distributed
                            library collection.
                        </p>

                    </div>

                </div>


                <form
                    method='POST'
                    class='book-form'
                >


                    <div class='form-section-title'>
                        📖 Book Information
                    </div>


                    <div class='form-grid'>


                        <div class='form-group full-width'>

                            <label>
                                Book Title
                            </label>

                            <div class='input-box'>

                                <span>
                                    📚
                                </span>

                                <input
                                    name='title'
                                    placeholder='Enter book title'
                                    required
                                >

                            </div>

                        </div>


                        <div class='form-group'>

                            <label>
                                Author
                            </label>

                            <div class='input-box'>

                                <span>
                                    ✍️
                                </span>

                                <input
                                    name='author'
                                    placeholder='Enter author name'
                                    required
                                >

                            </div>

                        </div>


                        <div class='form-group'>

                            <label>
                                Category
                            </label>

                            <div class='input-box'>

                                <span>
                                    🗂️
                                </span>

                                <select
                                    name='category'
                                >

                                    <option value='Technology'>
                                        💻 Technology
                                    </option>

                                    <option value='Science'>
                                        🔬 Science
                                    </option>

                                    <option value='Fiction'>
                                        📖 Fiction
                                    </option>

                                </select>

                            </div>

                        </div>


                        <div class='form-group'>

                            <label>
                                Available Copies
                            </label>

                            <div class='input-box'>

                                <span>
                                    📦
                                </span>

                                <input
                                    type='number'
                                    name='available_copies'
                                    min='1'
                                    value='1'
                                    required
                                >

                            </div>

                        </div>


                    </div>


                    <div class='form-footer'>


                        <a
                            href='?url=BookController/index'
                            class='cancel-btn'
                        >
                            Cancel
                        </a>


                        <button
                            type='submit'
                            class='save-btn'
                        >
                            💾 Save Book
                        </button>


                    </div>


                </form>


            </div>

        </div>

        ";
    }



    /*
        Edit Book

        Local Admin Only
    */

    public function edit($id)
    {
        AuthMiddleware::check();

        AdminMiddleware::check();


        echo "

        <link rel='stylesheet'
              href='/assets/css/book.css'>


        <div class='book-page'>


            <div class='form-container'>


                <div class='form-top'>

                    <a
                        href='?url=BookController/index'
                        class='back-link'
                    >
                        ← Back to Books
                    </a>

                </div>


                <div class='form-header'>

                    <div class='form-icon edit-icon'>
                        ✏️
                    </div>

                    <div>

                        <div class='page-badge'>
                            LIBRARY MANAGEMENT
                        </div>

                        <h1>
                            Update Book
                        </h1>

                        <p>
                            Update information for book
                            <strong>{$id}</strong>
                        </p>

                    </div>

                </div>


                <form
                    method='POST'
                    action='?url=BookController/update/$id'
                    class='book-form'
                >


                    <div class='form-section-title'>
                        ✏️ Book Information
                    </div>


                    <div class='form-grid'>


                        <div class='form-group full-width'>

                            <label>
                                Book Title
                            </label>

                            <div class='input-box'>

                                <span>
                                    📚
                                </span>

                                <input
                                    name='title'
                                    placeholder='Enter book title'
                                    required
                                >

                            </div>

                        </div>


                        <div class='form-group'>

                            <label>
                                Author
                            </label>

                            <div class='input-box'>

                                <span>
                                    ✍️
                                </span>

                                <input
                                    name='author'
                                    placeholder='Enter author name'
                                    required
                                >

                            </div>

                        </div>


                        <div class='form-group'>

                            <label>
                                Category
                            </label>

                            <div class='input-box'>

                                <span>
                                    🗂️
                                </span>

                                <select
                                    name='category'
                                >

                                    <option value='Technology'>
                                        💻 Technology
                                    </option>

                                    <option value='Science'>
                                        🔬 Science
                                    </option>

                                    <option value='Fiction'>
                                        📖 Fiction
                                    </option>

                                </select>

                            </div>

                        </div>


                        <div class='form-group'>

                            <label>
                                Available Copies
                            </label>

                            <div class='input-box'>

                                <span>
                                    📦
                                </span>

                                <input
                                    type='number'
                                    name='copies'
                                    min='1'
                                    placeholder='Enter copies'
                                    required
                                >

                            </div>

                        </div>


                    </div>


                    <div class='form-footer'>


                        <a
                            href='?url=BookController/index'
                            class='cancel-btn'
                        >
                            Cancel
                        </a>


                        <button
                            type='submit'
                            class='save-btn'
                        >
                            ✓ Update Book
                        </button>


                    </div>


                </form>


            </div>

        </div>

        ";
    }



    /*
        Update Book

        Local Admin Only
    */

    public function update($id)
    {
        AuthMiddleware::check();

        AdminMiddleware::check();


        $service =
            $this->getBookService();


        $service->updateBook(

            $id,

            $_POST["title"],

            $_POST["author"],

            $_POST["category"],

            $_POST["copies"]

        );


        header(
            "Location:?url=BookController/index"
        );

        exit;
    }



    /*
        Delete Book

        Local Admin Only
    */

    public function delete($id)
    {
        AuthMiddleware::check();

        AdminMiddleware::check();


        $service =
            $this->getBookService();


        $service->deleteBook(
            $id
        );


        header(
            "Location:?url=BookController/index"
        );

        exit;
    }
}
