<?php
require_once 'shared_functions.php';

requireCustomerLogin();

showHeader('Main Menu for Customers'); ?>
<fieldset>
<h2><a href='search_books.php'>Search Books</a></h2>
<h2><a href='search_orders.php'>Search Orders</a></h2>
<h2><a href='show_cart.php'>Show Shopping Cart</a></h2>
<h2><a href='logout.php'>Logout</a></h2>
</fieldset>
<?php showEmptyFooter(); ?>

