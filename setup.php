<?php
try {
	$db = new PDO("sqlite:/tmp/database.db");
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$sql = file_get_contents("init.sql");
    	$db->exec($sql);
    echo "Database initialized successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
