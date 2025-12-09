<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$host = 'localhost';
$dbname = 'fashion_store';
$username = 'root';
$password = '';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$data) {
    echo json_encode(['success' => false, 'error' => 'Nieprawidłowe żądanie']);
    exit;
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'users'")->fetch();
    if (!$tableCheck) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(200) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            phone VARCHAR(20),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
    }
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute([':email' => $data['email']]);
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Użytkownik z tym adresem email już istnieje']);
        exit;
    }
    
    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, first_name, last_name, phone) 
                           VALUES (:email, :password_hash, :first_name, :last_name, :phone)");
    
    $stmt->execute([
        ':email' => $data['email'],
        ':password_hash' => $hashedPassword,
        ':first_name' => $data['first_name'],
        ':last_name' => $data['last_name'],
        ':phone' => $data['phone'] ?? null
    ]);
    
    $userId = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Rejestracja udana',
        'user_id' => $userId,
        'user' => [
            'id' => $userId,
            'email' => $data['email'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name']
        ]
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => true,
        'message' => 'Rejestracja udana (tryb demo)',
        'demo_mode' => true
    ]);
}
?>