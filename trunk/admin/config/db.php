<?php
function db_c() {
	$dbserver	= '127.0.0.1';
	$dbuser		= 'root';
	$dbpass		= 'rares';
	$dbname		= 'myshop';
	$db_conn = mysql_connect($dbserver, $dbuser, $dbpass)
	or die ("Eroare mySQL : Nu ma pot conecta la baza de date.");
	mysql_select_db($dbname)
	or die ("Eroare mySQL : Nu pot selecta baza de date.");
	mysql_query("SET NAMES 'utf8';",$db_conn);
	return $db_conn;
}

//init Doctrine Connection
$config = MyShop_Config::getInstance()->load('db.ini');
$pdoUri = "mysql://{$config->database->mysql->user}"
        . ":{$config->database->mysql->pass}@{$config->database->mysql->host}"
        . "/{$config->database->mysql->db}";
$conn = Doctrine_Manager::connection($pdoUri);
$conn->setAttribute(Doctrine::ATTR_USE_NATIVE_ENUM, true);
Doctrine_Manager::getInstance()->setAttribute(Doctrine_Core::ATTR_AUTOLOAD_TABLE_CLASSES, true);

if($config->database->mysql->charset) {
    $conn->setCharset($config->database->mysql->charset);
}
$config->loadDatabaseConfig();
?>