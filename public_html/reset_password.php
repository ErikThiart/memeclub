<?php 
include '../app/init.php';
if($_SERVER['REQUEST_METHOD'] == "GET") {
  $reset_token = clean($_GET['code']);
  $email = clean($_GET['email']);
  // if empty stop.
  if(empty($reset_token) OR empty($email)) {
    redirect('logout.php');
  }
  // check if the user exist
  $stmt = $pdo->prepare('SELECT id, activation_token, password_reset_token FROM users WHERE password_reset_token = :reset_token AND email = :email');
  $stmt->bindParam(':reset_token', $reset_token);
  $stmt->bindParam(':email', $email);
  $stmt->execute();
  $user = $stmt->fetch();
  if($user) {
    $acc_token = $user['activation_token'];
    $user_id = $user['id'];
  } else {
    $_SESSION['message'] = "Invalid Token";
    $_SESSION['heading'] = "Please contact support.";
    redirect('error.php');
  }
}
if($_SERVER['REQUEST_METHOD'] == "POST") {
   if(hash_equals($_SESSION['csrf_token'], $_POST['token'])) {
     $reset_token = clean($_POST['acc_token']);
     $user_id = clean($_POST['acc']);
     $email = clean($_POST['email']);
     $reset_token = clean($_POST['reset_token']);
     if(empty($reset_token) OR empty($user_id) OR empty($email) OR empty($reset_token)) {
       redirect('logout.php');
     }
     if(empty($_POST['password']) OR empty($_POST['c_password'])) {
       $p_error = "Fill in a password.";
       $error = 1;
     }
     if($_POST['password'] !== $_POST['c_password']) {
       $p_error = "Password did not match! Try again.";
       $error = 1;
     } elseif(!preg_match('/^(?=.*\d)(?=.*[@#\-_$%^&+=§!\?])(?=.*[a-z])(?=.*[A-Z])[0-9A-Za-z@#\-_$%^&+=§!\?]{8,20}$/',$_POST['password'])) {
       $p_error = "Your password must have at least <strong>one lowercase</strong> letter,<br> at least <strong>one uppercase letter</strong>,<br> at least <strong>one number</strong><br> and  at least one special sign of <strong>@#-_$%^&+=§!? </strong><br>The complexity is for your own safety.";
       $error = 1;
     } else {
       $password = clean($_POST['password']);
       $hashed_pass =  password_hash($password, PASSWORD_DEFAULT);
     }
     if(empty($error)) {
        //reset the password
        reset_password($hashed_pass, $user_id, $email, $reset_token);
     } else {
        //errors are found
        $_SESSION['message'] = "Password Reset Failed";
        $_SESSION['heading'] = $p_error;
        redirect('error.php');
     }
   // csrf end
   } else {
     redirect('logout.php');
   }
// post end  
}
?>
<?php include 'header.php'; ?>
<?php include 'navbar.php'; ?>
<div class="container mt-5">
  <div class="row">
    <div class="col-sm">
      <form action="" method="post" class="mb-2">
        <div class="form-group">
          <label for="password">New Password</label>
          <input type="password" class="form-control" id="password" placeholder="Your Password" name="password" required pattern="(?=.*\d)(?=.*[@#\-_$%^&+=§!\?])(?=.*[a-z])(?=.*[A-Z])[0-9A-Za-z@#\-_$%^&+=§!\?]{8,20}"   required title="Your password must have at least one lowercase letter, at least one uppercase letter, at least one number and  at least one special sign of @#-_$%^&+=§!? The complexity is for your own safety.">
        </div>
        <div class="form-group">
          <label for="c_password">Confirm New Password</label>
          <input type="password" class="form-control" id="c_password" placeholder="Your Password" name="c_password" required pattern="(?=.*\d)(?=.*[@#\-_$%^&+=§!\?])(?=.*[a-z])(?=.*[A-Z])[0-9A-Za-z@#\-_$%^&+=§!\?]{8,20}"   required title="Your password must have at least one lowercase letter, at least one uppercase letter, at least one number and  at least one special sign of @#-_$%^&+=§!? The complexity is for your own safety.">
          <?php if(!empty($p_error)): ?><small class="form-text text-danger"><?= $p_error; ?></small> <?php endif; ?>
        </div>
        <input type="hidden" name="token" value="<?= $csrf_token;?>" />
        <input type="hidden" name="acc_token" value="<?= $acc_token;?>" />
        <input type="hidden" name="reset_token" value="<?= $reset_token;?>" />
        <input type="hidden" name="acc" value="<?= $user_id ;?>" />
        <input type="hidden" name="email" value="<?= $email ;?>" />
        <button type="submit" class="btn btn-primary" name="sign_in">Reset</button>
      </form>
      <span><a href="register.php">Sign up for a memeclub account (it's free)</a></span>
    </div>
  </div>
</div>
<?php include 'footer.php'; ?>