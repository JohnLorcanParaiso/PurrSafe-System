<?php

$host = 'localhost';
$db_name = 'admin_purrsafe_db';
$username = 'root';
$password = '';


function connect() {
    global $host, $db_name, $username, $password;
    $conn = new mysqli($host, $username, $password, $db_name);


    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}


function closeConnection($conn) {
    $conn->close();
}
?>