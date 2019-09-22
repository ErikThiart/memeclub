<?php include '../app/init.php'; ?>
<?php include '../app/auth.php'; ?>
<?php
//TODO: All of this into a functions file, why is it here?
// set the user id
$profile_id = clean($_SESSION['id']);
// get user details
$stmt = $pdo->prepare('SELECT id, username, avatar, invite_code FROM users WHERE id = :profile');
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
// count the number of referrals
$stmt = $pdo->prepare('SELECT COUNT(id) AS total FROM referrals WHERE referrer_id = :profile');
$stmt->bindParam(':profile', $profile_id);
$stmt->execute();
$referrals = $stmt->Fetch();
?>
<?php $page_title = "My Profile"; ?>
<?php include 'header.php'; ?>
<?php include 'navbar.php'; ?>

<div class="container mt-5">
  <div class="row">
    <div class="col-md-8 col-sm-8">
      <h2 class="card-title text-primary"><?= $user['username'];?></h2>
      <p><strong class="text-primary">Trophies: </strong><br>
        <span class="badge bg-info text-white p-1">Active Member</span>
        <?php if($referral_count['total'] > 0):?>
        <span class="badge bg-info text-white p-1" data-toggle="tooltip" data-placement="top" title="Begotten when you invite someone to the memeclub">Memester</span>
        <?php endif;?>
      </p>
      <h4 class="mt-4">Refferal System</h4>
      <p>Invite people to the memeclub using your link:<br> <a href="<?= $app_url;?>/invite.php?code=<?= $user['invite_code'];?>"><?= $app_url;?>/invite.php?code=<?= $user['invite_code'];?></a></p>
      <p>You have referred <?php echo $referrals['total'];?> people so far.</p>
    </div>
    <div class="col-md-4 col-sm-4 text-center">
      <?php if(!empty($_SESSION['heading'])): ?>
        <div class="alert alert-danger" role="alert">
        <h5 class="alert-heading"><?php echo $_SESSION['heading']; ?></h5>
        <p><?php echo $_SESSION['message']; ?></p>
      </div>
      <?php endif;?>
      <img class="img-fluid" src="<?php if(!empty($user['avatar'])) {echo $user['avatar']; } else { echo '/uploads/avatars/default.jpg'; } ?>" alt="profile picture" style="border-radius:50%;">
      <form action="upload_profile_picture.php" method="post" enctype="multipart/form-data" class="mb-4">
        <div class="form-group">
          <input type="file" class="form-control-file" id="profile_picture" name="profile_picture">
        </div>
        <input type="hidden" name="token" value="<?= $csrf_token;?>" />
        <button type="submit" class="btn btn-sm btn-block btn-primary">Upload Profile Picture</button>
      </form>
    </div>
  </div>
  <div class="row mt-3">
    <div class="col-md-4 col-sm-4 text-center">
      <h2><strong><?= $meme_count['total']; ?></strong></h2>
      <p>My Memes</p>
    </div>
    <div class="col-md-4 col-sm-4 text-center">
      <h2><strong><?= $meme_impression['total'];?></strong></h2>
      <p>My Meme Impressions</p>
    </div>
    <div class="col-md-4 col-sm-4 text-center">
      <h2><strong><?= $profile_views['total'];?></strong></h2>
      <p>My Profile Views</p>
    </div>
  </div>
</div>
<hr>
<?php include 'footer.php'; 
// clear the session messages
unset($_SESSION['heading']);
unset($_SESSION['message']);
?>