<?php
$host = 'host'; // ex. 'localhost'
$username = 'username'; // ex. 'root'
$password = 'password'; // ex. '12345678'
$database = 'ta_perbankan'; // ex. 'ta_perbankan'

try {
    $conn = mysqli_connect($host, $username, $password, $database);
    if (!$conn) {
        throw new Exception("Connection failed: " . mysqli_connect_error());
    }
} catch (Exception $e) {
    echo "<b>Error:</b> " . $e->getMessage();
}