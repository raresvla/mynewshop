<?php
session_name("MY_SHOP_ADMIN_by_Rares");
session_start();
require 'functii.php';
require 'server.php';
require 'class.user.php';

$browser = stripos($_SERVER['HTTP_USER_AGENT'], "msie") !== false ? "IE" : "NON-IE";

$user = new User(isset($_POST['__login']) ? $_POST : null);
if(!$user->loginStatus()) {
    //nelogat, afiseaza pagina de logare
    include "{$CALE_FIZICA_SERVER}login.php";
    die();
}