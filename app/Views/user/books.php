<?php

$books = $books ?? [];

?>

<link rel="stylesheet" href="/assets/css/book.css">

<div class="user-books-page">

    <div class="user-books-container">

        <!-- =====================================================
             HEADER
        ====================================================== -->

        <div class="user-books-header">

            <div class="user-books-heading">

                <div class="user-books-badge">
                    📚 DISTRIBUTED LIBRARY
                </div>

                <h1>
                    Available Books
                </h1>

                <p>
                    Browse books from all distributed library nodes
                </p>

            </div>

            <a
                href="?url=UserController/dashboard"
                class="user-back-btn"
            >
                ← Back to Dashboard
            </a>

        </div>


        <!-- =====================================================
             CATEGORY FILTER
        ====================================================== -->

        <div class="user-category-bar">

            <a
                href="?url=UserController/books"
                class="category-filter-btn active"
            >
                📚 All Books
            </a>

            <a
                href="?url=UserController/books/Technology"
                class="category-filter-btn technology-filter"
            >
                💻 Technology
            </a>

            <a
                href="?url=UserController/books/Science"
                class="category-filter-btn science-filter"
            >
                🔬 Science
            </a>

            <a
                href="?url=UserController/books/Fiction"
                class="category-filter-btn fiction-filter"
            >
                📖 Fiction
            </a>

        </div>


        <!-- =====================================================
             BOOK COUNT
        ====================================================== -->

        <div class="books-summary">

            <div>
                <strong>
                    Library Collection
                </strong>

                <span>
                    <?= count($books) ?> books available in the system
                </span>
            </div>

            <div class="distributed-status">
                🟢 3 Nodes Connected
            </div>

        </div>


        <!-- =====================================================
             BOOK GRID
        ====================================================== -->

        <?php if (!empty($books)): ?>

            <div class="user-books-grid">

                <?php foreach ($books as $book): ?>

                    <?php

                    $bookId =
                        $book["global_book_id"]
                        ?? $book["global_id"]
                        ?? $book["id"]
                        ?? "";

                    $title =
                        $book["title"]
                        ?? "Untitled Book";

                    $author =
                        $book["author"]
                        ?? "Unknown Author";

                    $category =
                        $book["category"]
                        ?? "Unknown";

                    $available =
                        (int)(
                            $book["available_copies"]
                            ?? 0
                        );

                    $node =
                        $book["node"]
                        ?? $category;

                    $categoryClass =
                        strtolower(
                            preg_replace(
                                "/[^a-zA-Z0-9]+/",
                                "-",
                                $category
                            )
                        );

                    ?>

                    <article class="user-book-card">

                        <!-- BOOK COVER -->

                        <div
                            class="
                                user-book-cover
                                <?= htmlspecialchars(
                                    $categoryClass
                                ) ?>
                            "
                        >

                            <div class="book-cover-icon">
                                📚
                            </div>

                            <span>
                                <?= htmlspecialchars(
                                    $category
                                ) ?>
                            </span>

                        </div>


                        <!-- BOOK CONTENT -->

                        <div class="user-book-content">

                            <div class="user-book-title-row">

                                <h2>
                                    <?= htmlspecialchars(
                                        $title
                                    ) ?>
                                </h2>

                            </div>


                            <p class="user-book-author">

                                by
                                <strong>
                                    <?= htmlspecialchars(
                                        $author
                                    ) ?>
                                </strong>

                            </p>


                            <!-- BOOK ID -->

                            <?php if ($bookId !== ""): ?>

                                <div class="user-book-id">

                                    ID:
                                    <span>
                                        <?= htmlspecialchars(
                                            $bookId
                                        ) ?>
                                    </span>

                                </div>

                            <?php endif; ?>


                            <!-- META -->

                            <div class="user-book-meta">

                                <div class="book-meta-item">

                                    <span class="meta-label">
                                        Category
                                    </span>

                                    <span
                                        class="
                                            book-category-tag
                                            <?= htmlspecialchars(
                                                $categoryClass
                                            ) ?>
                                        "
                                    >
                                        <?= htmlspecialchars(
                                            $category
                                        ) ?>
                                    </span>

                                </div>


                                <div class="book-meta-item">

                                    <span class="meta-label">
                                        Available
                                    </span>

                                    <?php if ($available > 0): ?>

                                        <span class="stock available-stock">
                                            <span class="stock-dot"></span>
                                            <?= $available ?> copies
                                        </span>

                                    <?php else: ?>

                                        <span class="stock unavailable-stock">
                                            <span class="stock-dot"></span>
                                            Out of stock
                                        </span>

                                    <?php endif; ?>

                                </div>

                            </div>


                            <!-- NODE -->

                            <div class="user-book-node">

                                <span>
                                    🖥️ Node
                                </span>

                                <strong>
                                    <?= htmlspecialchars(
                                        $node
                                    ) ?>
                                </strong>

                            </div>


                            <!-- ACTIONS -->

                            <div class="user-book-actions">

                                <a
                                    href="?url=UserController/read/<?= urlencode($bookId) ?>"
                                    class="book-details-btn"
                                >
                                    Details
                                </a>


                                <?php if ($available > 0): ?>

                                    <a
                                        href="?url=BorrowController/borrow/<?= urlencode($bookId) ?>"
                                        class="book-borrow-btn"
                                    >
                                        Borrow
                                    </a>

                                <?php else: ?>

                                    <button
                                        type="button"
                                        class="book-borrow-btn disabled"
                                        disabled
                                    >
                                        Unavailable
                                    </button>

                                <?php endif; ?>

                            </div>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <!-- EMPTY -->

            <div class="user-books-empty">

                <div class="empty-book-icon">
                    📚
                </div>

                <h2>
                    No Books Found
                </h2>

                <p>
                    There are currently no books in this category.
                </p>

                <a
                    href="?url=UserController/books"
                    class="user-back-btn"
                >
                    View All Books
                </a>

            </div>

        <?php endif; ?>

    </div>

</div>