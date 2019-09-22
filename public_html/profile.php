<?php include '../app/init.php'; ?>
<?php include '../app/auth.php'; ?>
<?php
if($_SERVER['REQUEST_METHOD'] === 'GET') {
   // The request is using the GET method //TODO: Move to a functions file, it's a repeat of my_profile
   if(!empty($_GET['i'])) {
     $profile_id = clean($_GET['i']);
     if($profile_id != $_SESSION['id']) {
       // log the profile view
       $stmt = $pdo->prepare('INSERT INTO user_impressions (profile_id, user_id) VALUES (:profile, :user)');
       $stmt->bindParam(':profile', $profile_id);
       $stmt->bindParam(':user', $_SESSION['id']);
       $stmt->execute(); 
     }
     // get user details
     $stmt = $pdo->prepare('SELECT id, username, avatar FROM users WHERE id = :profile');
     $stmt->bindParam(':profile', $profile_id);
     $stmt->execute();
     $user = $stmt->Fetch();
     // get user meme count
     $stmt = $pdo->prepare('SELECT COUNT(id) AS total FROM memes WHERE user_id = :profile');
     $stmt->bindParam(':profile', $profile_id);
     $stmt->execute();
     $meme_count = $stmt->Fetch();
     // get user profile views
     $stmt = $pdo->prepare('SELECT COUNT(id) AS total FROM user_impressions WHERE profile_id = :profile');
     $stmt->bindParam(':profile', $profile_id);
     $stmt->execute();
     $profile_views = $stmt->Fetch();
     // get meme impressions
     $stmt = $pdo->prepare('SELECT COUNT(meme_impressions.id) AS total FROM `meme_impressions` INNER JOIN memes ON meme_impressions.meme_name = memes.name INNER JOIN users ON memes.user_id = users.id WHERE users.id = :profile');
     $stmt->bindParam(':profile', $profile_id);
     $stmt->execute();
     $meme_impression = $stmt->Fetch();
     // get referral count
     $stmt = $pdo->prepare('SELECT COUNT(id) AS total FROM referrals WHERE referrer_id = :profile');
     $stmt->bindParam(':profile', $profile_id);
     $stmt->execute();
     $referral_count = $stmt->Fetch();
     // get all the user's memes
     $stmt = $pdo->prepare('SELECT users.username, users.id, memes.path, memes.name, memes.created, memes.id AS meme_id FROM users INNER JOIN memes ON users.id = memes.user_id WHERE memes.user_id = :profile ORDER BY memes.created DESC');
     $stmt->bindParam(':profile', $profile_id);
     $stmt->execute();
     $memes = $stmt->fetchAll();
   } else {
     redirect('logout.php');
   }
}
?>
<?php $page_title = $user['username']."'s" ." profile"; ?>
<?php $masonry = "load"; ?>
<?php include 'header.php'; ?>
<?php include 'navbar.php'; ?>

<div class="container mt-5">
  <div class="row">
    <div class="col-md-8 col-sm-8">
      <h2 class="card-title"><?= $user['username'];?></h2>
      <p><strong>Trophies: </strong><br>
        <span class="badge bg-info text-white p-1">Active Member</span>
        <?php if($referral_count['total'] > 0):?>
        <span class="badge bg-info text-white p-1" data-toggle="tooltip" data-placement="top" title="Begotten when you invite someone to the memeclub">Memester</span>
        <?php endif;?>
    </div>
    <div class="col-md-4 col-sm-4 text-center">
      <img class="img-fluid" src="<?php if(!empty($user['avatar'])) {echo $user['avatar']; } else { echo '/uploads/avatars/default.jpg'; } ?>" alt="profile picture" style="border-radius:50%;">
    </div>
  </div>
  <div class="row mt-3">
    <div class="col-md-4 col-sm-4 text-center">
      <h2><strong><?= $meme_count['total']; ?></strong></h2>
      <p>Memes</p>
    </div>
    <div class="col-md-4 col-sm-4 text-center">
      <h2><strong><?= $meme_impression['total'];?></strong></h2>
      <p>Meme Impressions</p>
    </div>
    <div class="col-md-4 col-sm-4 text-center">
      <h2><strong><?= $profile_views['total'];?></strong></h2>
      <p>Profile Views</p>
    </div>
  </div>
</div>
<hr>
<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <?php if($memes):?>
      <div class="card-columns">
          <?php foreach($memes as $meme): ?>      
          <div class="card">
            <a href="image.php?id=<?= $meme['name'];?>&meme=<?= $meme['meme_id'];?>"><img src="<?= $meme['path'];?>" class="img-fluid mx-auto d-block"></a>
            <div class="card-body text-center">
              <p class="card-text mb-1"><a href="profile.php?i=<?=$meme['id'];?>"><?=$meme['username'];?></a></p>
              <div class="row">
                <div class="col-sm-12">
                  <form action="upvote_meme.php" method="post" class="float-left mr-2 mt-1">
                    <?php
                      $stmt = $pdo->prepare('SELECT id FROM meme_votes WHERE vote_type = "upvote" AND user_id = :user_id AND meme_name = :meme_name');
                      $stmt->bindParam(':user_id', $_SESSION['id'], PDO::PARAM_INT);  
                      $stmt->bindParam(':meme_name', $meme['name'], PDO::PARAM_STR);
                      $stmt->execute();
                      $voted = $stmt->fetch();
                    ?>
                    <?php if($voted): ?>
                    <fieldset disabled="disabled">
                    <?php endif; ?>
                    <input type="image" src="iconic/arrow-up-solid<?php if($voted): ?>-active<?php endif; ?>.svg"width="15px" style="fill: red;">
                    <input type="hidden" name="meme_id" value="<?= $meme['name'];?>">
                    <input type="hidden" name="vote" value="upvote" >
                    <input type="hidden" name="token" value="<?= $csrf_token;?>" />
                    <?php if($voted): ?>
                    </fieldset>
                    <?php endif; ?>
                  </form>
                  <form action="upvote_meme.php" method="post" class="float-left ml-2 mt-1">
                    <?php
                      $stmt = $pdo->prepare('SELECT id FROM meme_votes WHERE vote_type = "downvote" AND user_id = :user_id AND meme_name = :meme_name');
                      $stmt->bindParam(':user_id', $_SESSION['id'], PDO::PARAM_INT);  
                      $stmt->bindParam(':meme_name', $meme['name'], PDO::PARAM_STR);
                      $stmt->execute();
                      $voted = $stmt->fetch();
                    ?>
                    <?php if($voted): ?>
                    <fieldset disabled="disabled">
                    <?php endif; ?>
                    <input type="image" src="iconic/arrow-down-solid<?php if($voted): ?>-active<?php endif; ?>.svg" width="15px">
                    <input type="hidden" name="meme_id" value="<?= $meme['name'];?>">
                    <input type="hidden" name="vote" value="downvote">
                    <input type="hidden" name="token" value="<?= $csrf_token;?>" />
                    <?php if($voted): ?>
                    </fieldset>
                    <?php endif; ?>
                  </form>
                  <span class="float-right text-primary"><img src="iconic/heart-solid.svg" class="img-fluid" width="15px" />
                  <?php 
                    $stmt = $pdo->prepare('SELECT COUNT(id) AS total FROM meme_votes WHERE vote_type = "upvote" AND meme_name = :meme_name');
                    $stmt->bindParam(':meme_name', $meme['name'], PDO::PARAM_STR);
                    $stmt->execute();
                    $upvote = $stmt->fetch();
                    $stmt = $pdo->prepare('SELECT COUNT(id) AS total FROM meme_votes WHERE vote_type = "downvote" AND meme_name = :meme_name');
                    $stmt->bindParam(':meme_name', $meme['name'], PDO::PARAM_STR);
                    $stmt->execute();
                    $downvote = $stmt->fetch();
                    $total_votes = $upvote['total'] - $downvote['total'];
                    echo $total_votes;
                  ?>
                  </span>
                </div>
              </div>
              <?php if($meme['id'] == $_SESSION['id'] OR $_SESSION['role'] == 'mod'):?>
              <small class="text-muted">
              <form action="delete_meme.php" method="post" class="masonry-description">
                <input type="hidden" name="owner_id" value="<?= $meme['id'];?>">
                <input type="hidden" name="meme_name" value="<?= $meme['name'];?>">
                <input type="hidden" name="token" value="<?= $csrf_token;?>" />
                <button type="submit" class="btn btn-link btn-sm" onclick="return confirm('Are you sure you want to delete this item?');">Delete Meme</button>
              </form>
              </small>
              <?php endif;?>
            </div>
          </div>
          <?php endforeach;?>
      </div>
      <?php else:?>
      <p class="mt-3 text-center">This user has not submitted any memes yet.</p>
      <?php endif;?>
    </div>
  </div>
</div>
<hr>
<?php include 'footer.php'; ?>