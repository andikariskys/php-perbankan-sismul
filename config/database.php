<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'ta_perbankan';

try {
    $conn = mysqli_connect($host, $username, $password, $database);
    if (!$conn) {
        throw new Exception("Connection failed: " . mysqli_connect_error());
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}