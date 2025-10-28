<?php
// includes/header.php
$pageTitle = $pageTitle ?? 'LuxStay Hotel';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">  <!-- ← This is the CSS link -->
</head>
<body>
    <header>
        <h1>LuxStay Hotel</h1>
        <nav>
            <a href="<?= basename($_SERVER['SCRIPT_NAME']) === 'index.php' ? './' : './index.php' ?>">Rooms</a>
            <a href="<?= basename($_SERVER['SCRIPT_NAME']) === 'book.php' ? './' : './book.php' ?>">Book Now</a>
            <a href="<?= basename($_SERVER['SCRIPT_NAME']) === 'cancel.php' ? './' : './cancel.php' ?>">Cancel</a>
            <a href="<?= basename($_SERVER['SCRIPT_NAME']) === 'calendar.php' ? './' : './calendar.php' ?>">Availability</a>
        </nav>
    </header>

    <main>
