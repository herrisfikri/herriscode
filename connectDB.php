<?php
/* Database connection settings */
	$servername = "localhost";
    $username = "root";		//put your phpmyadmin username.(default is "root")
    $password = "";			//if your phpmyadmin has a password put it here.(default is "root")
    $dbname = "rfidattendance";
	$conn = mysqli_connect($servername, $username, $password, $dbname);
	$con = mysqli_connect("localhost","root","","rfidattendance") or die("sorry! Unable to connect to DB");

	if ($conn->connect_error) {
        die("Database Connection failed: " . $conn->connect_error);
    }
?>