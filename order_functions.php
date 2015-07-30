<?php
// Bookstore in AMP
// 
// Author: Min Xu <xukmin@gmail.com>
//
// This page shows order details given an order_id from HTTP GET request.

require_once 'shared_functions.php';

function getCustomerName($connection, $order_id) {
  $query = "SELECT first_name, last_name FROM orders NATURAL JOIN customers " .
      "WHERE orders.order_id = $order_id;";

  $result = mysql_query($query, $connection);
  if (!$result) {
    die("Could not get customer name of order $order_id.");
  }

  $row = mysql_fetch_assoc($result);

  return $row['first_name'] . " " . $row['last_name'];
}

function calculateSubtotalOfBooks($connection, $order_id) {
  $query = "SELECT SUM(price * quantity) AS price " .
      "FROM order_book NATURAL JOIN books WHERE order_id = $order_id;";

  $result = mysql_query($query, $connection);
  if (!$result) {
    die("Could not calculate total price of the order." . mysql_error());
  }

  $row = mysql_fetch_assoc($result);

  return $row['price'];
}

function getNumBooks($connection, $order_id) {
  $query = "SELECT SUM(quantity) AS quantity FROM order_book " .
      "WHERE order_id = $order_id;";

  $result = mysql_query($query, $connection);
  if (!$result) {
    die("Could not get number of books in the order.");
  }

  $row = mysql_fetch_assoc($result);

  return $row['quantity'];
}

function getOrderRow($connection, $order_id) {
  $query = "SELECT * FROM orders WHERE order_id = $order_id;";

  $result = mysql_query($query, $connection);
  if (!$result) {
    die("Could not get shipping method of the order.");
  }

  $row = mysql_fetch_assoc($result);

  return $row;
}

function calculateShippingFee($shipping_method) {
  if ($shipping_method == "express") {
    return 10;
  } else {
    return 0;
  }
}

function getPaymentMethodFromOrderId($connection, $order_id) {
  $query = "SELECT card_number, card_company" .
      " FROM payments NATURAL JOIN orders WHERE order_id = {$order_id};";

  $results = mysql_query($query, $connection);
  if (!$results) {
    die("Could not get payment methods." . mysql_error());
  }

  $row = mysql_fetch_assoc($results);

  return composePaymentMethod($row['card_company'], $row['card_number']);
}

function showOrderSummary($connection, $order_id) {
  // TODO: Handle the case when the order does not exist.
  $customer_name = getCustomerName($connection, $order_id);
  $num_books = getNumBooks($connection, $order_id);
  $subtotal = calculateSubtotalOfBooks($connection, $order_id);
  $row = getOrderRow($connection, $order_id);
  $shipping_method = $row['shipping_method'];
  $order_date = $row['order_date'];
  $payment_method = getPaymentMethodFromOrderId($connection, $order_id);
  $total = $subtotal;

  // TODO: show shipping fee, subtotal of book and total price.
  echo "
      <table>
      <tr>
      <th class='right'>Customer Name</th>
      <td>{$customer_name}</td>
      </tr>
      <tr>
      <th class='right'>Number of Books</th>
      <td>{$num_books}</td>
      </tr>
      <tr>
      <th class='right'>Total Price</th>
      <td>\${$total}</td>
      </tr>
      <tr>
      <th class='right'>Shipping Method</th>
      <td>{$shipping_method}</td>
      </tr>
      <tr>
      <th class='right'>Payment Method</th>
      <td>{$payment_method}</td>
      </tr>
      <tr>
      <th class='right'>Date</th>
      <td>{$order_date}</td>
      </tr>
      </table>
      <br>";

  return true;
}

function showOrderedBooks($connection, $order_id) {
  $query = "SELECT books.isbn, books.title, books.author, books.publisher, " .
      "books.price, order_book.quantity, " .
      "books.price * order_book.quantity as subtotal " .
      "FROM books inner join order_book on books.isbn = order_book.isbn " .
      "where order_book.order_id = $order_id;";

  $results = mysql_query($query, $connection);
  if (!$results) {
    die('Could not get search result: ' . mysql_error());
  }

  $num_rows = mysql_num_rows($results);
  if ($num_rows == 0) {
    echo "<p class='center'>There are no books found in this order.</p>";
    return;
  }
  echo "
      <table>
      <tr>
      <th>ISBN</th>
      <th>Title</th>
      <th>Author</th>
      <th>Publisher</th>
      <th>Price</th>
      <th>Quantity</th>
      <th>Subtotal</th>
      </tr>";
  while ($row = mysql_fetch_assoc($results)) {
    echo "
        <tr>
        <td><a href='show_book.php?isbn={$row['isbn']}'>{$row['isbn']}</a></td>
        <td class='strong'>{$row['title']}</td>
        <td>{$row['author']}</td>
        <td>{$row['publisher']}</td>
        <td class='right'>\${$row['price']}</td>
        <td class='right'>{$row['quantity']}</td>
        <td class='right'>\${$row['subtotal']}</td>
        </tr>";
  }
  echo "</table>";
}

function showOrderFromOrderId($connection, $order_id) {
  if (empty($order_id)) {
    echo "<p class='center'>No order is specified.</p>";
    return;
  }

  if (!showOrderSummary($connection, $order_id)) {
    return;
  }
  showOrderedBooks($connection, $order_id);
}
?>

