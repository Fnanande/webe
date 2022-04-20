<?php

// require MySQL Connection
require ('../database/DBController.php');

// require Product Class
require ('../database/Product.php');

require ('../database/cart.php');

require './../vendor/autoload.php';
// DBController object
$db = new DBController();

// product object
$product = new product($db);

if (isset($_POST['itemid'])){
    $result = $product->getProduct($_POST['itemid']);
    echo json_encode($result);
}


