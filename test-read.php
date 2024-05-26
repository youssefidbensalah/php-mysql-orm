<?php

require_once 'Database.php';
require_once 'ORM.php';
require_once 'User.php';
require_once 'Product.php';

$db = new Database();

$userORM = new ORM($db, 'User');
$productORM = new ORM($db, 'Product');

// Find objects
$user = $userORM->find(22);
echo $user->name . " (" . $user->email . ") - " . $user->role . PHP_EOL;
echo "<br>";

$product = $productORM->find(22);
echo $product->name . " (" . $product->price . ") - " . $product->description . PHP_EOL;
echo "<br>";

// Find All Objects
echo "--------------------- Showing Users -------------------"."<br>";
$users = $userORM->findAll();
foreach($users as $item) {
    echo "User id : " . $item->id ." - ";
    echo "User name : " . $item->name ." - ";
    echo "User email : " . $item->email ." - ";
    echo "User role : " . $item->role ."<br>";
}

echo "--------------------- Showing Products -------------------"."<br>";
$products = $productORM->findAll();
foreach($products as $item){
    echo "Product id : " . $item->id ." - ";
    echo "Product name : " . $item->name ." - ";
    echo "Product price : " . $item->price ." - ";
    echo "Product description : " . $item->description ."<br>";
}