<?php
$dbPath = "/tmp/database.db";

$firstTime = !file_exists($dbPath);

$conn = new PDO("sqlite:" . $dbPath);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($firstTime) {
    require_once "setup.php";
}
?>
