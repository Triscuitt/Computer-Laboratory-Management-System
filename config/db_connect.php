<?php


$servername = "localhost";
$username = "root";
$password = "#Diverson3008";
$dbname = "dyci_lab_db";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
