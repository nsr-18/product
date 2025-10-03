<?php
// db.php
$servername = "localhost";
$username = "root"; // e.g., "root"
$password = ""; // e.g., "" for XAMPP/WAMP default
$dbname = "your_database_name";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>