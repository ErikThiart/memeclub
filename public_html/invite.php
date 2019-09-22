<?php include '../app/init.php'; 
// if the refered user is logged in - redirect (normally a case when a user clicks on his own link from his profile)
if(!empty($_SESSION['id'])) {
    $_SESSION['heading'] = 'Your URL is working fine.';
    $_SESSION['message'] = 'No point in clicking on it - copy it and send it to someone, or tweet it with the hashtag #memeclub.';
    redirect('my_profile.php');
}
if(!empty($_GET['code'])) {
  //clean and set code
  $referrer_code = clean($_GET['code']);
  $stmt = $pdo->prepare('SELECT id FROM users WHERE invite_code = :referrer_code');
  $stmt->bindParam(':referrer_code', $referrer_code);
  $stmt->execute();
  if($referrer = $stmt->Fetch()) {
    $_SESSION['referrer_id'] = $referrer['id'];
    redirect('register.php');
  } else {
    redirect('register.php');
  }
}
?>