<?php
echo "<h2>Instalacja Fashion Store dla XAMPP</h2>";

$host = 'localhost';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "1. Tworzenie bazy danych...<br>";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS fashion_store CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci");
    $pdo->exec("USE fashion_store");
    echo "✓ Baza danych utworzona<br><br>";
    
    echo "2. Tworzenie tabel...<br>";
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(100) UNIQUE NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✓ Tabela categories utworzona<br>";
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
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
    )");
    echo "✓ Tabela products utworzona<br>";
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
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
    )");
    echo "✓ Tabela orders utworzona<br>";
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT,
        quantity INT NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
    )");
    echo "✓ Tabela order_items utworzona<br>";
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(200) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        name VARCHAR(100),
        role ENUM('admin', 'manager') DEFAULT 'manager',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL
    )");
    echo "✓ Tabela admin_users utworzona<br><br>";
    
    echo "3. Dodawanie danych przykładowych...<br>";
    
    $pdo->exec("INSERT IGNORE INTO categories (name, slug) VALUES
        ('Odzież damska', 'odziez-damska'),
        ('Odzież męska', 'odziez-meska'),
        ('Obuwie', 'obuwie'),
        ('Akcesoria', 'akcesoria')");
    echo "✓ Kategorie dodane<br>";
    
    $products = [
        ['Elegancka sukienka wieczorowa', 'Elegancka sukienka z wysokogatunkowej bawełny.', 499.00, 650.00, 1, 'S,M,L', 'czarny,beżowy,niebieski'],
        ['Męska koszula bawełniana', 'Klasyczna koszula męska wykonana z 100% bawełny.', 299.00, null, 2, 'M,L,XL', 'niebieski,biały,szary'],
        ['Buty sportowe premium', 'Wygodne buty sportowe z oddychającego materiału.', 399.00, 550.00, 3, '40,41,42,43', 'biały,czarny'],
        ['Torebka skórzana', 'Elegancka torebka skórzana z praktycznym wnętrzem.', 249.00, null, 4, null, 'czarny,brązowy'],
        ['Kurtka jeansowa', 'Klasyczna kurtka jeansowa z wygodnym krojem.', 599.00, null, 2, 'M,L,XL', 'niebieski,czarny'],
        ['Spódnica midi', 'Elegancka spódnica midi z elastycznego materiału.', 199.00, 350.00, 1, 'XS,S,M', 'czarny,szary']
    ];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO products (name, description, price, old_price, category_id, size, color) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($products as $product) {
        $stmt->execute($product);
    }
    echo "✓ Przykładowe produkty dodane<br>";
    
    $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO admin_users (email, password_hash, name, role) VALUES (?, ?, ?, 'admin')");
    $stmt->execute(['admin@fashionstore.pl', $hashed_password, 'Administrator']);
    echo "✓ Konto administratora dodane<br><br>";
    
    echo "4. Tworzenie indeksów...<br>";
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_products_category ON products(category_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_orders_status ON orders(status)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_order_items_order ON order_items(order_id)");
    echo "✓ Indeksy utworzone<br><br>";
    
    echo "5. Tworzenie struktury katalogów...<br>";
    $directories = [
        'assets/images/products',
        'assets/images/banners',
        'assets/icons',
        'api',
        'admin'
    ];
    
    foreach ($directories as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
            echo "✓ Katalog $dir utworzony<br>";
        }
    }
    
    echo "<br><h3 style='color: green;'>✅ Instalacja zakończona pomyślnie!</h3>";
    echo "<p>Sklep jest gotowy do użycia.</p>";
    echo "<p><a href='index.html'>Przejdź do sklepu</a> | <a href='admin/login.php'>Panel administracyjny</a></p>";
    echo "<p><strong>Dane logowania do panelu:</strong><br>";
    echo "Email: admin@fashionstore.pl<br>";
    echo "Hasło: admin123</p>";
    
} catch(PDOException $e) {
    echo "<h3 style='color: red;'>❌ Błąd instalacji:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>Sprawdź czy MySQL jest uruchomiony w XAMPP.</p>";
}
?>