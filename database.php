<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "nilai_tepat";

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

?> 
