<?php

require_once "../vendor/autoload.php";

require_once "../app/Core/App.php";

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Distributed Library Management System</title>

    <link rel="stylesheet"
        href="/assets/css/style.css">

</head>

<body>

    <div class="site-wrapper">

        <?php

        new App();

        ?>

    </div>

</body>

</html>