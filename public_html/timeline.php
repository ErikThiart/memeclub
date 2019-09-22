<?php include '../app/init.php'; ?>
<?php include '../app/auth.php'; ?>
<?php $page_title = "Timeline"; ?>
<?php $masonry = "load"; ?>
<?php include 'header.php'; ?>
<?php include 'navbar.php'; ?>
<?php
// get all memes
$stmt = $pdo->prepare('SELECT users.username, users.id, memes.path, memes.name, memes.created, memes.id AS meme_id FROM users INNER JOIN memes ON users.id = memes.user_id ORDER BY memes.created DESC LIMIT 50');
$stmt->execute();
$memes = $stmt->fetchAll();
// get all online users
$stmt = $pdo->prepare('SELECT DISTINCT(users.username), users.id FROM users INNER JOIN sessions ON users.id = sessions.user_id WHERE sessions.logout_time IS NULL');
$stmt->execute();
$online_users = $stmt->fetchAll();
?>
<main class="container-fluid mt-4">
  <div class="row">
    <article class="col-sm-12 col-lg-10">
    <?php if($memes): ?>
    <?php $i = 1; ?>
    <div class="card-columns">
        <?php foreach($memes as $meme): ?>
        <?php  $card_id = $i++; ?>
        <div class="card" id="<?=$card_id;?>">
          <a href="image.php?id=<?= $meme['name'];?>&meme=<?= $meme['meme_id'];?>"><img src="<?= $meme['path'];?>" class="img-fluid mx-auto d-block"></a>
          <div class="card-body text-center">
            <p class="card-text mb-1"><a href="profile.php?i=<?=$meme['id'];?>"><?=$meme['username'];?></a></p>
            <!-- -->
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
                  <input type="image" src="iconic/arrow-up-solid<?php if($voted): ?>-active<?php endif; ?>.svg"width="13px" style="fill: red;">
                  <input type="hidden" name="meme_id" value="<?= $meme['name'];?>">
                  <input type="hidden" name="vote" value="upvote" >
                  <input type="hidden" name="token" value="<?= $csrf_token;?>" />
                  <input type="hidden" name="card_id" value="<?= $card_id;?>" />
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
                  <input type="image" src="iconic/arrow-down-solid<?php if($voted): ?>-active<?php endif; ?>.svg" width="13px">
                  <input type="hidden" name="meme_id" value="<?= $meme['name'];?>">
                  <input type="hidden" name="vote" value="downvote">
                  <input type="hidden" name="token" value="<?= $csrf_token;?>" />
                  <input type="hidden" name="card_id" value="<?= $card_id;?>" />
                  <?php if($voted): ?>
                  </fieldset>
                  <?php endif; ?>
                </form>
                <span class="float-right text-primary"><img src="iconic/heart-solid.svg" class="img-fluid" width="13px" />
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
            <!-- -->
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
    <h1 class="display-4">No memes in the system yet.</h1>
    <?php endif;?>
    </article>
    <aside class="col-sm-12 col-lg-2">
      <h2>Upload a meme</h2>
      <?php if(!empty($_SESSION['heading'])): ?>
        <div class="alert alert-danger" role="alert">
        <h5 class="alert-heading"><?php echo $_SESSION['heading']; ?></h5>
        <p><?php echo $_SESSION['message']; ?></p>
      </div>
      <?php endif;?>
      <form action="upload_meme.php" method="post" enctype="multipart/form-data" class="mb-4">
        <div class="form-group">
          <label for="meme">Select and upload a Meme</label>
          <input type="file" class="form-control-file" id="meme" name="meme">
        </div>
        <input type="hidden" name="token" value="<?= $csrf_token;?>" />
        <button type="submit" class="btn btn-block btn-primary">Upload</button>
      </form>
      <h2>Online Users</h2>
      <?php if($online_users): ?>
        <?php foreach($online_users as $online_user): ?>
          <a href="profile.php?i=<?=$online_user['id'];?>"><span class="badge badge-success p-2 mb-1"><?= $online_user['username']; ?></span></a>
        <?php endforeach; ?>
      <?php else: ?>
      <h2>No one is currently online.</h2>
      <?php endif; ?>
    </aside>
  </div>
</main>
<?php include 'footer.php'; 
// clear the session messages
unset($_SESSION['heading']);
unset($_SESSION['message']);
?>