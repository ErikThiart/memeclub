<?php
// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// Load Composer's autoloader
require '../vendor/autoload.php';

function register_user($username, $email, $password_hash, $referrer_id) 
{
    // get the DB
    global $pdo;
    $app_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}";
    // create the token
    $token = bin2hex(random_bytes(32));
    // create invite code
    $invite_code = generate_referral_code(6);
    // register the user
    try {
        $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash, activation_token, invite_code) VALUES (:username, :email, :password, :token, :invite_code)');
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
        if(sendmail($email,$subject,$message,$username)) {
          //log the referral
          if(!empty($referrer_id)) {
              $stmt = $pdo->prepare('INSERT INTO referrals (referral_id, referrer_id) VALUES (:referral_id, :referrer_id)');
              $stmt->bindParam(':referral_id', $user_id);
              $stmt->bindParam(':referrer_id', $referrer_id);
              if($stmt->execute()) {
               //get the email of the referer
               $stmt = $pdo->prepare('SELECT email, username FROM users WHERE id = :referrer_id');
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
          $_SESSION['message'] = "Thank you for registering on our platform. We have sent you a confirmation E-mail which you need to accept before your registration is completed.<br> Please check your spam folder if you use gmail.";
          redirect('success.php');
        } else {
          $_SESSION['message'] = "Confirmation E-mail was not sent.";
          $_SESSION['heading'] = "Something went wrong.";
          redirect('error.php');
        }
        }
    catch(PDOException $e)
        {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['heading'] = "Something went wrong.";
        redirect('error.php');     
        }
}

function confirm_registraiton($token, $email)
{
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
     } else {
       $heading = "Something went wrong (Error 1)";
       $message = "Please contact support on info@memeclub.xyz and tell them the error code you received.";
       redirect('error.php');  
     }
  } else {
    $_SESSION['heading'] = "Invalid Token OR You have already been activated.";
    $_SESSION['message'] = "Please contact support on info@memeclub.xyz and we can investigate and activate you manually.";
    redirect('error.php');   
  }
}

function reset_password($hashed_pass, $user_id, $email, $reset_token) 
{
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


function redirect($url, $permanent = false) 
{
	if($permanent) {
		header('HTTP/1.1 301 Moved Permanently');
	}
	header('Location: '.$url);
	exit();
}

function clean($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

function sendmail($to, $subject, $message, $name)
{
  
  $mail = new PHPMailer(true);
  $body             = $message;
  $mail->IsSMTP();
  $mail->SMTPAuth   = true;
  $mail->Host       = "mail.mordor.co.za";
  $mail->Port       = 587;
  $mail->Username   = "app@mordor.co.za";
  $mail->Password   = ""; // given to colaborators
  $mail->SMTPSecure = 'tls';
  $mail->SetFrom('app@mordor.co.za', 'Memeclub');
  $mail->Subject    = $subject;
  $mail->AltBody    = "You need to be able to view HTML messages.";
  $mail->MsgHTML($body);
  $address = $to;
  $mail->AddAddress($address, $name);
  if(!$mail->Send()) 
  {
    return 0;
  } 
  else 
  {
    return 1;
  }
}

function compress($source, $destination, $quality) 
{

    $info = getimagesize($source);

    if ($info['mime'] == 'image/jpeg') 
        $image = imagecreatefromjpeg($source);

    elseif ($info['mime'] == 'image/gif') 
        $image = imagecreatefromgif($source);

    elseif ($info['mime'] == 'image/png') 
        $image = imagecreatefrompng($source);

    imagejpeg($image, $destination, $quality);

    return $destination;
}

// Function to get the client ip address
function get_client_ip_server() {
    $ipaddress = '';
    if ($_SERVER['HTTP_CLIENT_IP'])
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if($_SERVER['HTTP_X_FORWARDED_FOR'])
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if($_SERVER['HTTP_X_FORWARDED'])
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if($_SERVER['HTTP_FORWARDED_FOR'])
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if($_SERVER['HTTP_FORWARDED'])
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if($_SERVER['REMOTE_ADDR'])
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
 
    return $ipaddress;
}

// Function to generate the referral code
function generate_referral_code($length_of_string) 
{ 
    $str_result = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890abcdefghijklmnopqrstuvwxyz'; 
    return substr(str_shuffle($str_result), 0, $length_of_string); 
} 
