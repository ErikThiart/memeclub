<?php include '../app/init.php'; ?>
<?php include 'header.php'; ?>
<?php include 'navbar.php'; ?>
<?php
// make sure a user exist
if(!empty($_SESSION['id'])) {
    redirect('timeline.php');
}
?>
<?php
if($_SERVER['REQUEST_METHOD'] == "POST") {
  if (hash_equals($_SESSION['csrf_token'], $_POST['token'])) {
    if(empty($_POST['login'])) {
      $l_error = "Please enter a username or email address.";
      $error = 1;
    } else {
      $login = clean($_POST['login']);
    }
    if(empty($error)) {
      // check if the user exists
      $stmt = $pdo->prepare('SELECT id, username, email FROM users WHERE username = :username OR email = :email');
      $stmt->bindParam(':username', $login);
      $stmt->bindParam(':email', $login);
      $stmt->execute();
      $user = $stmt->fetch();
      if($user) {
        //create a reset token (override old tokens)
        $reset_token = bin2hex(random_bytes(32));
        // set the token
        $stmt = $pdo->prepare('UPDATE users SET password_reset_token = :reset_token WHERE id = :user_id');
        $stmt->bindParam(':user_id', $user['id']);
        $stmt->bindParam(':reset_token', $reset_token);
        if($stmt->execute()) {
          //fire off the reset email
          $ip_addr = $_SERVER['REMOTE_ADDR'];
          $url = $app_url."/reset_password.php?code=".$reset_token."&email=".$user['email'];
          $message = '
          <h1>Reset Your Password</h1>
          <p>Someone requested your password to be reset (IP Address: '.$ip_addr.' at '.date('Y-m-d H:i:s').')</p>
          <p>If that was you, please click on the link below:</p>
          <p><a href="'.$url.'">Reset Password</a></p>
          <p>'.$url.'</p>
          ';
          $subject = "Confirm your registration.";
          if(sendmail($user['email'],$subject,$message,$user['username'])) {
            $_SESSION['heading'] = "Reset Pending";
            $_SESSION['message'] = "If the user exist then an E-mail have been sent with instructions on how to reset the password.";
            redirect('success.php'); 
          }
        } else {
          $_SESSION['message'] = "Reset Failed";
          $_SESSION['heading'] = "Something went wrong.";
          redirect('error.php');
        }
      } else {
          $_SESSION['heading'] = "Reset Pending";
          $_SESSION['message'] = "If the user exist then an E-mail have been sent with instructions on how to reset the password.";
          redirect('success.php'); 
      }
    }
  }
}
?>
<div class="container mt-5">
  <div class="row">
    <div class="col-sm">
      <form action="" method="post" class="mb-2">
        <div class="form-group">
          <label for="login">Username or E-mail Address</label>
          <input type="text" class="form-control" id="login" placeholder="Enter email or Username" name="login" required>
          <?php if(!empty($l_error)): ?><small class="form-text text-danger"><?= $l_error; ?></small> <?php endif; ?>
        </div>
        <input type="hidden" name="token" value="<?= $csrf_token;?>" />
        <button type="submit" class="btn btn-primary" name="sign_in">Reset Password</button>
      </form>
      <span><a href="register.php">Sign up for a new memeclub account</a></span>
    </div>
  </div>
</div>
<?php include 'footer.php'; ?>
