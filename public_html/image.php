<?php include '../app/init.php'; ?>
<?php include '../app/auth.php'; ?>
<?php
if($_SERVER['REQUEST_METHOD'] === 'GET') {
   // The request is using the GET method
   if(!empty($_GET['id']) AND !empty($_GET['meme'])) {
     if(!filter_input(INPUT_GET, "meme", FILTER_VALIDATE_INT)) {
        redirect('timeline.php');
     }
     $image_name = clean($_GET['id']);
     $meme_id = clean($_GET['meme']);
     $stmt = $pdo->prepare('INSERT INTO meme_impressions (meme_name, user_id) VALUES (:meme_name, :user_id)');
     $stmt->bindParam(':meme_name', $image_name);
     $stmt->bindParam(':user_id', $_SESSION['id']);
     $stmt->execute();
     //get the next image
     $next_image = $meme_id - 1;
     $stmt = $pdo->prepare('SELECT id, name FROM memes WHERE id = :next_image LIMIT 1');
     $stmt->bindParam(':next_image', $next_image);
     $stmt->execute();
   } else {
     redirect('logout.php');
   }
}
?>
<?php include 'header.php'; ?>
<?php include 'navbar.php'; ?>
<div class="container">
  <div class="row mb-4">
    <div class="col-sm mt-5 text-center">
      <img src="uploads/<?= $image_name ;?>" class="img-fluid mx-auto d-block">
      <a href="timeline.php" class="btn btn-primary mt-5">Back to Timeline</a>
      <?php if($meme = $stmt->Fetch()): ?>
       <a href="image.php?id=<?= $meme['name'];?>&meme=<?= $meme['id'];?>" class="btn btn-primary mt-5">NEXT MEME >></a>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include 'footer.php'; ?>