<?php
try {
    $dbPath = "/tmp/database.db";

    $conn = new PDO("sqlite:" . $dbPath);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // สร้าง table rooms ถ้ายังไม่มี
    $conn->exec("
        CREATE TABLE IF NOT EXISTS rooms (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            room_number TEXT NOT NULL,
            building TEXT NOT NULL,
            status TEXT DEFAULT 'available'
        )
    ");

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
