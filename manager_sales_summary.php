<?php

require_once 'shared_functions.php';

requireEmployeeLogin();

function composeQuery() {
  $month = $_POST['month'];
  $year = $_POST['year'];
  $query = "SELECT books.isbn, title, order_book.quantity, 
      price*order_book.quantity, orders.order_id, orders.order_date 
      FROM (books NATURAL JOIN order_book) JOIN orders USING (order_id)
      where DATE_FORMAT(orders.order_date, '%m-%Y') =
      DATE_FORMAT(STR_TO_DATE('{$year}-{$month}', '%Y-%m'), '%m-%Y');";

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
    There are no ordered books found for the given month.
    </p>";
    return;
  }
  echo "
    <table>
      <tr>
        <th>ISBN</th>
        <th>Title</th>
        <th>Quantity</th>
        <th>Price</th>
        <th>Order ID</th>
        <th>Date</th>
      </tr>";
  while ($row = mysql_fetch_assoc($results)) {
    $price_string = money_format("%i", $row['price*order_book.quantity']);
    echo "
      <tr>
        <td><a href='show_book.php?isbn={$row['isbn']}'>{$row['isbn']}</a></td>
        <td>{$row['title']}</td>
        <td>{$row['quantity']}</td>
        <td style='text-align:right;'>\${$price_string}</td>
        <td>{$row['order_id']}</td>
        <td>{$row['order_date']}</td>
      </tr>";
  }
  echo "</table>";
  if ($num_rows == 1) {
    echo "<p class='center'>Found 1 book.</p>";
  } else {
    echo "<p class='center'>Found $num_rows books.</p>";
  }
}

function showMonthlySalesSummary() {
  if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    return;
  }

  $connection = connect();

  if (empty($_POST['month'])) {
    echo "<p class='center'>Please input a month.</p>";
    return;
  }
  if (empty($_POST['year'])) {
    echo "<p class='center'>Please input a year.</p>";
    return;
  }
  $query = composeQuery();
  $results = mysql_query($query, $connection);
  showResults($results);

  mysql_close($connection);
}

showHeader('Monthly Sales Summary');
?>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
  <br>
  <fieldset>
    <legend>Enter the month and year below</legend>
    <p class="form">
      <label>Month: </label>
    <input type="text" name="month" value="<?php echo $_POST['month'] ?>">
    </p>
    <p class="form">
      <label>Year: </label>
    <input type="text" name="year" value="<?php echo $_POST['year'] ?>">
    </p>
  </fieldset>
  <br>
  <input type="submit" class="button" name="submit"
         value="Show Monthly Sales Summary">
</form>
<?php
showMonthlySalesSummary();
showFooter();
?>

