<?php
$servername = "localhost";
$userdbname = "memebeam";
$passdbword = "";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=memeclub_app", $userdbname, $passdbword);
    // set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
catch(PDOException $e)
    {
    echo "Connection failed: " . $e->getMessage();
    }
?>