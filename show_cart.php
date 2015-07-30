<?php

// Bookstore in AMP
// Author: Min Xu <xukmin@gmail.com>

require_once 'shared_functions.php';

requireCustomerLogin();

// TODO: Optimize 1 query per book to 1 query per cart.
function showOrderedBook($connection, $isbn, $quantity) {
  $query = "SELECT * FROM books WHERE isbn = {$isbn};";
  $result = mysql_query($query, $connection);
  if (!$result) {
    die("Could not get book with ISBN {$isbn}." . mysql_error());
  }

  $row = mysql_fetch_assoc($result);
  $price_string = money_format("%i", $row['price']);
  $subtotal = $quantity * $row['price'];
  $subtotal_string = money_format("%i", $subtotal);
  echo "
      <tr>
      <input type='hidden' name='isbns[]' value='{$isbn}'>
      <input type='hidden' name='quantities[]' value='{$quantity}'>
      <td><a href='show_book.php?isbn={$row['isbn']}'>{$row['isbn']}</a></td>
      <td class='strong'>{$row['title']}</td>
      <td>{$row['author']}</td>
      <td>{$row['publisher']}</td>
      <td class='right'>\${$price_string}</td>
      <td class='right'>{$quantity}</td>
      <td class='right'>\${$subtotal_string}</td>
      </tr>";
  return $subtotal;
}

function getPaymentMethods($connection, $customer_id) {
  $query = "SELECT card_number, card_company FROM payments WHERE" .
      " customer_id = '{$_COOKIE['customer_id']}';";

  $results = mysql_query($query, $connection);
  if (!$results) {
    die("Could not get payment methods." . mysql_error());
  }

  while ($row = mysql_fetch_assoc($results)) {
    $card_number = "{$row['card_number']}";
    $display_name = $row['card_company'] . " ending in " .
        substr($card_number, strlen($card_number) - 4);
    $payments[] = array(
      'card_number' => $card_number,
      'display_name' => $display_name
    );
  }

  return $payments;
}

function showCart() {
  $books = $_COOKIE['books'];
  if (empty($books)) {
    $books = array();
  }

  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_POST['submit'] == 'Add to Cart') {
      $isbn = $_POST['isbn'];
      $quantity = intval($_POST['quantity']);
      $title = $_POST['title'];

      if ($quantity == 1) {
        $copies = "copy";
        $have = "have";
      } else {
        $copies = "copies";
        $have = "has";
      }
      echo "
          <p class='center'>
          {$quantity} {$copies} of <em><strong>{$title}</strong></em> {$have}
          been added to your shopping cart.
          </p><br>";

      if (array_key_exists($isbn, $books)) {
        $books[$isbn] = strval(intval($books[$isbn]) + $quantity);
      } else {
        $books[$isbn] = strval($quantity);
      }
    } else if ($_POST['submit'] == 'Clear Cart') {
      echo "<p class='center'>Your shopping cart has been cleared.</p>";
      $books = array();
    }
  }

  echo "
      <form action='place_order.php' method='post'>
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

  if (count($books) == 0) {
    echo "
        </table>
        </form>
        <p class='center'>Your shopping cart is empty.</p>";
    return;
  }

  $n = 0;
  $num_books = 0;
  $subtotal = 0;
  $connection = connect();
  foreach ($books as $isbn => $quantity) {
    $n++;
    $num_books += $quantity;
    $subtotal += showOrderedBook($connection, $isbn, $quantity);
  }
  $subtotal_string = money_format("%i", $subtotal);
  echo "</table><br>";

  echo "
      <input type='hidden' name='total_price_string' value='{$subtotal_string}'>
      <table>
      <tr>
      <th class='right'>Number of Books</th>
      <td>{$num_books}</td>
      </tr>
      <tr>
      <th class='right'>Total Price</th>
      <td>\${$subtotal_string}</td>
      </tr>
      <tr>
      <th class='right'>Shipping Method</th>
      <td>
      <select name='shipping_method'>
      <option value='ground'>ground</option>
      <option value='express'>express</option>
      </select>
      </td>
      </tr>";

  // FIXME: Show payment selection or add payment method button.
  echo "
      <tr>
      <th class='right'>Payment Method</th>
      <td>
      <select name='card_number'>";
  $payments = getPaymentMethods($connection, $_COOKIE['customer_id']);
  foreach ($payments as $payment) {
    echo "
        <option value='{$payment['card_number']}'>
        {$payment['display_name']}
        </option>";
  }
  echo "
      </select>
      </td>
      </tr>
      </table>
      <br>
      <input type='submit' class='button' name='submit' value='Place Order'>
      </form>";

  echo "
      <form action='show_cart.php' method='post'>
      <br>
      <p class='center'>
      You may also choose to clear the shopping cart and start over.
      </p>
      <input type='submit' class='button' name='submit' value='Clear Cart'>
      </form>";

  mysql_close($connection);
}

// TODO: Check if there are enough copies left in inventory.
// NOTE: This function has to be called prior to any output.
function addToCart() {
  $isbn=$_POST['isbn'];
  $old_quantity = $_COOKIE["books"][$isbn];
  if (empty($old_quantity)) {
    $old_quantity = 0;
  } else {
    $old_quantity = $old_quantity; //intval($old_quantity);
  }
  $new_quantity = $old_quantity + intval($_POST['quantity']);
  setcookie("books[{$_POST['isbn']}]", "$new_quantity");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if ($_POST['submit'] == 'Add to Cart') {
    addToCart();
  } else if ($_POST['submit'] == 'Clear Cart') {
    clearCart();
  }
}

showHeader('Shopping Cart');
showCart();
showFooter();
?>

