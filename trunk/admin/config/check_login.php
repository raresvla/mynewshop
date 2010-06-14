<?php
session_name("MY_SHOP_ADMIN_by_Rares");
session_start();

//Initialisation settings
error_reporting(E_ALL  & ~E_NOTICE);
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
date_default_timezone_set('Europe/Bucharest');

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../../application'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    realpath(APPLICATION_PATH . '/models'),
    realpath(APPLICATION_PATH . '/models/generated'),
    get_include_path(),
)));

// Register Zend Auto Loader
require_once 'Zend/Loader/Autoloader.php';
$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->setFallbackAutoloader(true);
$autoloader->registerNamespace('MyShop_');
$autoloader->registerNamespace('Doctrine_');

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