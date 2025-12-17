<?php
function getConnection()
{
    $servername = "localhost";
    $username = "root";
    $password = "#Diverson3008";
    $database = "comlab_management_system";

    $conn = new mysqli($servername, $username, $password, $database);

    if ($conn->connect_error) {
        die("Connection Failed: " . $conn->connect_error);
    }
    return $conn;
}
