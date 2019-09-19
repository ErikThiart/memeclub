<?php
include '../app/init.php';
include '../app/auth.php';
require '../vendor/samayo/bulletproof/src/utils/func.image-resize.php';
if($_SERVER['REQUEST_METHOD'] == "POST") {
  if(hash_equals($_SESSION['csrf_token'], $_POST['token'])) {
      $image = new Bulletproof\Image($_FILES);

      $name = bin2hex(random_bytes(2).md5($_SESSION['username']).time().random_bytes(2));
      $min = 10240;
      $max = 3000000;
      $folderName = "uploads/avatars";

      // Pass a custom name, or leave it if you want it to be auto-generated
      $image->setName($name); 

      // define the min/max image upload size (size in bytes) 
      $image->setSize($min, $max); 

      // define allowed mime types to upload
      $image->setMime(array('jpeg', 'gif', 'png'));  

      // pass name (and optional chmod) to create folder for storage
      $image->setLocation($folderName);  

      if($image["profile_picture"]) {
        $upload = $image->upload(); 

        if($upload) {
              bulletproof\utils\resize (
                  $image->getFullPath(), 
                  $image->getMime(),
                  $image->getWidth(),
                  $image->getHeight(),
                  300,
                  300
              );
          // compress profile picture
          compress($image->getFullPath(), $image->getFullPath(), 90);
          $full_img_path = $image->getFullPath();
          // save profile picture in DB
          $stmt = $pdo->prepare('UPDATE users SET avatar = :avatar WHERE id = :user_id');
          $stmt->bindParam(':user_id', $_SESSION['id']);
          $stmt->bindParam(':avatar', $full_img_path);
          $stmt->execute();
          redirect('my_profile.php');
        } else {
          $_SESSION['heading'] = "Upload Error";
          $_SESSION['message'] = $image->getError();
          redirect('my_profile.php');
        }
      }
  } else {
     redirect('logout.php');
  }
} else {
   redirect('logout.php');
}