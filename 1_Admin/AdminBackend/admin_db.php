<?php

function connect() {
    $host = 'localhost';
    $db_name = 'purrsafe_db'; 
    $username = 'root';
    $password = '';

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