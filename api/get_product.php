<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$host = 'localhost';
$dbname = 'fashion_store';
$username = 'root';
$password = '';

$id = $_GET['id'] ?? 1;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
   
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'products'")->fetch();
    if (!$tableCheck) {
        echo json_encode(getSampleProduct($id));
        exit;
    }
    

    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p 
                           LEFT JOIN categories c ON p.category_id = c.id 
                           WHERE p.id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch();
    
    if (!$product) {
        echo json_encode(getSampleProduct($id));
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM products 
                           WHERE category_id = :category_id AND id != :id 
                           ORDER BY RAND() LIMIT 4");
    $stmt->bindParam(':category_id', $product['category_id']);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $relatedProducts = $stmt->fetchAll();
    
    $response = [
        'product' => $product,
        'relatedProducts' => $relatedProducts ?: getSampleRelatedProducts()
    ];
    
    echo json_encode($response);
    
} catch(PDOException $e) {
    echo json_encode(getSampleProduct($id));
}

function getSampleProduct($id) {
    $products = [
        1 => [
            'id' => 1,
            'name' => 'Elegancka sukienka wieczorowa',
            'description' => 'Elegancka sukienka z wysokogatunkowej bawełny z wyrafinowanym krojem. Idealna na specjalne okazje.',
            'price' => 499.00,
            'old_price' => 650.00,
            'category_name' => 'Odzież damska',
            'image' => 'assets/images/products/product1.jpg',
            'size' => 'S,M,L',
            'color' => 'czarny,beżowy,niebieski',
            'created_at' => date('Y-m-d H:i:s')
        ],
        2 => [
            'id' => 2,
            'name' => 'Męska koszula bawełniana',
            'description' => 'Klasyczna koszula męska wykonana z 100% bawełny. Perfekcyjna na spotkania biznesowe i casualowe stylizacje.',
            'price' => 299.00,
            'old_price' => null,
            'category_name' => 'Odzież męska',
            'image' => 'assets/images/products/product2.jpg',
            'size' => 'M,L,XL',
            'color' => 'niebieski,biały,szary',
            'created_at' => date('Y-m-d H:i:s')
        ]
    ];
    
    return [
        'product' => $products[$id] ?? $products[1],
        'relatedProducts' => getSampleRelatedProducts()
    ];
}

function getSampleRelatedProducts() {
    return [
        [
            'id' => 3,
            'name' => 'Buty sportowe premium',
            'price' => 399.00,
            'old_price' => 550.00,
            'image' => 'assets/images/products/product3.jpg'
        ],
        [
            'id' => 4,
            'name' => 'Torebka skórzana',
            'price' => 249.00,
            'image' => 'assets/images/products/product4.jpg'
        ],
        [
            'id' => 5,
            'name' => 'Kurtka jeansowa',
            'price' => 599.00,
            'image' => 'assets/images/products/product5.jpg'
        ],
        [
            'id' => 6,
            'name' => 'Spódnica midi',
            'price' => 199.00,
            'old_price' => 350.00,
            'image' => 'assets/images/products/product6.jpg'
        ]
    ];
}
?>