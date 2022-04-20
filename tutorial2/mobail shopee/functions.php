<?php
// require MYSQL connection
require('database/DBController.php');

// require product class
require('database/product.php');

// require cart class
require('database/cart.php');



// DBController object
$db=new DBController();

// product object
$product = new product($db);
$product_shuffle = $product->getData();

// Cart object
$Cart = new
Cart($db );


