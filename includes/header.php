<!-- includes/header.php -->
<header>
    <h1>Aura Luxe Hotel</h1>
    <nav>
        <a href="<?= $_SERVER['SCRIPT_NAME'] === '/index.php' ? './' : './index.php' ?>">Rooms</a>
        <a href="<?= $_SERVER['SCRIPT_NAME'] === '/book.php' ? './' : './book.php' ?>">Book Now</a>
        <a href="<?= $_SERVER['SCRIPT_NAME'] === '/cancel.php' ? './' : './cancel.php' ?>">Cancel</a>
        <a href="<?= $_SERVER['SCRIPT_NAME'] === '/calendar.php' ? './' : './calendar.php' ?>">Availability</a>
    </nav>
</header>