<?php
include 'includes/db.php';

$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? date('m');
$daysInMonth = date('t', mktime(0, 0, 0, $month, 1, $year));
$categories = ['deluxe', 'economy', 'regular', 'vip'];

// Get all rooms and group them by category
$allRooms = [];
$roomsResult = $db->query("SELECT id, room_number, category FROM rooms ORDER BY category, room_number");
while ($row = $roomsResult->fetch(PDO::FETCH_ASSOC)) {
    $allRooms[$row['category']][] = $row;
}

// Get all bookings that span this month
$stmt = $db->prepare("
    SELECT b.check_in_date, b.check_out_date, r.room_number, r.category
    FROM bookings b
    JOIN rooms r ON r.id = b.room_id
    WHERE (strftime('%Y-%m', b.check_in_date) <= ? AND strftime('%Y-%m', b.check_out_date) >= ?)
       OR (strftime('%Y-%m', b.check_in_date) = ?)
       OR (strftime('%Y-%m', b.check_out_date) = ?)
");
$stmt->execute(["$year-$month", "$year-$month", "$year-$month", "$year-$month"]);
$bookedRanges = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Convert ranges to daily bookings for the calendar
$bookedRooms = [];
foreach ($bookedRanges as $range) {
    $currentDate = $range['check_in_date'];
    while ($currentDate < $range['check_out_date']) { // < not <= because check_out is exclusive
        $bookedRooms[$currentDate][] = $range;
        $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
    }
}
?>

<?php $pageTitle = "Room Availability"; include 'includes/header.php'; ?>

<div class="calendar-container">
    <div class="calendar-controls">
        <a href="?year=<?= $year ?>&month=<?= str_pad($month - 1, 2, '0', STR_PAD_LEFT) ?>" 
           <?= $month == 1 ? 'style="visibility:hidden"' : '' ?>>&laquo; Prev</a>
        <h2><?= date('F Y', mktime(0, 0, 0, $month, 1, $year)) ?></h2>
        <a href="?year=<?= $year ?>&month=<?= str_pad($month + 1, 2, '0', STR_PAD_LEFT) ?>"
           <?= $month == 12 ? 'style="visibility:hidden"' : '' ?>>Next &raquo;</a>
    </div>

    <div class="calendar-grid">
        <?php
        $weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        foreach ($weekdays as $wd) echo "<div class='weekday'>$wd</div>";

        $firstDay = date('w', mktime(0, 0, 0, $month, 1, $year));
        for ($i = 0; $i < $firstDay; $i++) echo "<div class='empty'></div>";

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateStr = "$year-$month-" . str_pad($day, 2, '0', STR_PAD_LEFT);
            echo "<div class='calendar-day'>";
            echo "<strong>$day</strong>";

            foreach ($categories as $cat) {
                $allNums = array_column($allRooms[$cat] ?? [], 'room_number');
                
                // Get booked rooms for THIS date AND THIS category
                $bookedNums = [];
                if (isset($bookedRooms[$dateStr])) {
                    foreach ($bookedRooms[$dateStr] as $b) {
                        if ($b['category'] === $cat) {
                            $bookedNums[] = $b['room_number'];
                        }
                    }
                }
                
                $availableNums = array_diff($allNums, $bookedNums);
                $availableCount = count($availableNums);
                $bookedCount = count($bookedNums);

                if ($bookedCount === 0) {
                    // No rooms booked - show available count
                    echo "<div class='room-status available'>$cat: all $availableCount available</div>";
                } elseif ($availableCount === 0) {
                    // All rooms booked - show "fully booked"
                    echo "<div class='room-status booked'>$cat: fully booked</div>";
                } else {
                    // Some rooms booked - show which ones are booked
                    $bookedList = implode(', #', $bookedNums);
                    echo "<div class='room-status limited'>$cat: #{$bookedList} booked</div>";
                }
            }
            echo "</div>";
        }
        ?>
    </div>

    <div class="legend">
        <span class="available">● All rooms available</span>
        <span class="limited">● Some rooms booked (shows which)</span>
        <span class="booked">● Fully booked</span>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
