<?php include '../app/init.php'; ?>
<?php include '../app/auth.php'; ?>
<?php
// find all members
$stmt = $pdo->prepare('SELECT * FROM users');
$stmt->execute();
$users = $stmt->FetchAll();
?>
<?php $page_title = "All Members"; ?>
<?php include 'header.php'; ?>
<?php include 'navbar.php'; ?>

<div class="container-fluid mt-5">
  <div class="row">
    <div class="col-sm-12">
        <div class="row">
        <?php foreach($users as $user): ?>
          <?php
          // get user meme count
          $stmt = $pdo->prepare('SELECT COUNT(id) AS total FROM memes WHERE user_id = :profile');
          $stmt->bindParam(':profile', $user['id']);
          $stmt->execute();
          $meme_count = $stmt->Fetch();
          // get user profile views
          $stmt = $pdo->prepare('SELECT COUNT(id) AS total FROM user_impressions WHERE profile_id = :profile');
          $stmt->bindParam(':profile', $user['id']);
          $stmt->execute();
          $profile_views = $stmt->Fetch();
          // get meme impressions
          $stmt = $pdo->prepare('SELECT COUNT(meme_impressions.id) AS total FROM `meme_impressions` INNER JOIN memes ON meme_impressions.meme_name = memes.name INNER JOIN users ON memes.user_id = users.id WHERE users.id = :profile');
          $stmt->bindParam(':profile', $user['id']);
          $stmt->execute();
          $meme_impression = $stmt->Fetch();
          ?>
          <div class="col-sm-2 mb-3">
            <div class="card">
              <img src="<?php if(!empty($user['avatar'])) {echo $user['avatar']; } else { echo '/uploads/avatars/default.jpg'; } ?>" class="card-img-top img-fluid" alt="<?=$user['username'];?>" width="50%">
              <div class="card-body">
                <h5 class="card-title"><?=$user['username'];?></h5>
                  <div class="row mt-3">
                    <div class="col-md-4 col-sm-4 text-center">
                      <h2><strong><?= $meme_count['total']; ?></strong></h2>
                      <p>Memes</p>
                    </div>
                    <div class="col-md-4 col-sm-4 text-center">
                      <h2><strong><?= $meme_impression['total'];?></strong></h2>
                      <p>Meme Views</p>
                    </div>
                    <div class="col-md-4 col-sm-4 text-center">
                      <h2><strong><?= $profile_views['total'];?></strong></h2>
                      <p>Profile Views</p>
                    </div>
                  </div>
                <a href="profile.php?i=<?=$user['id'];?>" class="btn btn-primary btn-block">View Member</a>
              </div>
            </div>
          </div>
      <?php endforeach;?>
      </div>
    </div>
  </div>
</div>
<hr>
<?php include 'footer.php'; 
// clear the session messages
unset($_SESSION['heading']);
unset($_SESSION['message']);
?>