<?php
// set timezone
date_default_timezone_set('Africa/Johannesburg');
// start the session
session_start();
// set the app URL
$app_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}";
// generate csrf token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(50));
}
$csrf_token = $_SESSION['csrf_token'];
// include funtions
include 'functions.php';
// include database
include 'database.php';