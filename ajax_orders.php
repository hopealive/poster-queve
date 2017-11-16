<?php
require_once('Lib/Poster.php');
$orders = (new Poster())->getLastTransactions();
echo json_encode($orders);
?>

