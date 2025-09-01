<?php
// config.php - Koneksi database
$host = '192.168.1.10'; // Masukan localhost bila tidak setting ip server mysql/mariadb
$username = 'root'; // username database
$password = '';  // password db
$database = 'sik'; //nama database, khanza standarnya sik

// Koneksi
$conn = mysqli_connect($host, $username, $password, $database);
