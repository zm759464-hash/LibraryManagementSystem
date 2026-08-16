<?php

$book = $book ?? [];


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


$description =
    trim(
        $book["description"]
        ?? ""
    );


if ($description === "") {

    $description =
        "No description available for this book.";
}


$categoryClass =
    strtolower(
        preg_replace(
            "/[^a-zA-Z0-9]+/",
            "-",
            $category
        )
    );

?>

<link
    rel="stylesheet"
    href="/assets/css/book.css"
>


<div class="book-details-page">

    <div class="book-details-container">


        <!-- =====================================================
             TOP NAVIGATION
        ====================================================== -->

        <div class="details-topbar">

            <a
                href="?url=UserController/books"
                class="details-back-btn"
            >
                ← Back to Books
            </a>

            <div class="details-breadcrumb">

                Library

                <span>›</span>

                <?= htmlspecialchars($category) ?>

                <span>›</span>

                Details

            </div>

        </div>


        <!-- =====================================================
             MAIN DETAILS CARD
        ====================================================== -->

        <div class="book-details-card">


            <!-- =================================================
                 LEFT BOOK COVER
            ================================================== -->

            <div
                class="
                    details-book-cover
                    <?= htmlspecialchars($categoryClass) ?>
                "
            >

                <div
                    class="details-cover-decoration decoration-one">
                </div>

                <div
                    class="details-cover-decoration decoration-two">
                </div>


                <div class="details-cover-icon">
                    📚
                </div>


                <div class="details-cover-category">

                    <?= htmlspecialchars($category) ?>

                </div>


                <?php if ($bookId !== ""): ?>

                    <div class="details-cover-id">

                        <?= htmlspecialchars($bookId) ?>

                    </div>

                <?php endif; ?>

            </div>


            <!-- =================================================
                 RIGHT CONTENT
            ================================================== -->

            <div class="details-book-content">


                <!-- CATEGORY -->

                <div
                    class="
                        details-category
                        <?= htmlspecialchars($categoryClass) ?>
                    "
                >

                    <?= htmlspecialchars($category) ?>

                </div>


                <!-- TITLE -->

                <h1 class="details-book-title">

                    <?= htmlspecialchars($title) ?>

                </h1>


                <!-- AUTHOR -->

                <div class="details-author">

                    <span class="author-label">
                        Written by
                    </span>

                    <strong>
                        <?= htmlspecialchars($author) ?>
                    </strong>

                </div>


                <!-- DESCRIPTION -->

                <div class="details-description">

                    <h2>
                        About this book
                    </h2>

                    <p>
                        <?= htmlspecialchars($description) ?>
                    </p>

                </div>


                <!-- =================================================
                     INFORMATION GRID
                ================================================== -->

                <div class="details-info-grid">


                    <!-- AVAILABLE -->

                    <div class="details-info-box">

                        <div
                            class="details-info-icon available-icon"
                        >
                            ✓
                        </div>

                        <div>

                            <span class="details-info-label">
                                Availability
                            </span>

                            <?php if ($available > 0): ?>

                                <strong class="available-text">

                                    <?= $available ?>

                                    <?= $available === 1
                                        ? "copy"
                                        : "copies"
                                    ?>

                                </strong>

                            <?php else: ?>

                                <strong class="unavailable-text">
                                    Out of stock
                                </strong>

                            <?php endif; ?>

                        </div>

                    </div>


                    <!-- NODE -->

                    <div class="details-info-box">

                        <div
                            class="details-info-icon node-icon"
                        >
                            🖥
                        </div>

                        <div>

                            <span class="details-info-label">
                                Distributed Node
                            </span>

                            <strong>
                                <?= htmlspecialchars($category) ?>
                            </strong>

                        </div>

                    </div>


                    <!-- BOOK ID -->

                    <div class="details-info-box">

                        <div
                            class="details-info-icon id-icon"
                        >
                            #
                        </div>

                        <div>

                            <span class="details-info-label">
                                Global Book ID
                            </span>

                            <strong
                                class="details-global-id"
                            >
                                <?= htmlspecialchars($bookId) ?>
                            </strong>

                        </div>

                    </div>


                    <!-- STATUS -->

                    <div class="details-info-box">

                        <div
                            class="details-info-icon status-icon"
                        >
                            ●
                        </div>

                        <div>

                            <span class="details-info-label">
                                Status
                            </span>

                            <?php if ($available > 0): ?>

                                <strong class="available-text">
                                    Available
                                </strong>

                            <?php else: ?>

                                <strong class="unavailable-text">
                                    Out of stock
                                </strong>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     ACTIONS
                ================================================== -->

                <div class="details-actions">


                    <?php if ($available > 0): ?>

                        <!-- =========================================
                             AVAILABLE BOOK
                        ========================================== -->

                        <a
                            href="?url=BorrowController/borrow/<?= urlencode($bookId) ?>"
                            class="details-borrow-btn"
                        >
                            <span>📚</span>
                            Borrow This Book
                        </a>


                        <!-- HOLD DISABLED -->

                        <div class="details-hold-disabled">

                            <span class="hold-disabled-icon">
                                🔖
                            </span>

                            <div>

                                <strong>
                                    Hold Not Available
                                </strong>

                                <small>

                                    <?= $available ?>

                                    <?= $available === 1
                                        ? "copy is"
                                        : "copies are"
                                    ?>

                                    currently available.

                                </small>

                            </div>

                        </div>


                    <?php else: ?>

                        <!-- =========================================
                             OUT OF STOCK BOOK
                        ========================================== -->

                        <button
                            type="button"
                            class="
                                details-borrow-btn
                                disabled
                            "
                            disabled
                        >
                            <span>✕</span>
                            Currently Unavailable
                        </button>


                        <!-- HOLD ALLOWED -->

                        <a
                            href="?url=HoldController/create/<?= urlencode($bookId) ?>"
                            class="details-hold-btn"
                        >
                            <span>🔖</span>
                            Place Hold
                        </a>


                    <?php endif; ?>


                </div>


                <!-- =================================================
                     SMALL NOTE
                ================================================== -->

                <div class="details-note">

                    <?php if ($available > 0): ?>

                        🟢 This book is currently available.
                        You can borrow it directly.

                    <?php else: ?>

                        🔖 This book is currently out of stock.
                        Place a hold request to be notified
                        when it becomes available.

                    <?php endif; ?>

                </div>

            </div>

        </div>


        <!-- =====================================================
             DISTRIBUTED SYSTEM INFORMATION
        ====================================================== -->

        <div class="details-system-card">

            <div class="system-card-icon">
                🌐
            </div>

            <div class="system-card-content">

                <h3>
                    Distributed Library System
                </h3>

                <p>

                    This book is stored and managed by the

                    <strong>
                        <?= htmlspecialchars($category) ?>
                    </strong>

                    distributed database node.

                </p>

            </div>

            <div class="system-status">

                <span></span>

                Node Online

            </div>

        </div>


    </div>

</div>