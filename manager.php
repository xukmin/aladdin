<?php

require 'shared_functions.php';

function composeQuery() {
  $query = 'SELECT * FROM employees WHERE';
  $query .= " username = '${_POST['username']}'";
  $query .= " AND password = SHA1('${_POST['password']}')";
  $query .= ";";

  return $query;
}

function employeeLogin() {
  $query = composeQuery();
  $connection = connect();
  $results = mysql_query($query, $connection);
  if (!$results) {
    die('Could not get authentication information: ' . mysql_error());
  }
  if (mysql_num_rows($results) != 1) {
    // FIXME
    die("Login failed!");
  }
  $row = mysql_fetch_assoc($results);
  setcookie("customer_id", '', time() - 3600);
  setcookie("employee_id", "{$row['employee_id']}");
  setcookie("username", "{$row['username']}");
  setcookie("full_name", "{$row['first_name']} ${row['last_name']}");
  header("Location: /manager_menu.php" );
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  employeeLogin();
} 
?>

<?php showHeader('Manager Login'); ?>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
  <fieldset>
    <p class="form">
      <label>Username: </label>
      <input type="text" name="username"
             value="<?php echo $_POST['username'] ?>">
    </p>
    <p class="form">
      <label>Password: </label>
      <input type="password" name="password"
             value="<?php echo $_POST['password'] ?>">
    </p>
  </fieldset>
  <br>
  <input type="submit" class="button" name="submit" value="Log in">
</form>
<br>
<?php showFooter(); ?>

