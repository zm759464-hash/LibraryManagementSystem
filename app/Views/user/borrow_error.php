<?php

$title =
    isset($error["title"])
    ? $error["title"]
    : "Borrow Failed";

$message =
    isset($error["message"])
    ? $error["message"]
    : "Unable to complete the borrow request.";

$bookId =
    isset($error["book_id"])
    ? $error["book_id"]
    : "";

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= htmlspecialchars(
            $title,
            ENT_QUOTES,
            "UTF-8"
        ) ?>
    </title>

    <link
        rel="stylesheet"
        href="assets/css/borrow.css"
    >

</head>

<body>

    <div class="borrow-success-page">

        <div class="borrow-success-card">

            <div class="borrow-success-icon borrow-error-icon">
                !
            </div>


            <h1>

                <?= htmlspecialchars(
                    $title,
                    ENT_QUOTES,
                    "UTF-8"
                ) ?>

            </h1>


            <p class="borrow-success-message">

                <?= htmlspecialchars(
                    $message,
                    ENT_QUOTES,
                    "UTF-8"
                ) ?>

            </p>


            <?php if ($bookId !== ""): ?>

                <div class="borrow-details">

                    <div class="borrow-detail-row">

                        <span>
                            Book ID
                        </span>

                        <strong class="book-id">

                            <?= htmlspecialchars(
                                $bookId,
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>

                        </strong>

                    </div>

                </div>

            <?php endif; ?>


            <a
                href="?url=UserController/books"
                class="borrow-back-button"
            >
                Back to Books
            </a>

        </div>

    </div>

</body>

</html>