<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rests";
// Create connection
$conn = new PDO('mysql:host=localhost;dbname=rests', $username, $password);
$conn->query("SET NAMES 'utf8'");
date_default_timezone_set("Asia/Jerusalem");
