<?php

require_once 'Database.php';
require_once 'ORM.php';
require_once 'User.php';
require_once 'Product.php';

$db = new Database();

$userORM = new ORM($db, 'User');
$productORM = new ORM($db, 'Product');

// check for the autoIncrementField
// $userORM->verifyAutoIncrementField();

// Create objects
$user = new User();
$user->name = 'John Doe';
$user->email = 'john@example.com';
$user->role = 'admin';
$userORM->create($user);

$product = new Product();
$product->name = 'Product 1';
$product->price = 10.99;
$product->description = 'This is a description';
$productORM->create($product);

// Find objects
$user = $userORM->find(22);
echo $user->name . " (" . $user->email . ") - " . $user->role . PHP_EOL;
echo "<br>";

$product = $productORM->find(22);
echo $product->name . " (" . $product->price . ") - " . $product->description . PHP_EOL;
echo "<br>";

// Update objects
$user->email = 'newemail@example.com';
$userORM->update($user);

$product->description = 'Updated description';
$productORM->update($product);

// Delete objects
// $userORM->delete(1);
// $productORM->deleteAll();

// Update schema
// $userORM->updateSchema();
// $productORM->updateSchema();
