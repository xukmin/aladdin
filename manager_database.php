<?php

require_once 'shared_functions.php';

requireEmployeeLogin();

function showExampleQuery($description, $query) {
  echo "
      <form action='manager_database.php' method='post' id='{$description}'>
      <input type='hidden' name='query' value='{$query}'>
      <a href='#' onclick='submitQuery(\"{$description}\"); return false;'>
      {$description}
      </a>
      </form>";
}

function showExampleQueries() {
  echo "
      <br>
      <fieldset>
      <legend>Example Queries</legend>";
  showExampleQuery(
      "Show Books",
      "SELECT * FROM books JOIN inventory on books.isbn = inventory.isbn;");
  showExampleQuery("Show Customers", "SELECT * FROM customers;");
  showExampleQuery("Show Employees", "SELECT * FROM employees;");
  showExampleQuery("Show Orders", "SELECT * FROM orders;");
  showExampleQuery(
      "Show Customer Addresses",
      "SELECT customer_id, street_number, street_name, city, state, zip_code " .
      "FROM customer_address INNER JOIN address USING " .  
      "(street_number, street_name, city, state);");
  showExampleQuery("Show Employee Addresses",
      "SELECT employee_id, street_number, street_name, city, state, zip_code " .
      "FROM employee_address INNER JOIN address USING " . 
      "(street_number, street_name, city, state);");
  showExampleQuery("Show Customer Reviews", "SELECT * FROM comments;");
  showExampleQuery("Show Suppliers", "SELECT * FROM suppliers;");
  echo "</fieldset>";
}

showHeader('Direct Access to Database');
?>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
  <fieldset>
    <legend>Enter the query below</legend>
    <p class="form">
      <label>Query: </label>
      <textarea name="query" style='vertical-align: middle;'
                cols='50' rows='2'><?php echo $_POST['query'] ?></textarea>
    </p>
  </fieldset>
  <br>
  <input type="submit" class="button" name="submit" value="Submit">
  <br>
</form>
<br>
<script>
function submitQuery(name) {
  document.getElementById(name).submit();
}
</script>
<?php
processManagerQuery();
showExampleQueries();
showFooter();
?>

