<?php include '../app/init.php'; 
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
    if(empty($_POST['password'])){
      $p_error = "Please enter a password.";
      $error = 1;
    } else {
      $password = clean($_POST['password']);
    }
    if(empty($error)) {
      // check if the user exists
      $stmt = $pdo->prepare('SELECT id, username, email, password_hash, role FROM users WHERE activated = 1 AND (username = :username OR email = :email)');
      $stmt->bindParam(':username', $login);
      $stmt->bindParam(':email', $login);
      $stmt->execute();
      $user = $stmt->fetch();
      if($user && password_verify($password, $user['password_hash'])) {
        //user can log in.
        $_SESSION['id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['login_time'] = date('Y-m-d H:i:s');
        //If there is an open session, find the average session time and close the abandoned session with the average length for the user.
        $stmt = $pdo->prepare('SELECT id, user_id, login_time FROM sessions WHERE logout_time IS NULL AND user_id = :user_id');
        $stmt->bindParam(':user_id', $user['id']);
        $stmt->execute();
        $abandoned_sessions = $stmt->fetchAll();
        if($abandoned_sessions) {
          $stmt = $pdo->prepare('SELECT AVG(duration_seconds) as avg_seconds FROM sessions WHERE user_id = :user_id');
          $stmt->bindParam(':user_id', $user['id']);
          $stmt->execute();
          $user_avg_session_time = $stmt->fetch();
          // close the sessions
          foreach($abandoned_sessions as $abandoned_session) {
            $logout_time = date("Y-m-d H:i:s", (strtotime(date($abandoned_session['login_time'])) + $user_avg_session_time['avg_seconds']));
            $duration = strtotime($logout_time) - strtotime($abandoned_session['login_time']);
            $stmt = $pdo->prepare('UPDATE sessions SET logout_time = :logout_time, duration_seconds = :duration WHERE user_id = :user_id AND id = :session_id');
            $stmt->bindParam(':user_id', $user['id']);
            $stmt->bindParam(':session_id', $abandoned_session['id']);
            $stmt->bindParam(':logout_time', $logout_time);
            $stmt->bindParam(':duration', $duration);
            $stmt->execute();
          }
        }
        //create the session to track online activity of a user.
        $ip_addr = $_SERVER['REMOTE_ADDR'];
        $stmt = $pdo->prepare('INSERT INTO sessions (user_id, login_time, ip_address) VALUES (:id, :login_time, :ip_address)');
        $stmt->bindParam(':id', $user['id']);
        $stmt->bindParam(':login_time', $_SESSION['login_time']);
        $stmt->bindParam(':ip_address', $ip_addr);
        if($stmt->execute()) {
          redirect('timeline.php');
        }
      } else {
        $l_error = "The username/email or password is incorrect OR Your account is not activated yet.";
      }
    }
  }
}
?>
<?php include 'header.php'; ?>
<?php include 'navbar.php'; ?>
<div class="container mt-5">
  <div class="row">
    <div class="col-sm">
      <p>You affirm that you are at least 18 years of age or the age of majority in the jurisdiction you are accessing this website from. If you are under 18 or the applicable age of majority, you are not permitted to submit personal information to us or use this website. You also represent that the jurisdiction from which you access this website does not prohibit the receiving or viewing of sexually explicit content.</p>
      <p>If you need help, e-mail us: <a href="mailto:info@memeclub.xyz">info@memeclub.xyz</a></p>
      <form action="" method="post" class="mb-2 mt-3">
        <div class="form-group">
          <label for="login">Username or E-mail Address</label>
          <input type="text" class="form-control" id="login" placeholder="Enter email or Username" name="login" required>
          <?php if(!empty($l_error)): ?><small class="form-text text-danger"><?= $l_error; ?></small> <?php endif; ?>
        </div>
        <div class="form-group">
          <label for="password">Password (<span class="small"><a href="forgot_password.php">Forgot Password</a></span>)</label>
          <input type="password" class="form-control" id="password" placeholder="Your Password" name="password" required>
          <?php if(!empty($p_error)): ?><small class="form-text text-danger"><?= $p_error; ?></small> <?php endif; ?>
        </div>
        <input type="hidden" name="token" value="<?= $csrf_token;?>" />
        <button type="submit" class="btn btn-primary" name="sign_in">Sign In</button>
      </form>
      <span><a href="register.php">Sign up for a memeclub account (No under 18 year olds)</a></span>
    </div>
  </div>
  <div class="row mt-5">
    <div class="col-sm">
      <h5>Terms &amp; Conditions</h5>
      <p>If you choose, or are provided with, a user name, password or any other piece of information as part of our security procedures, you must treat such information as confidential and, you must not disclose it to any other person or entity and you are fully responsible for all activities that occur under your user name or password. You also acknowledge that your account is personal to you and agree not to provide any other person with access to this website or portions of it using your user name, password or other security information.</p>
      <p>You shall be solely responsible for your own content and the consequences of posting or publishing such content. You understand that we do not guarantee any confidentiality with respect to any content you contribute. We are not responsible for the Content on the website.</p>
      <p>We have the right to disable any user name, password or other identifier, whether chosen by you or provided by us, at any time in our sole discretion for any or no reason, including if, in our opinion, you have violated any provision of these Terms of Service.</p>
    </div>
  </div>
</div>
<?php include 'footer.php'; ?>