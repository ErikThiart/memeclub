<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarTogglerDemo03" aria-controls="navbarTogglerDemo03" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <a class="navbar-brand" href="index.php">MemeClub</a>

  <div class="collapse navbar-collapse" id="navbarTogglerDemo03">
    <ul class="navbar-nav mr-auto mt-2 mt-lg-0">
      <?php if(empty($_SESSION['id'])): ?>
      <li class="nav-item">
        <a class="nav-link" href="index.php">Login</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="register.php">Register</a>
      </li>
      <?php endif; ?>
      <?php if(!empty($_SESSION['id'])): ?>
      <li class="nav-item">
        <a class="nav-link" href="timeline.php">Timeline</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="all_memes.php">All Memes</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="top_memes.php">Top Memes</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="all_members.php">All Members</a>
      </li>
      <?php endif; ?>
    </ul>
    <?php if(!empty($_SESSION['id'])): ?>
<!--
    <form class="form-inline my-2 my-lg-0">
      <input class="form-control mr-sm-2" type="search" placeholder="Member Name" aria-label="Search">
      <button class="btn btn-outline-info my-2 my-sm-0" type="submit">Search Members</button>
    </form>
-->
    <ul class="navbar-nav ml-auto">
      <li class="nav-item dropdown hidden-md-down">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <strong><?= $_SESSION['username'] ;?></strong>
        </a>
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
          <a class="dropdown-item" href="my_profile.php">My Profile</a>
          <div class="dropdown-divider"></div>
          <a href="logout.php" class="dropdown-item"><strong>Logout</strong></a>
        </div>
      </li>
    </ul>
    <?php endif; ?>
  </div>
</nav>
