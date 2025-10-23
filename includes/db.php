<?php
// includes/db.php
$db_path = __DIR__ . '/../db/hotel.db';
$db = new PDO("sqlite:$db_path");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create tables if not exists
$db->exec("
    CREATE TABLE IF NOT EXISTS reservations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        full_name TEXT NOT NULL,
        address TEXT NOT NULL,
        id_type TEXT NOT NULL,
        payment_method TEXT NOT NULL,
        room_category TEXT NOT NULL,
        room_number INTEGER NOT NULL,
        check_in_date TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )
");

// Pre-populate rooms (run once)
$rooms = $db->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
if ($rooms == 0) {
    // Sample rooms: 5 per category
    $categories = ['deluxe', 'economy', 'regular', 'vip'];
    foreach ($categories as $cat) {
        for ($i = 1; $i <= 5; $i++) {
            $db->exec("INSERT INTO reservations (full_name, address, id_type, payment_method, room_category, room_number, check_in_date) 
                       VALUES ('AVAILABLE', '', '', '', '$cat', $i, 'AVAILABLE')");
        }
    }
}
?>