<?php
// includes/db.php
$db_path = __DIR__ . '/../db/hotel.db';
$db = new PDO("sqlite:$db_path");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create rooms table
$db->exec("
    CREATE TABLE IF NOT EXISTS rooms (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        room_number INTEGER NOT NULL,
        category TEXT NOT NULL,
        price REAL NOT NULL,
        UNIQUE(category, room_number)
    )
");

// Create bookings table - NOW WITH check_out_date
$db->exec("
    CREATE TABLE IF NOT EXISTS bookings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        full_name TEXT NOT NULL,
        address TEXT NOT NULL,
        id_type TEXT NOT NULL,
        payment_method TEXT NOT NULL,
        room_id INTEGER NOT NULL,
        check_in_date TEXT NOT NULL,
        check_out_date TEXT NOT NULL,  -- NEW: End date
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (room_id) REFERENCES rooms(id)
    )
");

// Seed rooms (10 per category with new numbering scheme)
$roomCount = $db->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
if ($roomCount == 0) {
    $roomRanges = [
        'regular' => [100, 109],
        'economy' => [201, 210],
        'deluxe'  => [301, 310],
        'vip'     => [401, 410]
    ];
    $prices = ['regular' => 2000, 'economy' => 1500, 'deluxe' => 5000, 'vip' => 10000];
    
    foreach ($roomRanges as $cat => $range) {
        for ($num = $range[0]; $num <= $range[1]; $num++) {
            $db->prepare("INSERT INTO rooms (room_number, category, price) VALUES (?, ?, ?)")
               ->execute([$num, $cat, $prices[$cat]]);
        }
    }
}
?>
