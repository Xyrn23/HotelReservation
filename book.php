<?php
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['full_name'];
    $address = $_POST['address'];
    $id_type = $_POST['id_type'];
    $payment = $_POST['payment_method'];
    $category = $_POST['room_category'];
    $date = $_POST['check_in_date'];

    // Find first available room in category
    $stmt = $db->prepare("
        SELECT room_number FROM reservations 
        WHERE room_category = ? AND full_name = 'AVAILABLE' 
        ORDER BY room_number LIMIT 1
    ");
    $stmt->execute([$category]);
    $room = $stmt->fetch();

    if ($room) {
        $db->prepare("
            UPDATE reservations 
            SET full_name = ?, address = ?, id_type = ?, payment_method = ?, check_in_date = ?
            WHERE room_category = ? AND room_number = ?
        ")->execute([$name, $address, $id_type, $payment, $date, $category, $room['room_number']]);
        $success = "Booking confirmed! Room: $category-" . $room['room_number'];
    } else {
        $error = "No available rooms in selected category.";
    }
}
?>
<?php include 'includes/header.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Book a Room</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <main class="form-container">
        <h2>Book Your Stay</h2>
        <?php if (!empty($success)): ?>
            <div class="alert success"><?= $success ?></div>
        <?php elseif (!empty($error)): ?>
            <div class="alert error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="full_name" placeholder="Full Name" required>
            <textarea name="address" placeholder="Address" required></textarea>

            <select name="id_type" required>
                <option value="">Select ID Type</option>
                <option value="National ID">National ID</option>
                <option value="SSS">SSS</option>
                <option value="PAGIBIG">PAGIBIG</option>
                <option value="Philhealth">Philhealth</option>
            </select>

<!-- ... existing form fields ... -->

<select name="payment_method" id="payment-method" required>
    <option value="">Payment Method</option>
    <option value="Credit Card">Credit Card</option>
    <option value="ATM Card">ATM Card</option>
    <option value="Bank Transfer">Bank Transfer</option>
</select>

<!-- Dummy Payment Forms -->
<div id="payment-forms">
    <!-- Credit Card -->
    <div class="payment-form" id="form-credit">
        <h4>Credit Card Details</h4>
        <input type="text" placeholder="Card Number" maxlength="19" oninput="formatCard(this)">
        <input type="text" placeholder="Expiry (MM/YY)" maxlength="5" oninput="formatExpiry(this)">
        <input type="text" placeholder="CVV" maxlength="4">
    </div>

    <!-- ATM Card -->
    <div class="payment-form" id="form-atm">
        <h4>ATM Card Details</h4>
        <input type="text" placeholder="Card Number" maxlength="16">
        <input type="password" placeholder="PIN (for verification only)" maxlength="4">
        <small>🔒 Securely processed. PIN not stored.</small>
    </div>

    <!-- Bank Transfer -->
    <div class="payment-form" id="form-bank">
        <h4>Bank Transfer Instructions</h4>
        <p>After booking, you’ll receive:</p>
        <ul>
            <li>Bank name: <strong>LuxBank Philippines</strong></li>
            <li>Account #: <strong>1029384756</strong></li>
            <li>Account Name: <strong>Aura Luxe Hotel Inc.</strong></li>
        </ul>
        <p>Please complete payment within <strong>24 hours</strong>.</p>
    </div>
        </div>

            <select name="room_category" required>
                <option value="">Room Category</option>
                <option value="deluxe">Deluxe</option>
                <option value="economy">Economy</option>
                <option value="regular">Regular</option>
                <option value="vip">VIP</option>
            </select>

            <input type="date" name="check_in_date" min="<?= date('Y-m-d') ?>" required>
            <button type="submit">Confirm Booking</button>
        </form>
    </main>
    <!-- Add before </body> in all PHP files -->
<script src="assets/js/main.js"></script>
</body>
</html>