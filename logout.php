<?php
require_once 'config/config.php';
require_once 'classes/User.php';

$user = new User();
$user->logout();

redirect('login.php');
?>
