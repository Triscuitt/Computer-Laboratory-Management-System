<?php


$servername = "localhost";
$username = "root";
$password = "#Diverson3008";
$dbname = "comlabsystem";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
