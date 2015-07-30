<?php
// Bookstore in AMP
// 
// Author: Min Xu <xukmin@gmail.com>
//
// This page shows order details given an order_id from HTTP GET request.

require_once 'shared_functions.php';
require_once 'order_functions.php';

requireLogin();

function showOrder() {
  $connection = connect();
  showOrderFromOrderId($connection, $_GET['order_id']);
  mysql_close($connection);
}

showHeader('Order Details');
showOrder();
showFooter();
?>
