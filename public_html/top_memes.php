<?php include '../app/init.php'; ?>
<?php include '../app/auth.php'; ?>
<?php $page_title = "Top Memes"; ?>
<?php $masonry = "load"; ?>
<?php include 'header.php'; ?>
<?php include 'navbar.php'; ?>
<?php
// get all memes
$stmt = $pdo->prepare('SELECT users.username, users.id, memes.path, memes.name, memes.id AS meme_id, SUM(meme_votes.vote_type = "upvote") - SUM( meme_votes.vote_type = "downvote" ) AS votes FROM users INNER JOIN memes ON users.id = memes.user_id INNER JOIN meme_votes ON memes.name = meme_votes.meme_name GROUP BY memes.path HAVING votes >= 1 ORDER BY votes DESC');
$stmt->execute();
$memes = $stmt->fetchAll();
?>
<main class="container-fluid mt-4">
  <div class="row">
    <article class="col-sm-12">
    <?php if($memes):?>
    <div class="card-columns">
        <?php foreach($memes as $meme): ?>      
        <div class="card">
          <a href="image.php?id=<?= $meme['name'];?>&meme=<?= $meme['meme_id'];?>"><img src="<?= $meme['path'];?>" class="img-fluid mx-auto d-block"></a>
          <div class="card-body text-center">
            <p class="card-text mb-1"><a href="profile.php?i=<?=$meme['id'];?>"><?=$meme['username'];?></a>
            <span class="float-right text-primary mr-2 mb-1"><img src="iconic/heart-solid.svg" class="img-fluid" width="15px" />
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
            </p>
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
  </div>
</main>
<?php include 'footer.php'; 
// clear the session messages
unset($_SESSION['heading']);
unset($_SESSION['message']);
?>