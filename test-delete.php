<?php

require_once 'Database.php';
require_once 'ORM.php';
require_once 'User.php';
require_once 'Product.php';

$db = new Database();

$userORM = new ORM($db, 'User');
$productORM = new ORM($db, 'Product');

// Find objects

try {

    // Trying to find a user with ID 27
    $product = $productORM->find(27);
    echo $product->name . " (" . $product->price . ") - " . $product->description . PHP_EOL;
    echo "<br>";

    // Trying to find a user with ID 27
    $user = $userORM->find(27);
    echo $user->name . " (" . $user->email . ") - " . $user->role . PHP_EOL;
    echo "<br>";

    

} catch (NotFoundException $e) {
    echo $e->getMessage();
}



// if($user)
//     echo $user->name . " (" . $user->email . ") - " . $user->role . PHP_EOL;
// echo "<br>";

// $product = $productORM->find(28);
// if($product)
//     echo $product->name . " (" . $product->price . ") - " . $product->description . PHP_EOL;
// echo "<br>";

// Delete Objects

$result = $userORM->delete(24);
echo $result . "<br>";
$result = $productORM->delete(26);
echo $result . "<br>";
