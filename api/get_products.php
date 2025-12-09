<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');


$host = 'localhost';
$dbname = 'fashion_store';
$username = 'root';
$password = '';  

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch(PDOException $e) {

    echo json_encode(getSampleProducts());
    exit;
}


$category = $_GET['category'] ?? null;
$sort = $_GET['sort'] ?? 'new';


$tableCheck = $pdo->query("SHOW TABLES LIKE 'products'")->fetch();
if (!$tableCheck) {
    echo json_encode(getSampleProducts());
    exit;
}

$sql = "SELECT p.*, c.name as category_name FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE 1=1";

$params = [];

if ($category) {
    $sql .= " AND c.name LIKE :category";
    $params[':category'] = "%$category%";
}

switch($sort) {
    case 'popular':
        $sql .= " ORDER BY p.views DESC";
        break;
    case 'price_asc':
        $sql .= " ORDER BY p.price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY p.price DESC";
        break;
    case 'new':
    default:
        $sql .= " ORDER BY p.created_at DESC";
        break;
}

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    
    if (empty($products)) {
        $products = getSampleProducts();
    }
    
    echo json_encode($products);
} catch(PDOException $e) {
    echo json_encode(getSampleProducts());
}

function getSampleProducts() {
    return [
        [
            'id' => 1,
            'name' => 'Elegancka sukienka wieczorowa',
            'description' => 'Elegancka sukienka z wysokogatunkowej bawełny z wyrafinowanym krojem.',
            'price' => 499.00,
            'old_price' => 650.00,
            'category_name' => 'Odzież damska',
            'image' => 'assets/images/products/product1.jpg',
            'size' => 'S,M,L',
            'color' => 'czarny,beżowy,niebieski',
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'id' => 2,
            'name' => 'Męska koszula bawełniana',
            'description' => 'Klasyczna koszula męska wykonana z 100% bawełny.',
            'price' => 299.00,
            'old_price' => null,
            'category_name' => 'Odzież męska',
            'image' => 'assets/images/products/product2.jpg',
            'size' => 'M,L,XL',
            'color' => 'niebieski,biały,szary',
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'id' => 3,
            'name' => 'Buty sportowe premium',
            'description' => 'Wygodne buty sportowe z oddychającego materiału.',
            'price' => 399.00,
            'old_price' => 550.00,
            'category_name' => 'Obuwie',
            'image' => 'assets/images/products/product3.jpg',
            'size' => '40,41,42,43',
            'color' => 'biały,czarny',
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'id' => 4,
            'name' => 'Torebka skórzana',
            'description' => 'Elegancka torebka skórzana z praktycznym wnętrzem.',
            'price' => 249.00,
            'old_price' => null,
            'category_name' => 'Akcesoria',
            'image' => 'assets/images/products/product4.jpg',
            'size' => null,
            'color' => 'czarny,brązowy',
            'created_at' => date('Y-m-d H:i:s')
        ]
    ];
}
?>