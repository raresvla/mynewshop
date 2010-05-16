<?php
function db_c()
{
	$dbserver	= 'localhost';
	$dbuser		= 'root';
	$dbpass		= 'rares';
	$dbname		= 'mynewshop';
	$db_conn = mysql_connect($dbserver, $dbuser, $dbpass)
	or die ("Eroare mySQL : Nu ma pot conecta la baza de date.");
	mysql_select_db($dbname)
	or die ("Eroare mySQL : Nu pot selecta baza de date.");
	mysql_query("SET NAMES 'utf8';",$db_conn);
	return $db_conn;
}
?>