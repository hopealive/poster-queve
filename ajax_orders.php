<?php
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__FILE__));
define('ADMIN_PATH', dirname(__FILE__).DS.'admin');
define('VIEWS_PATH', ROOT.DS.'view');

require_once('lib/Log.php');
require_once('lib/Db.php');
require_once('lib/Crud.php');
require_once('lib/Poster.php');

$poster = new Poster();
$orders = $poster->getLastTransactions();
echo json_encode($orders);
?>

