CREATE DATABASE IF NOT EXISTS footyshirts_db;
USE footyshirts_db;

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    email VARCHAR(120) NOT NULL,
    team VARCHAR(80) NOT NULL,
    shirt_size VARCHAR(10) NOT NULL,
    quantity INT NOT NULL,
    delivery_method VARCHAR(20) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    delivery_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
