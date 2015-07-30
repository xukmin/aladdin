<?php

// Bookstore in AMP
// Author: Min Xu <xukmin@gmail.com>

require_once 'shared_functions.php';

function getShippingMethod($connection, $order_id) {
  $query = "SELECT shipping_method FROM orders WHERE order_id = $order_id;";

  $result = mysql_query($query, $connection);
  if (!$result) {
    die("Could not get shipping method of the order.");
  }

  $row = mysql_fetch_assoc($result);

  return $row['shipping_method'];
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

  if (empty($_COOKIE['customer_id'])) {
    return $row['topic'];
  }

  $books=$_COOKIE['books'];
  $isbn=$row['isbn'];
  $quantity=$books[$isbn];
  $more = "";
  if (!empty($quantity)) {
    $more = "More";
    if (intval($quantity) == 1) {
      $copies = "copy"; 
      $are = "is";
    } else {
      $copies = "copies";
      $are = "are";
    }
    echo "
        <p class='center'>
        {$books[$isbn]} {$copies} of <em><strong>{$row['title']}</strong></em>
        $are already in your shopping cart.</p>";
  }

  echo "
      <form action='show_cart.php' method='post'>
      <input type='hidden' name='isbn' value='{$isbn}'>
      <input type='hidden' name='title' value='{$row['title']}'>
      <fieldset>
      <p class='form'>
      <label>{$more} Copies to Buy:</label>
      <input type='number' name='quantity' value='1'
      min='1' max='{$row['quantity']}' step='1' style='text-align:right;'>
      </p>
      </fieldset>
      <br>
      <input type='submit' class='button' name='submit' value='Add to Cart'>
      </form>
      <br>"; 

  return $row['topic'];
}

function showReview($connection, $isbn) {
  if (!empty($_COOKIE['customer_id'])) { 
    echo "
        <h3>
        <a href='add_review.php?isbn={$isbn}'>Add Customer Review</a>
        </h3><br>";
  }
  echo "<h2 style='color:black;'>Customer Reviews</h2>";
  $query = "SELECT customers.first_name, customers.last_name, content, rating 
        FROM (comments NATURAL JOIN orders) JOIN customers USING (customer_id) 
        WHERE isbn = '{$isbn}';";
  $results = mysql_query($query, $connection);
  if (!$results) {
    die('Could not get search result: ' . mysql_error());
  }

  $num_rows = mysql_num_rows($results);
  if ($num_rows == 0) {
    echo "<p class='center'>Sorry, book with ISBN $isbn is not found. </p>";
    return;
  }
  echo "
      <table>
      <tr>
      <th>Customer</th>
      <th>Rating</th>
      <th>Comments</th>
      </tr>";
  while ($row = mysql_fetch_assoc($results)) {
    //FIXME : order_id
    echo "
      <tr>
      <td>{$row['first_name']} {$row['last_name']}</td>
      <td>{$row['rating']}</td>
      <td>{$row['content']}</td>
      </tr>";  
  }
  echo "</table>";
}

function showRelatedBooks($connection, $isbn, $topic) {
  if (empty($_COOKIE['customer_id'])) {
    return;
  }

  echo "<br><h2 style='color:black;'>Suggested Related Books for You</h2>";

  $query = "SELECT * FROM books WHERE topic = {$topic} AND isbn != {$isbn};";

  $results = mysql_query($query, $connection);
  if (!$results) {
    return;
  }

  echo "
      <table>
      <tr>
      <th>ISBN</th>
      <th>Title</th>
      </tr>";
  while ($row = mysql_fetch_assoc($results)) {
    echo "
        <tr>
        <td><a href='show_book.php?isbn={$row['isbn']}'>{$row['isbn']}</a></td>
        <td>{$row['title']}</td>
        </tr>";
  }
  echo "</table>";
}

// FIXME: show image
function showBook() {
  if (empty($_GET['isbn'])) {
    echo "<p class='center'>No book is specified.</p>";
    return;
  }

  $isbn = $_GET['isbn'];
  $connection = connect();
  $topic = showBookDetails($connection, $isbn);
  showReview($connection, $isbn);
  showRelatedBooks($connection, $isbn, $topic);
  mysql_close($connection);
}

showHeader('Book Details');
showBook();
showFooter();
?>

