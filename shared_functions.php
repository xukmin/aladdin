<?php

function requireLogin() {
  if (empty($_COOKIE['username'])) {
    header("Location: /");
    exit();
  }
}

function requireCustomerLogin() {
  if (empty($_COOKIE['customer_id'])) {
    header("Location: /");
    exit();
  }
}

function requireEmployeeLogin() {
  if (empty($_COOKIE['employee_id'])) {
    header("Location: /");
    exit();
  }
}

function logout() {
  setcookie('customer_id', '', time() - 3600);
  setcookie('employee_id', '', time() - 3600);
  setcookie('username', '', time() - 3600);
  setcookie('full_name', '', time() - 3600);
  clearCart();
}

function redirectToHome() {
  header("Location: /");
  exit();
}

function connect() {
  $dbhost = 'localhost';
  $dbuser = 'root';
  $dbpass = '';
  $connection = mysql_connect($dbhost, $dbuser, $dbpass);
  if (!$connection) {
    die('Could not connect to MySQL server: ' . mysql_error());
  }
  mysql_select_db('bookstore');
  return $connection;
}

// Must be called prior to any output.
function clearCart() {
  $books = $_COOKIE['books'];
  foreach ($books as $isbn => $quantity) {
    setcookie("books[$isbn]", "", time() - 3600);
  }
}

function composePaymentMethod($card_company, $card_number) {
  return $card_company . " ending in " .
      substr($card_number, strlen($card_number) - 4);
}

function showEmptyFooter() {
  echo "
      </body>
      <html>";
}

function showHeader($title) {
  $full_name = $_COOKIE['full_name'];
  if (empty($full_name)) {
    $greeting = "<h1>Welcome to Min Xu's Bookstore</h1>";
  } else if (!empty($_COOKIE['customer_id'])) {
    $greeting = "
        <h1>Welcome to Min Xu's Bookstore</h1>
        <h3>Hello, {$full_name}</h3>";
  } else if (!empty($_COOKIE['employee_id'])) {
    $greeting = "
        <h1 style='color:red;'>Welcome to Min Xu's Bookstore</h1>
        <h3 style='color:red;'>Hello, {$full_name}</h3>";
  }

  echo "
      <!DOCTYPE html>
      <html>
      <head>
      <meta charset='utf-8'>
      <link rel='stylesheet' type='text/css' href='bookstore.css'>
      <title>Bookstore</title>
      </head>
      <body>
      {$greeting}
      <h2>{$title}</h2>";
}

function showFooter() {
  if (!empty($_COOKIE['customer_id'])) {
    echo "
        <br>
        <h3>
        Back to <a href='customer_menu.php'>Main Menu for Customers</a>
        </h3>";
  } else if (!empty($_COOKIE['employee_id'])) {
    echo "
        <br>
        <h3 style='color:red;'>
        Back to <a href='manager_menu.php'>Main Menu for Employees</a>
        </h3>";
  }

  echo "
      </body>
      </html>";
}

function showGeneralizedResults($results) {
  if (mysql_num_rows($results) == 0) {
    return;
  }

  $map = array(
    'isbn' => 'ISBN',
    'customer_id' => 'Customer ID',
    'employee_id' => 'Employee ID',
    'order_id' => 'Order ID',
    'supplier_id' => 'Supplier ID'
  );

  echo "<h2>Query Results</h2>";
  echo "<table>";
  $first = true;
  while ($row = mysql_fetch_assoc($results)) {
    if ($first) {
      echo "<tr>";
      $first = false;
      foreach ($row as $key => $value) {
        if ($key == 'password') {
          continue;
        }
        if (!empty($map[$key])) {
          $display_key = $map[$key];
        } else {
          $display_key = ucwords(str_replace('_', ' ', $key));
        } 
        echo "<th>{$display_key}</th>";
      }
      echo "</tr>";
    }
    echo "<tr>";
    foreach ($row as $key => $value) {
      if ($key == 'password') {
        continue;
      } else if ($key == 'image') {
        echo "
          <td>
          <img src='show_image.php?isbn={$row['isbn']}' alt='Image'
               height='240'>
          </td>";
      } else {
        echo "<td>{$value}</td>";
      }
    }
    echo "</tr>";
  }
  echo "</table>";
}

function processManagerQuery() {
  if (empty($_COOKIE['employee_id'])) {
    return;
  }

  if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    return;
  }

  $connection = connect();
  $query = $_POST['query'];
  $results = mysql_query($query);
  if (!$results) {
    echo "<p class='center'>Failed to execute the query: " . mysql_error()
      . "</p>";
    return;
  } else {
    echo "<p class='center'>Query is executed successfully.</p>";
  }
  showGeneralizedResults($results);
  mysql_close($connection);
}
?>

