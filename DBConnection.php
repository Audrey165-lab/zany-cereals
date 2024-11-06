<?php
if(!is_dir(__DIR__.'./db'))
    mkdir(__DIR__.'./db');
if(!defined('db_file')) define('db_file',__DIR__.'./db/grocery_db.db');
function my_udf_md5($string) {
    return md5($string);
}

Class DBConnection extends SQLite3{
    protected $db;
    function __construct(){
        $this->open(db_file);
        $this->createFunction('md5', 'my_udf_md5');
        $this->exec("PRAGMA foreign_keys = ON;");

        $this->exec("CREATE TABLE IF NOT EXISTS `admin_list` (
            `admin_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
            `fullname` INTEGER NOT NULL,
            `username` TEXT NOT NULL,
            `password` TEXT NOT NULL,
            `status` INTEGER NOT NULL Default 1,
            `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"); 

        $this->exec("CREATE TABLE IF NOT EXISTS `customer_list` (
            `customer_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
            `fullname` INTEGER NOT NULL,
            `username` TEXT NOT NULL,
            `password` TEXT NOT NULL,
            `email` TEXT NOT NULL,
            `contact` TEXT NOT NULL,
            `address` TEXT NOT NULL,
            `status` INTEGER NOT NULL Default 1,
            `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `date_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        $this->exec("CREATE TABLE IF NOT EXISTS `category_list` (
            `category_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
            `name` INTEGER NOT NULL,
            `status` INTEGER NOT NULL Default 1,
            `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        $this->exec("CREATE TABLE IF NOT EXISTS `fees_list` (
            `fee_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
            `location` INTEGER NOT NULL,
            `amount` REAL NOT NULL,
            `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        $this->exec("CREATE TABLE IF NOT EXISTS `product_list` (
            `product_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
            `category_id` INTEGER NOT NULL,
            `name` INTEGER NOT NULL,
            `description` TEXT NOT NULL,
            `price` REAL NOT NULL,
            `status` INTEGER NOT NULL Default 1,
            `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `date_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(`category_id`) REFERENCES `category_list`(`category_id`) ON DELETE CASCADE
        )");
        $this->exec("CREATE TABLE IF NOT EXISTS `inventory_list` (
            `inventory_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
            `product_id` INTEGER NOT NULL,
            `quantity` INTEGER NOT NULL,
            `type` INTEGER NOT NULL Default 1,
            `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(`product_id`) REFERENCES `product_list`(`product_id`) ON DELETE CASCADE
        )");
        $this->exec("CREATE TABLE IF NOT EXISTS `cart_list` (
            `customer_id` INTEGER NOT NULL,
            `product_id` INTEGER NOT NULL,
            `quantity` REAL NOT NULL,
            `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(`customer_id`) REFERENCES `customer_list`(`customer_id`) ON DELETE CASCADE,
            FOREIGN KEY(`product_id`) REFERENCES `product_list`(`product_id`) ON DELETE CASCADE
        )");
        $this->exec("CREATE TABLE IF NOT EXISTS `order_list` (
            `order_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
            `customer_id` INTEGER NOT NULL,
            `transaction_code` INTEGER NOT NULL,
            `fee_id` INTEGER NOT NULL,
            `delivery_address` INTEGER NOT NULL,
            `total_amount` REAL NOT NULL,
            `status` INTEGER NOT NULL Default 0,
            `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(`fee_id`) REFERENCES `fees_list`(`fee_id`) ON DELETE CASCADE,
            FOREIGN KEY(`customer_id`) REFERENCES `customer_list`(`customer_id`) ON DELETE CASCADE
        )");
        $this->exec("CREATE TABLE IF NOT EXISTS `order_items` (
            `order_id` INTEGER NOT NULL,
            `inventory_id` INTEGER NOT NULL,
            `product_id` INTEGER NOT NULL,
            `quantity` REAL NOT NULL,
            `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(`order_id`) REFERENCES `order_list`(`order_id`) ON DELETE CASCADE,
            FOREIGN KEY(`inventory_id`) REFERENCES `inventory_list`(`inventory_id`) ON DELETE CASCADE,
            FOREIGN KEY(`product_id`) REFERENCES `product_list`(`product_id`) ON DELETE CASCADE
        )");

        $this->exec("CREATE TRIGGER IF NOT EXISTS updatedTime_cust AFTER UPDATE on `customer_list`
        BEGIN
            UPDATE `customer_list` SET date_updated = CURRENT_TIMESTAMP where customer_id = customer_id;
        END
        ");
        $this->exec("CREATE TRIGGER IF NOT EXISTS updatedTime_prod AFTER UPDATE on `product_list`
        BEGIN
            UPDATE `product_list` SET date_updated = CURRENT_TIMESTAMP where product_id = product_id;
        END
        ");

        $this->exec("INSERT or IGNORE INTO `admin_list` VALUES (1,'Administrator','admin',md5('admin123'),1, CURRENT_TIMESTAMP)");

    }
    function __destruct(){
         $this->close();
    }
}

$conn = new DBConnection();