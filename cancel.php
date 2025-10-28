<?php
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
    // Delete the single booking record (which covers all nights)
    $db->prepare("DELETE FROM bookings WHERE id = ?")->execute([$_POST['booking_id']]);
    $message = "Reservation canceled.";
}

// Fetch all active bookings
$reservations = $db->query("
    SELECT b.*, r.category, r.room_number
    FROM bookings b
    JOIN rooms r ON r.id = b.room_id
    ORDER BY b.check_in_date DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<?php $pageTitle = "Cancel Reservation"; include 'includes/header.php'; ?>

<div class="form-container">
    <h2>Reservations</h2>
    <?php if (!empty($message)): ?>
        <div class="alert success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if (empty($reservations)): ?>
        <p>No active reservations found.</p>
    <?php else: ?>
        <div class="reservations-list">
            <?php foreach ($reservations as $r): ?>
                <div class="reservation-item">
                    <p><strong><?= htmlspecialchars($r['full_name']) ?></strong></p>
                    <p>
                        <?= ucfirst($r['category']) ?> Room #<?= $r['room_number'] ?> 
                        • <?= $r['check_in_date'] ?> to <?= $r['check_out_date'] ?>
                    </p>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="booking_id" value="<?= $r['id'] ?>">
                        <button type="submit" class="btn-cancel" 
                                onclick="return confirm('Cancel reservation for <?= htmlspecialchars($r['full_name']) ?>?');">
                            Cancel
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
