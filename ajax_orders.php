<?php
require_once('Lib/Poster.php');
$orders = (new Poster())->getIncomingOrders();
echo json_encode($orders);
?>

