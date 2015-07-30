<?php

// NOTE:
// 1. PHP require / include statements introduce an extra carriage return (#0a)
//    character at the beginning of the generated file content.
//    To work around this, copied the connect() function here.
// 2. Extra lines / whitespaces between PHP tags introduce extra carriage 
//    return characters in the generated file content.
//    To work around this, avoid any extra lines / whitespaces between PHP tags.
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

function showBookCover() {
  $isbn = $_GET['isbn'];
  if (empty($isbn)) {
    die("No ISBN is specified.");
  }

  $query = "SELECT image FROM books WHERE isbn = '{$isbn}';";

  $connection = connect();
  $result = mysql_query($query, $connection);

  header('Content-type: image/jpeg');
  echo mysql_result($result, 0);
}

showBookCover();
?>
