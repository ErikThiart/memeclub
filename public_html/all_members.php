<?php
  /**
   * TODO: Description of what this file does missing.
   */

  include '../app/init.php';
  include '../app/auth.php';
  // find all members
  $stmt = $pdo->prepare('SELECT * FROM users');
  $stmt->execute();
  $users = $stmt->FetchAll();
  
  $allMembers = [];
  $indexCounter = 0;

  foreach($users as $user) {
    $allMembers += new User();
    $allMembers[$indexCounter]->initUserFromAll($user, $pdo);
    $indexCounter++;
  }
 
  $page_title = "All Members"; 
  include 'header.php'; 
  include 'navbar.php'; 
?>

<div class="container-fluid mt-5">
  <div class="row">
    <div class="col-sm-12">
        <div class="row">
        <?php foreach($allMembers as $user): ?>
          <div class="col-sm-2 mb-3">
            <div class="card">
              <img src="<?= $user->avatar;?>" class="card-img-top img-fluid" alt="<?=$user->username;?>" width="50%">
              <div class="card-body">
                <h5 class="card-title"><?=$user->username;?></h5>
                  <div class="row mt-3">
                    <div class="col-md-4 col-sm-4 text-center">
                      <h2><strong><?= $user->memeCount; ?></strong></h2>
                      <p>Memes</p>
                    </div>
                    <div class="col-md-4 col-sm-4 text-center">
                      <h2><strong><?= $user->memeImpressions;?></strong></h2>
                      <p>Meme Views</p>
                    </div>
                    <div class="col-md-4 col-sm-4 text-center">
                      <h2><strong><?= $user->profileViews;?></strong></h2>
                      <p>Profile Views</p>
                    </div>
                  </div>
                <a href="profile.php?i=<?=$user->userID;?>" class="btn btn-primary btn-block">View Member</a>
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