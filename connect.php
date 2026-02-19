<?php
$db_file = __DIR__ . "/database.db";

$conn = new PDO("sqlite:" . $db_file);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>
