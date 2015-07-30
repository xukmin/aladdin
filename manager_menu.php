<?php
require_once 'shared_functions.php';

requireEmployeeLogin();

showHeader('Main Menu for Managers'); ?>
<fieldset>
<h2><a href='manager_database.php'>Direct Access to Database</a></h2>
<h2><a href='manager_sales_summary.php'>Show Monthly Sales Summary</a></h2>
<h2><a href='search_books.php'>Search Books</a></h2>
<h2><a href='search_orders.php'>Search Orders</a></h2>
<h2><a href='logout.php'>Logout</a></h2>
</fieldset>
<?php showEmptyFooter(); ?>

