<?php
// make sure a user exist
if(empty($_SESSION['id'])) {
    redirect('index.php');
}