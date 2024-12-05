<?php
include 'admin_db.php';

$conn = connect();
if ($conn) {
    echo "Connected successfully to the database.";
    closeConnection($conn);
} else {
    echo "Connection failed.";
}
?>