<?php
include 'includes/db.php';

// Get selected month/year (default: current)
$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? date('m');
$daysInMonth = date('t', mktime(0, 0, 0, $month, 1, $year));

// Fetch all bookings for this month
$stmt = $db->prepare("
    SELECT room_category, room_number, check_in_date 
    FROM reservations 
    WHERE full_name != 'AVAILABLE' 
      AND strftime('%Y-%m', check_in_date) = ?
");
$stmt->execute(["$year-$month"]);
$bookings = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

// Room categories and total rooms per category (5 each)
$categories = ['deluxe', 'economy', 'regular', 'vip'];
$totalRoomsPerCategory = 5;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Availability Calendar</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="calendar-container">
        <div class="calendar-controls">
            <a href="?year=<?= $year ?>&month=<?= str_pad($month - 1, 2, '0', STR_PAD_LEFT) ?>" 
               <?= $month == 1 ? 'style="visibility:hidden"' : '' ?>>&laquo; Prev</a>
            <h2><?= date('F Y', mktime(0, 0, 0, $month, 1, $year)) ?></h2>
            <a href="?year=<?= $year ?>&month=<?= str_pad($month + 1, 2, '0', STR_PAD_LEFT) ?>"
               <?= $month == 12 ? 'style="visibility:hidden"' : '' ?>>Next &raquo;</a>
        </div>

        <div class="calendar-grid">
            <?php
            // Weekday headers
            $weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            foreach ($weekdays as $wd) echo "<div class='weekday'>$wd</div>";

            // Empty cells before 1st day
            $firstDay = date('w', mktime(0, 0, 0, $month, 1, $year));
            for ($i = 0; $i < $firstDay; $i++) {
                echo "<div class='empty'></div>";
            }

            // Days of the month
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $dateStr = "$year-$month-" . str_pad($day, 2, '0', STR_PAD_LEFT);
                $dayBookings = [];

                // Count booked rooms per category on this date
                if (isset($bookings[$dateStr])) {
                    foreach ($bookings[$dateStr] as $b) {
                        $dayBookings[$b['room_category']] = ($dayBookings[$b['room_category']] ?? 0) + 1;
                    }
                }

                echo "<div class='calendar-day'>";
                echo "<strong>$day</strong>";

                // Show availability per category
                foreach ($categories as $cat) {
                    $booked = $dayBookings[$cat] ?? 0;
                    $available = $totalRoomsPerCategory - $booked;
                    $color = $available == 0 ? 'booked' : ($available <= 2 ? 'limited' : 'available');
                    echo "<div class='room-status $color' title=''>$available $cat</div>";
                }
                echo "</div>";
            }
            ?>
        </div>

        <div class="legend">
            <span class="available">● Available (3–5)</span>
            <span class="limited">● Limited (1–2)</span>
            <span class="booked">● Fully Booked</span>
        </div>
    </main>
    <!-- Add before </body> in all PHP files -->
<script src="assets/js/main.js"></script>
</body>
</html>