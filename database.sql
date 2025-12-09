
CREATE DATABASE IF NOT EXISTS fashion_store;
USE fashion_store;

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    old_price DECIMAL(10, 2),
    size VARCHAR(20),
    color VARCHAR(50),
    category_id INT,
    image VARCHAR(500),
    images JSON,
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(200) NOT NULL,
    email VARCHAR(200) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(100),
    zip_code VARCHAR(10),
    country VARCHAR(100) DEFAULT 'Polska',
    shipping_method VARCHAR(50),
    payment_method VARCHAR(50),
    notes TEXT,
    total_price DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(200) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(100),
    role ENUM('admin', 'manager') DEFAULT 'manager',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

INSERT INTO categories (name, slug) VALUES
('Odzież damska', 'odziez-damska'),
('Odzież męska', 'odziez-meska'),
('Obuwie', 'obuwie'),
('Akcesoria', 'akcesoria');

INSERT INTO products (name, description, price, old_price, category_id, image, size, color) VALUES
('Elegancka sukienka wieczorowa', 'Elegancka sukienka z wysokogatunkowej bawełny z wyrafinowanym krojem. Idealna na specjalne okazje.', 499.00, 650.00, 1, 'assets/images/products/product1.jpg', 'S,M,L', 'czarny,beżowy,niebieski'),
('Męska koszula bawełniana', 'Klasyczna koszula męska wykonana z 100% bawełny. Perfekcyjna na spotkania biznesowe i casualowe stylizacje.', 299.00, NULL, 2, 'assets/images/products/product2.jpg', 'M,L,XL', 'niebieski,biały,szary'),
('Buty sportowe premium', 'Wygodne buty sportowe z oddychającego materiału. Idealne do aktywności fizycznej i codziennego noszenia.', 399.00, 550.00, 3, 'assets/images/products/product3.jpg', '40,41,42,43', 'biały,czarny'),
('Torebka skórzana', 'Elegancka torebka skórzana z praktycznym wnętrzem. Perfekcyjny dodatek do każdej stylizacji.', 249.00, NULL, 4, 'assets/images/products/product4.jpg', NULL, 'czarny,brązowy'),
('Kurtka jeansowa', 'Klasyczna kurtka jeansowa z wygodnym krojem. Uniwersalny element garderoby na każdą porę roku.', 599.00, NULL, 2, 'assets/images/products/product5.jpg', 'M,L,XL', 'niebieski,czarny'),
('Spódnica midi', 'Elegancka spódnica midi z elastycznego materiału. Idealna do biura i wieczornych wyjść.', 199.00, 350.00, 1, 'assets/images/products/product6.jpg', 'XS,S,M', 'czarny,szary');

INSERT INTO admin_users (email, password_hash, name, role) VALUES
('admin@fashionstore.pl', '$2y$10$YourHashedPasswordHere', 'Administrator', 'admin');

CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_order_items_order ON order_items(order_id);