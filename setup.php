<?php
try {
    $db = new PDO("sqlite:/tmp/database.db");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = file_get_contents(__DIR__ . "/init.sql");
    $db->exec($sql);

} catch (PDOException $e) {
    echo "Setup Error: " . $e->getMessage();
}
?>
