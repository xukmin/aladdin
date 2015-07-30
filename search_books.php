<?php

require_once 'shared_functions.php';

requireLogin();

function composeQuery() {
  $query = 'SELECT * FROM books NATURAL JOIN inventory';

  $criteria = array();
  if (!empty($_POST['isbn'])) {
    $criteria[] = "isbn = '${_POST['isbn']}'";
  }
  if (!empty($_POST['title'])) {
    $criteria[] = "title = '${_POST['title']}'";
  }
  if (!empty($_POST['author'])) {
    $criteria[] = "author = '${_POST['author']}'";
  }
  if (!empty($_POST['publisher'])) {
    $criteria[] = "publisher = '${_POST['publisher']}'";
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
    There are no books found. Please revise your criteria and try again.
    </p>";
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
        <th>Inventory</th>
      </tr>";
  while ($row = mysql_fetch_assoc($results)) {
    $price_string = money_format("%i", $row['price']);
    echo "
      <tr>
        <td><a href='show_book.php?isbn={$row['isbn']}'>{$row['isbn']}</a></td>
        <td>{$row['title']}</td>
        <td>{$row['author']}</td>
        <td>{$row['publisher']}</td>
        <td style='text-align:right;'>\${$price_string}</td>
        <td style='text-align:right;'>{$row['quantity']}</td>
      </tr>";
  }
  echo "</table>";
  if ($num_rows == 1) {
    echo "<p class='center'>Found 1 book.</p>";
  } else {
    echo "<p class='center'>Found $num_rows books.</p>";
  }
}

function searchBooks() {
  if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    return;
  }
  $query = composeQuery();
  $connection = connect();
  $results = mysql_query($query, $connection);
  showResults($results);
  mysql_close($connection);
}

showHeader('Search Books');
?>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
  <fieldset>
    <legend>Enter the search criteria below</legend>
    <p class="form">
      <label>ISBN: </label>
      <input type="text" name="isbn" value="<?php echo $_POST['isbn'] ?>">
    </p>
    <p class="form">
      <label>Title: </label>
      <input type="text" name="title" value="<?php echo $_POST['title'] ?>">
    </p>
    <p class="form">
      <label>Author: </label>
      <input type="text" name="author" value="<?php echo $_POST['author'] ?>">
    </p>
    <p class="form">
      <label>Publisher: </label>
      <input type="text" name="publisher"
             value="<?php echo $_POST['publisher'] ?>">
    </p>
  </fieldset>
  <br>
  <input type="submit" class="button" name="submit" value="Search Books">
</form>
<?php
searchBooks();
showFooter();
?>

