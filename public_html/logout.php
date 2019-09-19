<?php
// Initialize the session.
include '../app/init.php';
//close the db session
$now = date('Y-m-d H:i:s');
$time_online = strtotime($now) - strtotime($_SESSION['login_time']); // now() - login time
$stmt = $pdo->prepare('UPDATE sessions SET logout_time = :time, duration_seconds = :time_online WHERE user_id = :user_id AND login_time = :login_time');
$stmt->bindParam(':time', $now);
$stmt->bindParam(':time_online', $time_online);
$stmt->bindParam(':user_id', $_SESSION['id']);
$stmt->bindParam(':login_time', $_SESSION['login_time']);
if($stmt->execute()) {
  // Unset all of the session variables.
  $_SESSION = array();
  // If it's desired to kill the session, also delete the session cookie.
  // Note: This will destroy the session, and not just the session data!
  if (ini_get("session.use_cookies")) {
      $params = session_get_cookie_params();
      setcookie(session_name(), '', time() - 42000,
          $params["path"], $params["domain"],
          $params["secure"], $params["httponly"]
      );
  }
  // Finally, destroy the session and redirect.
  if(session_destroy()) {
    header('Location: index.php');
    exit();
  } 
}
session_destroy();
header('Location: index.php');
exit();
?>