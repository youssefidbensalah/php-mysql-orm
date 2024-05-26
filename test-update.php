<?php

require_once 'Database.php';
require_once 'ORM.php';
require_once 'User.php';
require_once 'Product.php';

$db = new Database();

$userORM = new ORM($db, 'User');
$productORM = new ORM($db, 'Product');

echo "-------------------- Before Updating -----------------------"."<br>";

// Find objects
$user = $userORM->find(22);
echo $user->name . " (" . $user->email . ") - " . $user->role . PHP_EOL;
echo "<br>";

$product = $productORM->find(22);
echo $product->name . " (" . $product->price . ") - " . $product->description . PHP_EOL;
echo "<br>";

// Update objects
echo "---------------------  Updating...  -------------------"."<br>";

$user->email = 'jose-saleh@example.com';

$userORM->update($user);

$product->description = 'Updated Jose  Saleh description';
$productORM->update($product);
echo "<br>";

echo "---------------------  Updating...  -------------------"."<br>";
// Find objects
$user = $userORM->find(22);
echo $user->name . " (" . $user->email . ") - " . $user->role . PHP_EOL;
echo "<br>";

$product = $productORM->find(22);
echo $product->name . " (" . $product->price . ") - " . $product->description . PHP_EOL;
echo "<br>";