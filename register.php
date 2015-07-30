<?php

require_once 'shared_functions.php';

function validateRegistration() {
  if (empty($_POST['username'])) {
    echo "<p class='center'>Please input a username.</p>";
    return false;
  }
  if (empty($_POST['first_name'])) {
    echo "<p class='center'>Please input your first name.</p>";
    return false;
  }
  if (empty($_POST['last_name'])) {
    echo "<p class='center'>Please input your last name.</p>";
    return false;
  }
  if ($_POST['password'] != $_POST['password2']) {
    echo "
        <p class='center'>
        Passwords do not match. Please retype your passwords.
        </p>";
    return false;
  }
  if (empty($_POST['password'])) {
    echo "<p class='center'>Password cannot be empty.</p>";
    return false;
  }
  if (empty($_POST['email'])) {
    echo "<p class='center'>Please input your email address.</p>";
    return false;
  }
  return true;
}

function composeQuery() {
  $query = "INSERT INTO customers" .
    " (username, password, first_name, last_name, email) VALUES" .
    "('{$_POST['username']}', SHA1('{$_POST['password']}')," .
    " '{$_POST['first_name']}', '{$_POST['last_name']}', '{$_POST['email']}');";

  return $query;
}

function showCustomer($connection, $customer_id) {
  echo "<h2>Customer Registraion Information</h2>";

  $query = "SELECT username, first_name, last_name, email" .
      " FROM customers WHERE customer_id = '{$customer_id}';";
  $result = mysql_query($query, $connection);
  if (!$result) {
    die("<p class='center'>Cannot retrieve customer information.</p>");
  }
  $row = mysql_fetch_assoc($result);
  echo "
      <table>
      <tr>
      <th>Username</th>
      <td>{$row['username']}</td>
      </tr>
      <tr>
      <th>First Name</th>
      <td>{$row['first_name']}</td>
      </tr>
      <th>Last Name</th>
      <td>{$row['last_name']}</td>
      </tr>
      <tr>
      <th>Email</th>
      <td>{$row['email']}</td>
      </tr>
      </table>";
}

function register() {
  if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    return false;
  }
  if (!validateRegistration()) {
    return false;
  }
  $query = composeQuery();
  $connection = connect();
  $result = mysql_query($query, $connection);
  if (!$result) {
    echo "<p class='center'>Registration is failed. Please try again.</p>";
    mysql_close($connection);
    return false;
  }
  $customer_id = mysql_insert_id($connection);
  echo "
      <p class='center'>
      Congratulations! You have been registered successfully.
      </p>";
  showCustomer($connection, $customer_id);
  echo "<h3>Go to <a href='/'>Login Page</a></h3>";
  mysql_close($connection);
  return true;
}

function showRegistrationForm() {
  echo "
      <form action='{$_SERVER['PHP_SELF']}' method='post'>
      <fieldset>
      <legend>Enter your registration information below</legend>
      <p class='form'>
      <label>Username: </label>
      <input type='text' name='username' value='{$_POST['username']}'>
      </p>
      <p class='form'>
      <label>Password: </label>
      <input type='password' name='password' value=''>
      </p>
      <p class='form'>
      <label>Retype Password: </label>
      <input type='password' name='password2' value=''>
      </p>
      <p class='form'>
      <label>First Name: </label>
      <input type='text' name='first_name' value='{$_POST['first_name']}'>
      </p>
      <p class='form'>
      <label>Last Name: </label>
      <input type='text' name='last_name' value='{$_POST['last_name']}'>
      </p>
      <p class='form'>
      <label>Email: </label>
      <input type='text' name='email' value='{$_POST['email']}'>
      </p>
      </fieldset>
      <br>
      <input type='submit' class='button' name='submit' value='Register'>
      </form>";
}

showHeader('New Customer Registraion');
if (!register()) {
  showRegistrationForm();
}
showFooter();
?>
