<?php

// Bookstore in AMP
// Author: Min Xu <xukmin@gmail.com>

require_once 'shared_functions.php';

function getOrdersWithBook($connection, $isbn) {
  $customer_id = $_COOKIE['customer_id'];

  $orders = array();

  $query = "SELECT DISTINCT orders.order_id FROM orders JOIN order_book" .
      " ON orders.order_id = order_book.order_id WHERE" .
      " orders.customer_id = {$customer_id} AND order_book.isbn = '{$isbn}';";

  $results = mysql_query($query, $connection);
  if (!$results) {
    die("query=$query<br>");
    die("Failed to get relevant order_ids. " . mysql_error());
  }

  if (mysql_num_rows($results) == 0) {
    echo "<p class='center'>Sorry, you have to purchase this book before
      adding comments.</p>";
    return $orders;
  }

  while ($row = mysql_fetch_assoc($results)) {
    $orders[] = $row['order_id'];
  }

  return $orders;
}

function showBookDetails($connection, $isbn) {
  $query = "SELECT * FROM books NATURAL JOIN inventory WHERE isbn = '{$isbn}';";

  $results = mysql_query($query, $connection);
  if (!$results) {
    die('Could not get search result: ' . mysql_error());
  }

  $num_rows = mysql_num_rows($results);
  if ($num_rows == 0) {
    echo "<p class='center'>Sorry, book with ISBN $isbn is not found. </p>";
    return;
  }

  $row = mysql_fetch_assoc($results);
  $price_string = money_format("%i", $row['price']);
  echo "
      <table>
      <tr>
      <th class='right'>ISBN</th>
      <td>{$row['isbn']}</td>
      <td class='image' rowspan='6'>
      <img src='show_image.php?isbn={$isbn}' alt='Book Cover Image' height='240'>
      </td>
      </tr>
      <tr>
      <th class='right'>Title</th>
      <td class='strong'>{$row['title']}</td>
      </tr>
      <tr>
      <th class='right'>Author</th>
      <td>{$row['author']}</td>
      </tr>
      <tr>
      <th class='right'>Publisher</th>
      <td>{$row['publisher']}</td>
      </tr>
      <tr>
      <th class='right'>Price</th>
      <td>\${$price_string}</td>
      </tr>
      <tr>
      <th class='right'>Quantity</th>
      <td>{$row['quantity']}</td>
      </tr>
      </table>
      <br>";

  $books=$_COOKIE['books'];
  $isbn=$row['isbn'];

  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (insertReview($connection, $isbn)) {
      return;
    }
  }
  showAddReviewForm($connection, $isbn);
}

function showAddReviewForm($connection, $isbn) {
  $orders = getOrdersWithBook($connection, $isbn);
  if (empty($orders)) {
    return;
  }
  echo "
      <form action='add_review.php?isbn={$isbn}' method='post'>
      <input type='hidden' name='isbn' value='{$isbn}'>
      <fieldset>
      <p class='form'>
      <label>Please Input Your Comments:</label>
      <textarea name='comments' style='vertical-align:middle;width:'></textarea>
      </p>
      <p class='form'>
      <label>Your Rating of this Book:</label>
      <input type='number' name='rating' value='5' min='1' max='5' step='1'>
      </p>
      <p class='form'>
      <label>Your Order with this Book:</label><select name='order_id'>";
  foreach ($orders as $order) {
    echo "<option value='{$order}'>{$order}</option>";
  }
  echo "</select>
      </p>
      </fieldset>
      <br>
      <input type='submit' class='button' name='submit' value='Submit Review'>
      </form>
      <br>"; 
}

function insertReview($connection, $isbn) {
  $order_id = $_POST['order_id'];
  $rating = $_POST['rating'];
  $comments = $_POST['comments'];
  if (empty($comments)) {
    echo "<p class='center'>Your comments cannot be empty.</p>";
    return false;
  }

  $query = "INSERT INTO comments" .
    " (order_id, isbn, rating, content) VALUES" .
    " ({$order_id}, '{$isbn}', {$rating}, '{$comments}');";
  $result = mysql_query($query, $connection);
  if (!$result) {
    echo "<p class='center'>Failed to submit review for this book.</p>";
    return false;
  }
  echo "
      <p class='center'>
      Congratulations! Your review for this book is submitted successfully.
      </p>
      <p class='center'>
      Thank you for sharing!
      </p>";
  return true;
}

function addReview() {
  if (empty($_GET['isbn'])) {
    echo "<p class='center'>No book is specified.</p>";
    return;
  }

  $isbn = $_GET['isbn'];
  $connection = connect();
  $topic = showBookDetails($connection, $isbn);
  mysql_close($connection);
}

showHeader('Add Customer Review');
addReview();
showFooter();
?>

