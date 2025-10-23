<?php include 'includes/db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Aura Luxe Hotel</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <h1>Aura Luxe Hotel</h1>
        <nav>
            <a href="index.php">Rooms</a>
            <a href="book.php">Book Now</a>
            <a href="cancel.php">Cancel</a>
            <a href="calendar.php">Availability</a>
        </nav>
    </header>

    <main>
        <div class="hero">
            <div class="overlay">
                <h2>Elegant Stays, Unforgettable Moments</h2>
            </div>
        </div>

        <section class="rooms">
            <?php
            $categories = ['deluxe', 'economy', 'regular', 'vip'];
            $prices = ['deluxe' => 5000, 'economy' => 1500, 'regular' => 2500, 'vip' => 10000];
            foreach ($categories as $cat): ?>
                <div class="room-card">
                    <img src="assets/img/<?= $cat ?>.jpg" alt="<?= ucfirst($cat) ?> Room">
                    <h3><?= ucfirst($cat) ?> Suite</h3>
                    <p>₱<?= number_format($prices[$cat]) ?> / night</p>
                </div>
            <?php endforeach; ?>
        </section>
    </main>
<script src="assets/js/main.js"></script>
</body>
</html>