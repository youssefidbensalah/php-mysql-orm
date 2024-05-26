<?php

require_once 'Database.php';
require_once 'ORM.php';
require_once 'User.php';
require_once 'Product.php';

$db = new Database();

$userORM = new ORM($db, 'User');
$productORM = new ORM($db, 'Product');

// Create Objects 
$user = new User();
$user->name = 'Last User';
$user->email = 'last-user@example.com';
$user->role = 'user';
$userORM->create($user);

$product = new Product();
$product->name = 'Last Product';
$product->price = 10.99;
$product->description = 'This is the lasy products description';
$productORM->create($product);

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