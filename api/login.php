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
        echo json_encode([
            'success' => true,
            'message' => 'Logowanie udane (tryb demo)',
            'demo_mode' => true,
            'user' => [
                'id' => 1,
                'email' => $data['email'],
                'first_name' => 'Demo',
                'last_name' => 'User'
            ]
        ]);
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute([':email' => $data['email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'Nieprawidłowy email lub hasło']);
        exit;
    }
    
    if (!password_verify($data['password'], $user['password_hash'])) {
        echo json_encode(['success' => false, 'error' => 'Nieprawidłowy email lub hasło']);
        exit;
    }
    
    unset($user['password_hash']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Logowanie udane',
        'user' => $user
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Błąd serwera: ' . $e->getMessage()]);
}
?>