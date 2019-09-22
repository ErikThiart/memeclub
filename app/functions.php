<?php
// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// Load Composer's autoloader
require '../vendor/autoload.php';

//TODO: Most of this stuff needs to be moved into a User class.

/**
 * Register a user
 *
 * @param [string] $username
 * @param [string] $email
 * @param [string] $password_hash
 * @param [int] $referrer_id
 * @return void
 */
function register_user($username, $email, $password_hash, $referrer_id) {
  // get the DB
  global $pdo;
  $app_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}";
  // create the token
  $token = bin2hex(random_bytes(3).$email.time().random_bytes(1));
  // create invite code
  $invite_code = substr(md5($email.microtime()),rand(0,26),10); //TODO: MD5 is completely insecure. use password_hash.
  // register the user
  try {
    $stmt = $pdo->prepare(
      'INSERT INTO users (username, email, password_hash, activation_token, invite_code) 
        VALUES (:username, :email, :password, :token, :invite_code)');
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $password_hash);
    $stmt->bindParam(':token', $token);
    $stmt->bindParam(':invite_code', $invite_code);
    $stmt->execute();
    $user_id = $pdo->lastInsertId();
    // send off a registraion email
    $message = '
      Please confirm your registration click on the link below: <br>
      '.$app_url.'/confirm_registration.php?activation_code='.$token.'&email='.$email.' <br>
    ';
    $subject = "Confirm your registration.";
    if (sendmail($email,$subject,$message,$username)) {
      //log the referral
      if (!empty($referrer_id)) {
        $stmt = $pdo->prepare('INSERT INTO referrals (referral_id, referrer_id) VALUES (:referral_id, :referrer_id)');
        $stmt->bindParam(':referral_id', $user_id);
        $stmt->bindParam(':referrer_id', $referrer_id);
        if($stmt->execute()) {
          //get the email of the referer
          $stmt = $pdo->prepare(
            'SELECT email, username 
            FROM users 
            WHERE id = :referrer_id');
          $stmt->bindParam(':referrer_id', $referrer_id);
          $stmt->execute();
          $referrer = $stmt->fetch();
          $message = '
          <h3>Congratulations, '.$referrer['username'].'</h3>
          <p>'.$username.' just signed up to Memeclub using your invite code!</p>
          <p>Invite more people and become the top memester.</p>
          ';
          $subject = 'Someone redeemed your invite code on MemeClub';
          sendmail($referrer['email'],$subject,$message,$referrer['username']);
        }
      }
      // Redirect the user to success page.
      $_SESSION['heading'] = "Registration Successful";
      $_SESSION['message'] = "Thank you for registering on our platform. We have send you a confirmation E-mail which you need to accept before your registration is completed.<br> Please check your spam folder if you use gmail.";
      redirect('success.php');
    } 
    
    $_SESSION['message'] = "Confirmation E-mail was not sent.";
    $_SESSION['heading'] = "Something went wrong.";
    redirect('error.php');

  } catch(PDOException $e) {
    $_SESSION['message'] = "Error: " . $e->getMessage();
    $_SESSION['heading'] = "Something went wrong.";
    redirect('error.php');     
  }
  //TODO: Both end of try and end of catch end in redirect. Move to here instead.
}

function confirm_registraiton($token, $email) {
  // get the DB
  global $pdo;
  //confirm user's registration
  $stmt = $pdo->prepare('SELECT id FROM users WHERE activation_token = :token AND email = :email AND activated = 0');
  $stmt->bindParam(':token', $token);
  $stmt->bindParam(':email', $email);
  $stmt->execute();
  $user = $stmt->fetch();
  if($user) {
    //make the user active and redirect to success page
    $stmt = $pdo->prepare('UPDATE users SET activated = 1 WHERE id = :user_id');
    $stmt->bindParam(':user_id', $user['id']);
    if($stmt->execute()){
      $_SESSION['heading'] = "Account Activated";
      $_SESSION['message'] = "Thank you, you can now log in.";
      redirect('success.php');    
    }
    $heading = "Something went wrong (Error 1)";
    $message = "Please contact support on info@memeclub.xyz and tell them the error code you received.";
    redirect('error.php');   
  } 
  // else { //else is not needed as if already redirects. Default behavior.
  $_SESSION['heading'] = "Invalid Token OR You have already been activated.";
  $_SESSION['message'] = "Please contact support on info@memeclub.xyz and we can investigate and activate you manually.";
  redirect('error.php');   
  // }
}

function reset_password($hashed_pass, $user_id, $email, $reset_token) {
   // get the DB
   global $pdo;
   $stmt = $pdo->prepare('UPDATE users SET password_hash = :password_hash, password_reset_token = NULL WHERE id = :user_id AND email = :email AND password_reset_token = :reset_token');
   $stmt->bindParam(':password_hash', $hashed_pass);
   $stmt->bindParam(':user_id', $user_id);
   $stmt->bindParam(':email', $email);
   $stmt->bindParam(':reset_token', $reset_token);
   if($stmt->execute()) {
      redirect('index.php');
   } else {
      $_SESSION['message'] = "Password Reset Failed";
      $_SESSION['heading'] = "Contact Support";
      redirect('error.php'); 
   }
}

/**
 * Undocumented function
 *
 * @param [type] $url
 * @param boolean $permanent
 * @return void
 * 
 * @SuppressWarnings(PHPMD.ExitExpression)
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
function redirect($url, $permanent = false) { //TODO: Move permanent to class definition. 
	if ($permanent) {
		header('HTTP/1.1 301 Moved Permanently');
	}
	header('Location: '.$url);
	exit();
}

function clean($data) { //TODO: Check where this goes, this is not something you should usually be doing. https://stackoverflow.com/questions/4223980/the-ultimate-clean-secure-function
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

function sendmail($to, $subject, $message, $name) {
  
  $mail = new PHPMailer(true);
  $body             = $message;
  $mail->IsSMTP();
  $mail->SMTPAuth   = true;
  $mail->Host       = "mail.mordor.co.za";
  $mail->Port       = 587;
  $mail->Username   = "app@mordor.co.za";
  $mail->Password   = "";
  $mail->SMTPSecure = 'tls';
  $mail->SetFrom('app@mordor.co.za', 'Memeclub');
  $mail->Subject    = $subject;
  $mail->AltBody    = "You need to be able to view HTML messages.";
  $mail->MsgHTML($body);
  $address = $to;
  $mail->AddAddress($address, $name);
  
  if (!$mail->Send()) {
    return 0;
  } 
  // else  //TODO: Why else? You already break with return
  // {
    return 1;
  // }
}

function compress($source, $destination, $quality) {

  $info = getimagesize($source);

  if ($info['mime'] == 'image/jpeg') 
      $image = imagecreatefromjpeg($source);

  elseif ($info['mime'] == 'image/gif') 
      $image = imagecreatefromgif($source);

  elseif ($info['mime'] == 'image/png') 
      $image = imagecreatefrompng($source);

  imagejpeg($image, $destination, $quality);

  return $destination; //TODO: This is a weird return to give the original parameter given without change?
}

// Function to get the client ip address
/**
 * Returns the client IP, either from where forwarded or remote address.
 * Failing results in unknown or error string.
 *
 * @return string returns IP or UNKNOWN
 */
function get_client_ip_server() {
  //TODO: This should be in a session object for accessing server global.

  if ($_SERVER['HTTP_CLIENT_IP'])
      return filter_input(INPUT_SERVER, 'HTTP_CLIENT_IP', FILTER_SANITIZE_STRING);
  else if($_SERVER['HTTP_X_FORWARDED_FOR'])
      return filter_input(INPUT_SERVER, 'HTTP_X_FORWARDED_FOR', FILTER_SANITIZE_STRING);
  else if($_SERVER['HTTP_X_FORWARDED'])
      return filter_input(INPUT_SERVER, 'HTTP_X_FORWARDED', FILTER_SANITIZE_STRING);
  else if($_SERVER['HTTP_FORWARDED_FOR'])
      return filter_input(INPUT_SERVER, 'HTTP_FORWARDED_FOR', FILTER_SANITIZE_STRING);
  else if($_SERVER['HTTP_FORWARDED'])
      return filter_input(INPUT_SERVER, 'HTTP_FORWARDED', FILTER_SANITIZE_STRING);
  else if($_SERVER['REMOTE_ADDR'])
      return filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_SANITIZE_STRING);

  return 'UNKNOWN';
}
