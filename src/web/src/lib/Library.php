<?php

class Library
{
    public $taxRate = 0.1;

    public $navbarName = [
        "not" => [],
        "root" => ["products", "sales", "purchases", "users"],
        "admin" => ["products", "sales", "purchases", "users"],
        "salesWorker" => ["products", "sales"],
        "purchaseWorker" => ["products", "purchases"]
    ];

    public $routes = [
        '/' => ['controller' => 'login', 'action' => 'index'],
        '/login' => ['controller' => 'login', 'action' => 'index'],
        '/check' => ['controller' => 'login', 'action' => 'check'],
        '/menu' => ['controller' => 'menu', 'action' => 'products'],
        '/menu/products' => ['controller' => 'menu', 'action' => 'products'],
        '/menu/purchases' => ['controller' => 'menu', 'action' => 'purchases'],
        '/menu/sales' => ['controller' => 'menu', 'action' => 'sales'],
        '/menu/users' => ['controller' => 'menu', 'action' => 'users'],
        '/category' => ['controller' => 'category', 'action' => 'index'],
        '/category/increase' => ['controller' => 'category', 'action' => 'increase'],
        '/category/editing' => ['controller' => 'category', 'action' => 'editing'],
        '/customer' => ['controller' => 'customer', 'action' => 'index'],
        '/customer/increase' => ['controller' => 'customer', 'action' => 'increase'],
        '/customer/editing' => ['controller' => 'customer', 'action' => 'editing'],
        '/product' => ['controller' => 'product', 'action' => 'index'],
        '/product/increase' => ['controller' => 'product', 'action' => 'increase'],
        '/product/editing' => ['controller' => 'product', 'action' => 'editing'],
        '/purchaseContract' => ['controller' => 'purchaseContract', 'action' => 'index'],
        '/purchaseContract/increase' => ['controller' => 'purchaseContract', 'action' => 'increase'],
        '/purchaseContract/increaseTable' => ['controller' => 'purchaseContract', 'action' => 'increaseTable'],
        '/purchaseContract/editing' => ['controller' => 'purchaseContract', 'action' => 'editing'],
        '/purchaseContract/editingTable' => ['controller' => 'purchaseContract', 'action' => 'editingTable'],
        '/salesContract' => ['controller' => 'salesContract', 'action' => 'index'],
        '/salesContract/increase' => ['controller' => 'salesContract', 'action' => 'increase'],
        '/salesContract/increaseTable' => ['controller' => 'salesContract', 'action' => 'increaseTable'],
        '/salesContract/editing' => ['controller' => 'salesContract', 'action' => 'editing'],
        '/salesContract/editingTable' => ['controller' => 'salesContract', 'action' => 'editingTable'],
        '/supplier' => ['controller' => 'supplier', 'action' => 'index'],
        '/supplier/increase' => ['controller' => 'supplier', 'action' => 'increase'],
        '/supplier/editing' => ['controller' => 'supplier', 'action' => 'editing'],
        '/user' => ['controller' => 'user', 'action' => 'index'],
        '/user/increase' => ['controller' => 'user', 'action' => 'increase'],
        '/user/editing' => ['controller' => 'user', 'action' => 'editing'],
    ];
}
