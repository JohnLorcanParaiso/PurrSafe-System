<?php

function connect() {
    // Database connection parameters
    $host = 'localhost';
    $db_name = 'purrsafe_db'; // Name of your database
    $username = 'root';
    $password = '';

    // Create a MySQL connection
    $conn = new mysqli($host, $username, $password, $db_name);

    // Check for connection errors
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

function closeConnection($conn) {
    $conn->close();
}
?>