<?php
// config/db.php

// Database credentials
$servername = "localhost";   // usually 'localhost'
$username = "root";          // your MySQL username
$password = "";              // your MySQL password
$dbname = "studyflow_db";    // database name we created

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// echo "Connected successfully"; // Uncomment for testing
?>
