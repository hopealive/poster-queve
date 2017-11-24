<?php
require_once('lib/Poster.php');
$orders = (new Poster())->getLastTransactions();
echo json_encode($orders);
?>

