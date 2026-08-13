<?php
$booksView = [];
if (!empty($books)) {
    foreach ($books as $book) {
        $bookId = htmlspecialchars($book["global_id"] ?? $book["global_book_id"] ?? $book["book_id"] ?? $book["id"] ?? "");
        $title = htmlspecialchars($book["title"] ?? "Unknown");
        $author = htmlspecialchars($book["author"] ?? "Unknown");
        $category = htmlspecialchars($book["category"] ?? "Unknown");
        $available = htmlspecialchars($book["available_copies"] ?? "0");
        $node = htmlspecialchars($book["node"] ?? "-");

        $booksView[] = [
            'id' => $bookId,
            'title' => $title,
            'author' => $author,
            'category' => $category,
            'available' => $available,
            'node' => $node,
        ];
    }
}
?>

<link rel='stylesheet' href='assets/css/user-books.css'>

<div class='user-books-page'>
    <h1>Available Books</h1>
    <a class='user-books-back' href='?url=UserController/dashboard'>Back</a>

    <?php if (empty($booksView)) : ?>
        <p>No books available.</p>
    <?php else : ?>
        <div class='user-books-list'>
            <?php foreach ($booksView as $book) : ?>
                <div class='user-book-card'>
                    <div class='book-card-top'>
                        <div class='book-cover'>📘</div>
                        <div class='book-meta'>
                            <h3 class='book-title'><?php echo $book['title']; ?></h3>
                            <div class='book-author'><?php echo $book['author']; ?></div>
                            <div class='book-category'><?php echo $book['category']; ?></div>
                        </div>
                    </div>

                    <div class='book-card-bottom'>
                        <div class='book-info'>
                            <div class='book-availability'>Available: <strong><?php echo $book['available']; ?></strong></div>
                            <div class='book-node'>Node: <?php echo $book['node']; ?></div>
                        </div>

                        <div class='user-book-actions'>
                            <a class='btn btn-outline' href='?url=UserController/read/<?php echo $book['id']; ?>'>Details</a>
                            <a class='btn btn-primary' href='?url=BorrowController/borrow/<?php echo $book['id']; ?>'>Borrow</a>
                            <a class='btn btn-secondary' href='?url=HoldController/create/<?php echo $book['id']; ?>'>Hold</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>