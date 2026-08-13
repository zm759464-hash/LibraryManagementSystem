<?php
$userData = $user ?? ($_SESSION["user"] ?? []);
$bookId = htmlspecialchars($id ?? "");
$foundBook = $foundBook ?? null;

if (!$foundBook) {
    echo "
    <div class='book-not-found'>
        <div class='book-not-found-icon'>📚</div>
        <h2>Book Not Found</h2>
        <p>The requested book could not be found in the distributed library.</p>
        <a class='book-not-found-link' href='?url=UserController/books'>← Back to Books</a>
    </div>
    ";
    return;
}

$title = htmlspecialchars($foundBook["title"] ?? "Unknown Title");
$author = htmlspecialchars($foundBook["author"] ?? "Unknown Author");
$category = htmlspecialchars($foundBook["category"] ?? "Unknown");
$available = htmlspecialchars($foundBook["available_copies"] ?? "0");
$node = htmlspecialchars($foundBook["node"] ?? "-");
$description = htmlspecialchars($foundBook["description"] ?? "No description available for this book.");
?>

<link rel='stylesheet' href='assets/css/user-books.css'>
<link rel='stylesheet' href='assets/css/user-read.css'>

<div class='user-books-page'>
    <a class='user-books-back' href='?url=UserController/books'>← Back to Books</a>
    <div class='user-book-detail'>
        <h1><?php echo $title; ?></h1>
        <p><strong>Author:</strong> <?php echo $author; ?></p>
        <p><strong>Category:</strong> <?php echo $category; ?></p>
        <p><strong>Available:</strong> <?php echo $available; ?></p>
        <p><strong>Node:</strong> <?php echo $node; ?></p>
        <p><strong>Description:</strong> <?php echo $description; ?></p>
    </div>
    <div class='user-book-actions'>
        <a href='?url=BorrowController/borrow/<?php echo $bookId; ?>'>Borrow</a>
        <a href='?url=HoldController/create/<?php echo $bookId; ?>'>Place Hold</a>
    </div>
</div>