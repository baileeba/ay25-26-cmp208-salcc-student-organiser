<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "signup_db";

// Creating an SQL connection_aborted

$conn = new mysqli($servername, $username, $password,
$dbname);

// Testing Connection
if($conn->connect_error) {
	die("Connection Failed: " . $conn->connect_error);
}

echo "$dbname connected successfully!";

?>