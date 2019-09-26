<?php
include '../app/init.php';
if($_SERVER['REQUEST_METHOD'] === 'GET') {
$token = $_GET['activation_code'];
$email = $_GET['email'];
confirm_registraiton($token, $email);
} else {
  redirect('logout.php');
}

