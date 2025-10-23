<?php
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reservation_id'])) {
    $id = $_POST['reservation_id'];
    // Only allow cancellation if not 'AVAILABLE'
    $db->prepare("UPDATE reservations SET full_name='AVAILABLE', address='', id_type='', payment_method='', check_in_date='AVAILABLE' WHERE id = ? AND full_name != 'AVAILABLE'")->execute([$id]);
    $message = "Reservation canceled.";
}
?>
<?php include 'includes/header.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Cancel Reservation</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <main>
        <h2>My Reservations</h2>
        <?php if (!empty($message)): ?><p class="alert success"><?= $message ?></p><?php endif; ?>

        <div class="reservations-list">
            <?php
            $reservations = $db->query("SELECT * FROM reservations WHERE full_name != 'AVAILABLE' ORDER BY check_in_date DESC");
            while ($r = $reservations->fetch()): ?>
                <div class="reservation-item">
                    <p><strong><?= htmlspecialchars($r['full_name']) ?></strong></p>
                    <p><?= ucfirst($r['room_category']) ?> Room #<?= $r['room_number'] ?> • <?= $r['check_in_date'] ?></p>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="reservation_id" value="<?= $r['id'] ?>">
                        <button type="submit" class="btn-cancel">Cancel</button>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>
    </main>
    <!-- Add before </body> in all PHP files -->
<script src="assets/js/main.js"></script>
</body>
</html>