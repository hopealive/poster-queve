<?php
require_once('Lib/Poster.php');

$Poster = new Poster();
$orders = $Poster->getIncomingOrders();

$row = "";
foreach ($orders as $order) {
    $row .= "<tr><td>".$order['id']."</td>";
    switch ($order['status']) {
        case 0:
            $row .= '<td class="status-in-progress">Очікування</td>';
            break;
        case 1:
            $row .= '<td class="status-complete">Готово</td>';
            break;
        default:
            $row .= '<td>n/a</td>';
            break;
    }
    $row .= '</tr>';
}
    echo json_encode(['data' => $row]);
?>

