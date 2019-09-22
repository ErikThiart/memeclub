<?php
include '../app/init.php';
include '../app/auth.php';
if($_SERVER['REQUEST_METHOD'] == "POST") {
  if(hash_equals($_SESSION['csrf_token'], $_POST['token']) AND !empty($_SESSION['id'])) {
    $vote_type = clean($_POST['vote']);
    $meme_name = clean($_POST['meme_id']);
    if(!empty($_POST['card_id'])){
      $card_id = clean($_POST['card_id']); 
    } else {
      $card_id = "";
    }
    //handle the vote
    $stmt = $pdo->prepare('SELECT id FROM meme_votes WHERE user_id = :user_id AND meme_name = :meme_name');
    $stmt->bindParam(':user_id', $_SESSION['id'], PDO::PARAM_INT);  
    $stmt->bindParam(':meme_name', $meme_name, PDO::PARAM_STR);
    $stmt->execute();
    $voted = $stmt->fetch();
    if($voted) {
      //update the vote type
      $stmt = $pdo->prepare('UPDATE meme_votes SET vote_type = :vote_type WHERE user_id = :user_id AND meme_name = :meme_name');
      $stmt->bindParam(':meme_name', $meme_name, PDO::PARAM_STR); 
      $stmt->bindParam(':vote_type', $vote_type, PDO::PARAM_STR); 
      $stmt->bindParam(':user_id', $_SESSION['id'], PDO::PARAM_INT);                                         
      if($stmt->execute()) {
        redirect('timeline.php#'.$card_id);
      }
    } else {
      //new vote
      $stmt = $pdo->prepare('INSERT INTO meme_votes (vote_type, user_id, meme_name) VALUES (:vote_type, :user_id, :meme_name)');
      $stmt->bindParam(':meme_name', $meme_name, PDO::PARAM_STR); 
      $stmt->bindParam(':vote_type', $vote_type, PDO::PARAM_STR); 
      $stmt->bindParam(':user_id', $_SESSION['id'], PDO::PARAM_INT);                                         
      if($stmt->execute()) {
        redirect('timeline.php#'.$card_id);
      }
      
    }
  } else {
     redirect('logout.php');
  }
} else {
   redirect('logout.php');
}