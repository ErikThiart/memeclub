<?php include '../app/init.php'; ?>
<?php
if(empty($_SESSION['heading']) OR empty($_SESSION['message'])) {
  redirect('index.php');
}
?>
<?php include 'header.php'; ?>
<?php include 'navbar.php'; ?>
<div class="container">
  <div class="row align-items-center mt-5">
    <div class="col-sm">
      <div class="alert alert-danger p-5" role="alert">
        <h3 class="alert-heading"><?php echo $_SESSION['heading']; ?></h3>
        <p class="lead"><?php echo $_SESSION['message']; ?></p>
      </div>
  </div>
</div>
<?php include 'footer.php'; ?>
<?php
// clear the session messages
unset($_SESSION['heading']);
unset($_SESSION['message']);
?>