<?php
session_start();

// testing
$_SESSION['user_id'] = 1;
$_SESSION['nama_role'] = 'Admin';

echo "User ID: " . $_SESSION['user_id'] . "<br>";
echo "Nama Role: " . $_SESSION['nama_role'] . "<br>";
echo "Session ID: " . session_id() . "<br>";