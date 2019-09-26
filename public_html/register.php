<?php include '../app/init.php'; 
//register user
if($_SERVER['REQUEST_METHOD'] == "POST") {
    if (hash_equals($_SESSION['csrf_token'], $_POST['token'])) {
        //validate input
        if(empty($_POST['username'])) {
          $u_error = "Username cannot be empty.";
          $error = 1;
        } elseif(!preg_match('/^[a-zA-Z0-9]{5,}$/', $_POST['username'])) { 
          $u_error = "Username must be alphanumeric & longer than or equals 5 characters";
          $error = 1;
        } else {
          $username = clean($_POST['username']);
          // check if the username is taken
          $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :username');
          $stmt->bindParam(':username', $username, PDO::PARAM_STR);
          $stmt->execute();
          $username_exist = $stmt->fetchAll();
          if($username_exist) {
            $u_error = "Username is already taken.";
            $error = 1;
          }
        }
        if(empty($_POST['email'])) {
          $e_error = "E-mail cannnot be empty.";
          $error = 1;
        } elseif(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
          $e_error = "E-mail is not valid.";
          $error = 1;
        } else {
          $email = clean($_POST['email']);
          // check if the email exists
          $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
          $stmt->bindParam(':email', $email, PDO::PARAM_STR);
          $stmt->execute();
          $email_exist = $stmt->fetchAll();
          if($email_exist) {
            $e_error = "E-mail already exists.";
            $error = 1;
          }
        }
        if(empty($_POST['password']) OR empty($_POST['c_password'])) {
          $p_error = "Fill in a password.";
          $error = 1;
        }
        if($_POST['password'] !== $_POST['c_password']) {
          $p_error = "Password did not match! Try again.";
          $error = 1;
        } elseif(!preg_match('/^(?=.*\d)(?=.*[@#\-_$%^&+=§!\?])(?=.*[a-z])(?=.*[A-Z])[0-9A-Za-z@#\-_$%^&+=§!\?]{8,20}$/',$_POST['password'])) {
          $p_error = "Your password must have at least <strong>one lowercase</strong> letter,<br> at least <strong>one uppercase letter</strong>,<br> at least <strong>one number</strong><br> and at least one special sign of <strong>@#-_$%^&+=§!? </strong><br>The complexity is for your own safety.";
          $error = 1;
        } else {
          $password = clean($_POST['password']);
          $password_hash =  password_hash($password, PASSWORD_DEFAULT);
        }
        if(!empty($_POST['referrer_id'])) {
           $referrer_id = clean($_POST['referrer_id']);
        } else {
           $referrer_id = null;
        }
        if(empty($error)) {
          User::register($username, $email, $password_hash, $referrer_id);
          //  register_user($username, $email, $password_hash, $referrer_id);
        }
    } else {
      redirect('logout.php');
    }
}
?>
<?php include 'header.php'; ?>
<?php include 'navbar.php'; ?>
<div class="container mt-5">
  <div class="row">
    <div class="col-sm">
      <p>When creating an account on Memeclub by registering you agree that you are an individual (i.e., not a corporate entity) and are at least 18 years of age or the age of majority in the jurisdiction you are accessing this website from. No one under the age of 18 may register or provide any personal information to or on Memeclub (including, for example, a name, address, telephone number or email address), we thank you for understanding. You also represent that the jurisdiction from which you access this Website does not prohibit the receiving or viewing of sexually explicit content.</p>
      <form action="" method="post" class="mb-3 mt-3">
        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" class="form-control <?php if(isset($u_error)) {echo "is-invalid";} ?>" id="username" placeholder="Enter username" name="username" value="<?php if(!empty($username)){echo $username;}?>">
          <?php if(isset($u_error)) {echo '<small class="form-text text-danger">'.$u_error.'</small>';} ?>
        </div>
        <div class="form-group">
          <label for="email">Email address</label>
          <input type="email" class="form-control <?php if(isset($e_error)) {echo "is-invalid";} ?>" id="email" aria-describedby="emailHelp" placeholder="Enter email" name="email" value="<?php if(isset($email)){echo $email;}?>">
          <?php if(isset($e_error)) {echo '<small class="form-text text-danger">'.$e_error.'</small>';} else {
          echo '<small id="emailHelp" class="form-text text-muted">We\'ll never share your email with anyone else.</small>'; }?>
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" class="form-control <?php if(isset($p_error)) {echo "is-invalid";} ?>" id="password" placeholder="Password" name="password">
        </div>
        <div class="form-group">
          <label for="c_password">Confirm Password</label>
          <input type="password" class="form-control <?php if(isset($p_error)) {echo "is-invalid";} ?>" id="c_password" placeholder="Confirm Password" name="c_password">
          <?php if(isset($p_error)) {echo '<small class="form-text text-danger">'.$p_error.'</small>';} ?>
        </div>
        <input type="hidden" name="token" value="<?= $csrf_token;?>" />
        <input type="hidden" name="referrer_id" value="<?php if(!empty($_SESSION['referrer_id'])){ echo $_SESSION['referrer_id'];}?>" />
        <button type="submit" class="btn btn-primary">Register</button>
        <p class="mt-1">By registring you acknowledge to be <span class="text-primary">older than 18 years</span> of age.</p>
      </form>
    </div>
  </div>
</div>
<?php include 'footer.php'; 
// clear the referrer_id session
//unset($_SESSION['referrer_id']);
?>