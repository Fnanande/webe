<?php

require './DBController.php';
require './cart.php';
require './vendor/autoload.php';

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] == 'GET'){
    $stmt = "select * from products;";
    $result = $conn->query($stmt);
    if($result->num_rows){
        $array = array();
        while($row = $result->fetch_assoc()){
            array_push($array, new Item($row['id'], $row['name'], $row['price'], $row['image']));
        }
        echo json_encode($array);
    }
    else echo "Something went wrong. Try again later!!!"; 
    exit();
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $itemList = json_decode(trim(file_get_contents('php://input')));
        $Subtotal = 0;
        foreach ($itemList as $item) {
            $id = $conn->real_escape_string(($item->id));
            $quantity = $conn->real_escape_string(($item->quantity));

            $result = $conn->query('select price from shoping cart where id =' . $id . ';');
            if ($result->num_rows) {
                $row = $result->fetch_assoc();
                $Subtotal += $row['price'] * intval($quantity);
            } else {
                echo json_encode(['status' => 'no such product found']);
                exit();
            }
            header("Location: " . $_SERVER['PHP_SELF']);
        }
        $stripe = new \Stripe\StripeClient('sk_test_51KgqLGARCavAM8yDMaTetKMRq3t1dzok1gVZYAGoH6oRXeG0azy5FgjlTO9E7f2IsZJPMKMYyXjJ733W2ez2i3aY00QB2kwTcv');
        $session = $stripe->checkout->sessions->create([
            'success_url' => 'http://localhost:8080/frontend/index.html?status=success',
            'cancel_url' => 'http://localhost:8080/frontend/index.html?status=failure',
            'payment_method_types' => ['card'],
            'mode' => 'payment',
            'line_items' => [
                [
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => 'gbp',
                        'unit_amount' => $Subtotal,
                        'product_data' => [
                            'name' => 'Grocery Store',
                            'description' => 'Your Invoice for Gracery today.'
                        ]
                    ]
                ]
            ]
        ]);


        echo json_encode(['id' => $session->id]);


    }
}
}
?>
