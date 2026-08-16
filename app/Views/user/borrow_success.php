<?php

$bookId =
    isset($data["book_id"])
    ? $data["book_id"]
    : "";

$borrowDate =
    isset($data["borrow_date"])
    ? $data["borrow_date"]
    : "";

$dueDate =
    isset($data["due_date"])
    ? $data["due_date"]
    : "";

$loanDays =
    isset($data["loan_days"])
    ? $data["loan_days"]
    : 14;

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
        Borrow Successful
    </title>

    <link
        rel="stylesheet"
        href="assets/css/borrow.css"
    >

</head>

<body>

    <div class="borrow-success-page">

        <div class="borrow-success-card">

            <div class="borrow-success-icon">
                ✓
            </div>


            <h1>
                Borrow Successful
            </h1>


            <p class="borrow-success-message">
                Your book has been successfully borrowed.
            </p>


            <div class="borrow-details">

                <div class="borrow-detail-row">

                    <span>
                        Borrow Date
                    </span>

                    <strong>
                        <?= htmlspecialchars(
                            $borrowDate,
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>
                    </strong>

                </div>


                <div class="borrow-detail-row">

                    <span>
                        Due Date
                    </span>

                    <strong class="due-date">

                        <?= htmlspecialchars(
                            $dueDate,
                            ENT_QUOTES,
                            "UTF-8"
                        ) ?>

                    </strong>

                </div>


                <div class="borrow-detail-row">

                    <span>
                        Loan Period
                    </span>

                    <strong>
                        <?= (int) $loanDays ?> Days
                    </strong>

                </div>


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


            <div class="borrow-notice">

                Please return the book before the due date.

            </div>


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