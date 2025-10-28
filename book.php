<?php
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check_availability'])) {
    $checkIn = $_POST['check_in_date'];
    $checkOut = $_POST['check_out_date'];
    $category = $_POST['room_category'];

    // Get all rooms in category
    $stmt = $db->prepare("SELECT id, room_number FROM rooms WHERE category = ?");
    $stmt->execute([$category]);
    $allRooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Find rooms that are NOT booked for ANY day in the range
    $availableRoomIds = [];
    foreach ($allRooms as $room) {
        $stmt = $db->prepare("
            SELECT 1 FROM bookings 
            WHERE room_id = ? 
            AND check_in_date < ? AND check_out_date > ?
        ");
        $stmt->execute([$room['id'], $checkOut, $checkIn]);
        if (!$stmt->fetch()) {
            $availableRoomIds[] = $room['id'];
        }
    }

    // Get full room details for available rooms
    if (!empty($availableRoomIds)) {
        $placeholders = str_repeat('?,', count($availableRoomIds) - 1) . '?';
        $stmt = $db->prepare("SELECT id, room_number, category FROM rooms WHERE id IN ($placeholders) ORDER BY room_number");
        $stmt->execute($availableRoomIds);
        $availableRooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $availableRooms = [];
    }

    $selectedCategory = $category;
    $selectedCheckIn = $checkIn;
    $selectedCheckOut = $checkOut;
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['room_id'])) {
    // Process the booking
    $name = trim($_POST['full_name']);
    $address = trim($_POST['address']);
    $id_type = $_POST['id_type'];
    $payment = $_POST['payment_method'];
    $room_id = $_POST['room_id'];
    $checkIn = $_POST['check_in_date'];
    $checkOut = $_POST['check_out_date'];

    // Verify the selected room is available for the entire range
    $stmt = $db->prepare("
        SELECT 1 FROM bookings 
        WHERE room_id = ? 
        AND check_in_date < ? AND check_out_date > ?
    ");
    $stmt->execute([$room_id, $checkOut, $checkIn]);
    if (!$stmt->fetch()) {
        $db->prepare("
            INSERT INTO bookings (full_name, address, id_type, payment_method, room_id, check_in_date, check_out_date)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ")->execute([$name, $address, $id_type, $payment, $room_id, $checkIn, $checkOut]);
        $success = "Booking confirmed! Room for $name from $checkIn to $checkOut.";
    } else {
        $error = "Selected room is not available for the entire date range.";
    }
}
?>

<?php $pageTitle = "Book a Room"; include 'includes/header.php'; ?>

<div class="form-container">
    <h2>Book Your Stay</h2>
    <?php if (!empty($success)): ?>
        <div class="alert success"><?= htmlspecialchars($success) ?></div>
    <?php elseif (!empty($error)): ?>
        <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" id="availability-form">
        <input type="hidden" name="check_availability" value="1">
        
        <div class="form-row full-width">
            <select name="room_category" id="room-category" required>
                <option value="">Room Category</option>
                <option value="regular" <?= (isset($selectedCategory) && $selectedCategory === 'regular') ? 'selected' : '' ?>>Regular</option>
                <option value="economy" <?= (isset($selectedCategory) && $selectedCategory === 'economy') ? 'selected' : '' ?>>Economy</option>
                <option value="deluxe" <?= (isset($selectedCategory) && $selectedCategory === 'deluxe') ? 'selected' : '' ?>>Deluxe</option>
                <option value="vip" <?= (isset($selectedCategory) && $selectedCategory === 'vip') ? 'selected' : '' ?>>VIP</option>
            </select>
        </div>

        <div class="form-row">
            <input type="date" name="check_in_date" id="check-in-date" min="<?= date('Y-m-d') ?>" 
                   value="<?= $selectedCheckIn ?? '' ?>" required>
            <input type="date" name="check_out_date" id="check-out-date" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" 
                   value="<?= $selectedCheckOut ?? '' ?>" required>
        </div>

        <button type="submit" id="check-availability-btn">Check Availability</button>
    </form>

    <?php if (isset($availableRooms) && !empty($availableRooms)): ?>
        <div id="booking-step-2">
            <h3>Available Rooms for <?= ucfirst($selectedCategory) ?> (<?= $selectedCheckIn ?> to <?= $selectedCheckOut ?>)</h3>
            
            <form method="POST" id="booking-form">
                <!-- Hidden fields to preserve availability selection -->
                <input type="hidden" name="check_in_date" value="<?= $selectedCheckIn ?>">
                <input type="hidden" name="check_out_date" value="<?= $selectedCheckOut ?>">
                
                <!-- Room Selection: Full Width -->
                <div class="form-row full-width">
                    <select name="room_id" id="room-number" required>
                        <option value="">Select a room</option>
                        <?php foreach ($availableRooms as $room): ?>
                            <option value="<?= $room['id'] ?>">#<?= $room['room_number'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Personal Details: Only appear after room selection -->
                <div id="details-section" style="display:none;">
                    <div class="form-row">
                        <input type="text" name="full_name" placeholder="Full Name" required>
                        <textarea name="address" placeholder="Address" required></textarea>
                    </div>

                    <div class="form-row">
                        <select name="id_type" required>
                            <option value="">Select ID Type</option>
                            <option value="National ID">National ID</option>
                            <option value="SSS">SSS</option>
                            <option value="PAGIBIG">PAGIBIG</option>
                            <option value="Philhealth">Philhealth</option>
                        </select>

                        <select name="payment_method" id="payment-method" required>
                            <option value="">Payment Method</option>
                            <option value="Credit Card">Credit Card</option>
                            <option value="ATM Card">ATM Card</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                        </select>
                    </div>

                    <!-- Dummy Payment Forms -->
                    <div id="payment-forms">
                        <div class="payment-form" id="form-credit" style="display:none;">
                            <h4>Credit Card Details</h4>
                            <input type="text" placeholder="Card Number" maxlength="19" oninput="formatCard(this)">
                            <input type="text" placeholder="Expiry (MM/YY)" maxlength="5" oninput="formatExpiry(this)">
                            <input type="text" placeholder="CVV" maxlength="4">
                        </div>

                        <div class="payment-form" id="form-atm" style="display:none;">
                            <h4>ATM Card Details</h4>
                            <input type="text" placeholder="Card Number" maxlength="16">
                            <input type="password" placeholder="PIN (for verification only)" maxlength="4">
                            <small>🔒 Securely processed. PIN not stored.</small>
                        </div>

                        <div class="payment-form" id="form-bank" style="display:none;">
                            <h4>Bank Transfer Instructions</h4>
                            <p>After booking, you’ll receive:</p>
                            <ul>
                                <li>Bank name: <strong>LuxBank Philippines</strong></li>
                                <li>Account #: <strong>1029384756</strong></li>
                                <li>Account Name: <strong>LuxStay Hotel Inc.</strong></li>
                            </ul>
                            <p>Please complete payment within <strong>24 hours</strong>.</p>
                        </div>
                    </div>

                    <button type="submit">Confirm Booking</button>
                </div>
            </form>
        </div>
    <?php elseif (isset($selectedCategory) && isset($selectedCheckIn)): ?>
        <p>No available rooms for <?= ucfirst($selectedCategory) ?> from <?= $selectedCheckIn ?> to <?= $selectedCheckOut ?>.</p>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const checkInInput = document.getElementById('check-in-date');
    const checkOutInput = document.getElementById('check-out-date');
    const roomSelect = document.getElementById('room-number');
    const detailsSection = document.getElementById('details-section');
    const paymentSelect = document.getElementById('payment-method');
    const forms = {
        'Credit Card': 'form-credit',
        'ATM Card': 'form-atm',
        'Bank Transfer': 'form-bank'
    };

    // Set min check-out date to day after check-in
    checkInInput.addEventListener('change', function() {
        if (this.value) {
            checkOutInput.min = new Date(new Date(this.value).getTime() + 24*60*60*1000).toISOString().split('T')[0];
        }
    });

    // Show details section when a room is selected
    if (roomSelect) {
        roomSelect.addEventListener('change', function() {
            if (this.value) {
                detailsSection.style.display = 'block';
            } else {
                detailsSection.style.display = 'none';
            }
        });
    }

    // Payment forms
    if (paymentSelect) {
        paymentSelect.addEventListener('change', () => {
            Object.values(forms).forEach(id => {
                const el = document.getElementById(id);
                if (el) el.style.display = 'none';
            });
            const formId = forms[paymentSelect.value];
            if (formId) {
                document.getElementById(formId).style.display = 'block';
            }
        });
    }

    // Card formatting
    window.formatCard = function(input) {
        let value = input.value.replace(/\D/g, '');
        let formatted = '';
        for (let i = 0; i < value.length && i < 16; i++) {
            if (i > 0 && i % 4 === 0) formatted += ' ';
            formatted += value[i];
        }
        input.value = formatted;
    };
    
    window.formatExpiry = function(input) {
        let value = input.value.replace(/\D/g, '');
        if (value.length >= 3) {
            value = value.substring(0, 2) + '/' + value.substring(2, 4);
        }
        input.value = value;
    };
});
</script>

<?php include 'includes/footer.php'; ?>
