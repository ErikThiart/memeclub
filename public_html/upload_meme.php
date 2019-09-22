<?php
include '../app/init.php';
include '../app/auth.php';
require '../vendor/samayo/bulletproof/src/utils/func.image-resize.php';
if($_SERVER['REQUEST_METHOD'] === 'POST') {
  if(hash_equals($_SESSION['csrf_token'], $_POST['token'])) {
    $image = new Bulletproof\Image($_FILES);

    $name = bin2hex(random_bytes(3).md5($_SESSION['id']).time().random_bytes(2));
    $min = 10240;
    $max = 3000000;
    $folderName = "uploads";

    // Pass a custom name, or leave it if you want it to be auto-generated
    $image->setName($name); 

    // define the min/max image upload size (size in bytes) 
    $image->setSize($min, $max); 

    // define allowed mime types to upload
    $image->setMime(array('jpeg', 'gif', 'png'));  

    // pass name (and optional chmod) to create folder for storage
    $image->setLocation($folderName);  

    if($image["meme"]) {
      $upload = $image->upload(); 

      if($upload) {
            bulletproof\utils\resize (
                $image->getFullPath(), 
                $image->getMime(),
                $image->getWidth(),
                $image->getHeight(),
                1024,
                512
            );
        // compress meme
        compress($image->getFullPath(), $image->getFullPath(), 90);
        $full_img_path = $image->getFullPath();
        // save meme in DB
        $img_name = $image->getName().'.'.$image->getMime();
        $stmt = $pdo->prepare('INSERT INTO memes (name, path, user_id) VALUES (:name, :path, :user_id)');
        $stmt->bindParam(':user_id', $_SESSION['id']);
        $stmt->bindParam(':path', $full_img_path);
        $stmt->bindParam(':name', $img_name);
        $stmt->execute();
        redirect('timeline.php');
      } else {
        $_SESSION['heading'] = "Upload Error";
        $_SESSION['message'] = $image->getError();
        redirect('timeline.php');
      }
    }
  //end csrf
  } else {
    redirect('logout.php');
  }
// end request method  
} else {
  redirect('logout.php');
}