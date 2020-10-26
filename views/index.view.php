<?php require('partials/header.php');?>
<h1>Users</h1>
<?php foreach($users as $user): ?>
    <li><?= $user; ?></li>
<?php endforeach;?>
<?php require('partials/footer.php');?>
