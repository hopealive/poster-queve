<?php
error_reporting(E_ALL);
session_start();
date_default_timezone_set('Europe/Kiev');

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__FILE__));
define('ADMIN_PATH', dirname(__FILE__).DS.'admin');
define('VIEWS_PATH', ROOT.DS.'view');

require_once(ROOT.DS.'lib'.DS.'App.php');
App::run($_SERVER['REQUEST_URI']);

$poster = new Poster();
$orders = $poster->getLastTransactions();
echo json_encode($orders);
?>

