<?php

// Bookstore in AMP
// Author: Min Xu <xukmin@gmail.com>

require_once 'shared_functions.php';

requireLogin();

function composeQuery() {
  $query = "SELECT * FROM orders INNER JOIN payments" .
      " ON orders.card_number = payments.card_number";

  if (!empty($_COOKIE['customer_id'])) {
    $query .= " AND orders.customer_id = {$_COOKIE['customer_id']}";
  } else if (!empty($_POST['customer_id'])) {
    $query .= " AND orders.customer_id = {$_POST['customer_id']}";
  }

  $criteria = array();
  if (!empty($_POST['order_id'])) {
    $criteria[] = "orders.order_id = '${_POST['order_id']}'";
  }
  if (!empty($_POST['shipping_method'])) {
    $criteria[] = "orders.shipping_method = '${_POST['shipping_method']}'";
  }
  $num_criteria = count($criteria);
  if ($num_criteria > 0) {
    $query .= " WHERE";
    $query .= " ${criteria[0]}";
    for ($i = 1; $i < $num_criteria; $i++) {
      $query .= " AND ${criteria[$i]}";
    }
  }
  $query .= ";";

  return $query;
}

function showResults($results) {
  if (!$results) {
    die('Could not get search result: ' . mysql_error());
  }

  echo "<h2>Search Results</h2>";
  $num_rows = mysql_num_rows($results);
  if ($num_rows == 0) {
    echo "
        <p class='center'>
        There are no orders found. Please revise your criteria and try again.
        </p>";
    return;
  }
  echo "
      <table>
      <tr>
      <th>Order ID</th>
      <th>Customer ID</th>
      <th>Payment Method</th>
      <th>Shipping Method</th>
      <th>Date</th>
      </tr>";
  while ($row = mysql_fetch_assoc($results)) {
    $payment_method =
        composePaymentMethod($row['card_company'], $row['card_number']);
    echo "
    <tr>
      <td>
      <a href='show_order.php?order_id=${row['order_id']}'>
      ${row['order_id']}
      </a>
      </td>
      <td>{$row['customer_id']}</td>
      <td>{$payment_method}</td>
      <td>{$row['shipping_method']}</td>
      <td>{$row['order_date']}</td>
    </tr>";
  }
  echo "</table>";
  if ($num_rows == 1) {
    echo "<p class='center'>Found 1 order.</p>";
  } else {
    echo "<p class='center'>Found $num_rows orders.</p>";
  }
}

// TODO: Show more details in search results, e.g. number of books, total price.
function searchOrders() {
  if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    return;
  }
  $query = composeQuery();
  $connection = connect();
  $results = mysql_query($query, $connection);
  showResults($results);
  mysql_close($connection);
}

function showSearchOrdersForm() {
  echo "
      <form action='{$_SERVER['PHP_SELF']}' method='post'>
      <fieldset>
      <legend>Enter the search criteria below</legend>
      <p class='form'>
      <label>Order ID: </label><input type='text' name='order_id'>
      </p>";

  if (empty($_COOKIE['customer_id'])) {
    echo "
        <p class='form'>
        <label>Customer ID:</label><input type='text' name='customer_id'>
        </p>";
  } 

  echo "
      <p class='form'>
      <label>Shipping Method: </label>
      <input type='text' name='shipping_method'>
      </p>
      </fieldset>
      <br>
      <input type='submit' class='button' name='submit' value='Search Orders'>
      </form>";
}

showHeader('Search Orders');
showSearchOrdersForm();
searchOrders();
showFooter();
?>
