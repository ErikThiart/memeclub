<?php
include '../app/init.php';
include '../app/auth.php';
if($_SERVER['REQUEST_METHOD'] === 'POST') {
   if(hash_equals($_SESSION['csrf_token'], $_POST['token'])) { 
      $role = $_SESSION['role'];
      $owner_id = clean($_POST['owner_id']);
      $meme_name = clean($_POST['meme_name']);
      if(empty($owner_id) OR empty($meme_name) OR empty($role)) {
        redirect('logout.php');
      }
      //only the author and mods can delete memes
      if($role == 'mod' OR $owner_id == $_SESSION['id']) {
        // delete meme
        $stmt = $pdo->prepare('DELETE FROM memes WHERE name = :meme_name');
        $stmt->bindParam(':meme_name', $meme_name, PDO::PARAM_STR);   
        $stmt->execute();
        // delete impressions associated with the meme
        $stmt = $pdo->prepare('DELETE FROM meme_impressions WHERE meme_name = :meme_name');
        $stmt->bindParam(':meme_name', $meme_name, PDO::PARAM_STR);   
        $stmt->execute();
        // rename the meme in the file system and move it
        $meme_info = pathinfo("uploads/".$meme_name);
        $new_name = uniqid(substr($meme_info['filename'], 2, 50), true);
        rename("uploads/".$meme_name, "uploads/deleted/".$new_name.".".$meme_info['extension']);
        // redirect back to timeline
        redirect('timeline.php');
      } else {
        // not authed.
        redirect('logout.php');
      }
   // csrf end  
   } else {
     redirect('logout.php');
   }
//request method end  
} else {
  redirect('logout.php');
}