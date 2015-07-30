<?php

// Bookstore in AMP
// Author: Min Xu <xukmin@gmail.com>

require_once 'shared_functions.php';
require_once 'order_functions.php';

requireCustomerLogin();

// TODO: Execute the SQL statements in a transaction.

function getQuantity($connection, $isbn) {
  $query = "SELECT quantity FROM inventory WHERE isbn = '{$isbn}';";
  $result = mysql_query($query, $connection);
  if (!$result) {
    $quantity = 0;
  } else {
    $row = mysql_fetch_assoc($result);
    $quantity = intval($row['quantity']);
  }
  return $quantity;
}

function updateInventory($connection, $books) {
  foreach ($books as $isbn => $quantity) {
    $old_quantity = getQuantity($connection, $isbn);
    if ($old_quantity < $quantity) {
      $missing = $quantity - $old_quantity;
      if ($missing == 1) {
        // TODO: Add links to the missing books.
        echo "
          <p class='center'>There lacks 1 copy of book with
          <em><strong>ISBN {$isbn}</strong></em>.</p>";
      } else {
        echo "
          <p class='center'>There lack {$missing} copies of book with
          <em><strong>ISBN {$isbn}</strong></em>.</p>";
      }
      return false;
    }
    $new_quantity = $old_quantity - $quantity;
    $query = "UPDATE inventory SET quantity = $new_quantity" .
        " WHERE isbn = '{$isbn}';";
    $result = mysql_query($query, $connection);
    if (!$result) {
      echo "<p class='center'>Failed to retrieve books from inventory.</p>";
      return false;
    }
  }
  return true;
}

// FIXME: add Date, and show it in search_orders.php, show_order.php.
// Returns the newly inserted order_id on success; empty string otherwise.
function insertOrder($connection, $books) {
  $customer_id = $_COOKIE['customer_id'];
  $shipping_method = $_POST['shipping_method'];
  $card_number = $_POST['card_number'];

  $query = "INSERT INTO orders" .
    " (customer_id, shipping_method, card_number, order_date) VALUES" .
    " ({$customer_id}, '{$shipping_method}', '{$card_number}', CURDATE());";
  $result = mysql_query($query, $connection);
  if (!$result) {
    echo "<p class='center'>Failed to place the order.</p>";
    return false;
  }
  $order_id = mysql_insert_id($connection);

  $query = "INSERT INTO order_book VALUES ";
  $is_first_value = true;
  foreach ($books as $isbn => $quantity) {
    if ($is_first_value) {
      $is_first_value = false;
    } else {
      $query .= ", ";
    }
    $query .= "({$order_id}, '{$isbn}', {$quantity})";
  }
  $query .= ";";

  $result = mysql_query($query, $connection);
  if (!$result) {
    echo "<p class='center'>Failed to add books into the order.</p>";
    return "";
  }

  return $order_id;
}

function getPaymentMethodFromCardNumber($connection, $card_number) {
  $query = "SELECT card_company" .
      " FROM payments WHERE card_number = {$card_number};";

  $results = mysql_query($query, $connection);
  if (!$results) {
    die("Could not get payment methods." . mysql_error());
  }

  $row = mysql_fetch_assoc($results);

  return composePaymentMethod($row['card_company'], $card_number);
}

function placeOrder() {
  // TODO: Do not die, display some useful links.
  $books = $_COOKIE['books'];
  if ($_SERVER['REQUEST_METHOD'] != 'POST' || empty($books)) {
    echo "<p class='center'>There is no order to place.</p>";
    return;
  }

  $connection = connect();
  if (!updateInventory($connection, $books)) {
    return;
  }
  $order_id = insertOrder($connection, $books);
  if (empty($order_id)) {
    return;
  }

  clearCart();  // NOTE: this has to be called prior to any output.

  $payment_method =
      getPaymentMethodFromCardNumber($connection, $_POST['card_number']);
  $total_price_string = $_POST['total_price_string'];
  echo "
      <p class='center'>Your credit card
      <em><strong>{$payment_method}</strong></em>
      is charged <em><strong>\${$total_price_string}</strong></em>.</p>
      <p class='center'>
      <a href='show_order.php?order_id={$order_id}'>
      <em><strong>Order {$order_id}</strong></em></a>
      is placed successfully.</p>";

  echo "<h2>Order Details</h2>";
  showOrderFromOrderId($connection, $order_id);

  mysql_close($connection);
}

showHeader('Place Order');
placeOrder();
showFooter();

?>
