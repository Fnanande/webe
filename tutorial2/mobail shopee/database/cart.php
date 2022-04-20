<?php

class Cart
{

    public $db = null;

    public function __construct(DBController $db)
    {
        if (!isset($db->con)) return null;
        $this->db = $db;
    }

    // insert into cart table
    public function insertIntoCart($params = null, $table = "cart")
    {
        if ($this->db->con != null) {
            if ($params != null) {
                // "Insert into cart(user_id) values (0)"
                // get table columns
                $columns = implode(',', array_keys($params));

                $values = implode(',', array_values($params));

                // create sql query
                $query_string = sprintf("INSERT INTO %s(%s) VALUES(%s)", $table, $columns, $values);

                // execute query
                $result = $this->db->con->query($query_string);
                return $result;
            }
        }
    }


    // to get user_id and item_id and insert into cart table
    public function addToCart($userid, $itemid)
    {
        if (isset($userid) && isset($itemid)) {
            $params = array(
                "user_id" => $userid,
                "item_id" => $itemid
            );

            // insert data into cart
            $result = $this->insertIntoCart($params);
            if ($result) {
                // Reload Page
                header("Location: " . $_SERVER['PHP_SELF']);
            }
        }
    }

    // delete cart item using cart item id
    public function deleteCart($item_id = null, $table = 'cart')
    {
        if ($item_id != null) {
            $result = $this->db->con->query("DELETE FROM {$table} WHERE item_id={$item_id}");
            if ($result) {
                header("Location:" . $_SERVER['PHP_SELF']);
            }
            return $result;
        }
    }

    // calculate sub total
    public function getSum($arr)
    {
        if (isset($arr)) {
            $sum = 0;
            foreach ($arr as $item) {
                $sum += floatval($item[0]);
            }
            return sprintf('%.2f', $sum);
        }
    }

    // get item_it of shopping cart list
    public function getCartId($cartArray = null, $key = "item_id")
    {
        if ($cartArray != null) {
            $cart_id = array_map(function ($value) use ($key) {
                return $value[$key];
            }, $cartArray);
            return $cart_id;
        }
    }

    // Save for later
    public function saveForLater($item_id = null, $saveTable = "wishlist", $fromTable = "cart")
    {
        if ($item_id != null) {
            $query = "INSERT INTO {$saveTable} SELECT * FROM {$fromTable} WHERE item_id={$item_id};";
            $query .= "DELETE FROM {$fromTable} WHERE item_id={$item_id};";

            // execute multiple query
            $result = $this->db->con->multi_query($query);

            if ($result) {
                header("Location :" . $_SERVER['PHP_SELF']);
            }
            return $result;
        }

    }
            
        public function checkout($user_id, $item_id = null, $saveTable = "", $fromTable = "cart")
        {
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










