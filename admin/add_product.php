<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$host = 'localhost';
$dbname = 'fashion_store';
$username = 'root';
$password = '';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? '';
    $old_price = $_POST['old_price'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $size = $_POST['size'] ?? '';
    $color = $_POST['color'] ?? '';
    
    
    if (empty($name) || empty($price) || empty($category_id)) {
        $error = 'Wypełnij wymagane pola: nazwa, cena, kategoria';
    } else {
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            
            $tableCheck = $pdo->query("SHOW TABLES LIKE 'products'")->fetch();
            if (!$tableCheck) {
                $pdo->exec("CREATE TABLE IF NOT EXISTS products (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(200) NOT NULL,
                    description TEXT,
                    price DECIMAL(10,2) NOT NULL,
                    old_price DECIMAL(10,2),
                    category_id INT,
                    size VARCHAR(50),
                    color VARCHAR(100),
                    image VARCHAR(500),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )");
            }
            
            $tableCheck = $pdo->query("SHOW TABLES LIKE 'categories'")->fetch();
            if (!$tableCheck) {
                $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL
                )");
                
                $pdo->exec("INSERT IGNORE INTO categories (name) VALUES 
                    ('Odzież damska'),
                    ('Odzież męska'),
                    ('Obuwie'),
                    ('Akcesoria')");
            }
            
            $image_path = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../assets/images/products/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $filename = uniqid() . '_' . basename($_FILES['image']['name']);
                $target_file = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image_path = 'assets/images/products/' . $filename;
                }
            }
            
            $stmt = $pdo->prepare("INSERT INTO products (name, description, price, old_price, category_id, size, color, image) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $name,
                $description,
                floatval($price),
                !empty($old_price) ? floatval($old_price) : null,
                intval($category_id),
                $size,
                $color,
                $image_path
            ]);
            
            $message = 'Produkt został dodany pomyślnie!';
            
        } catch(PDOException $e) {
            $error = 'Błąd bazy danych: ' . $e->getMessage();
        }
    }
}

$categories = [];
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'categories'")->fetch();
    if ($tableCheck) {
        $stmt = $pdo->query("SELECT * FROM categories");
        $categories = $stmt->fetchAll();
    }
} catch(PDOException $e) {
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dodaj produkt - Panel administracyjny</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-form-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px 0;
        }
        
        .form-card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }
        
        .form-title {
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #000;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #000;
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .btn-submit {
            background: #000;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 4px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
            display: block;
            width: 100%;
            margin-top: 20px;
        }
        
        .btn-submit:hover {
            background: #333;
        }
        
        .message {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #666;
            text-decoration: none;
        }
        
        .back-link:hover {
            color: #000;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <div class="container">
                <nav class="admin-nav">
                    <h1><i class="fas fa-plus-circle"></i> Dodaj produkt</h1>
                    <div>
                        <a href="panel.php"><i class="fas fa-arrow-left"></i> Powrót do panelu</a>
                    </div>
                </nav>
            </div>
        </header>
        
        <div class="container admin-form-container">
            <?php if ($message): ?>
                <div class="message success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <div class="form-card">
                <h2 class="form-title">Nowy produkt</h2>
                
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name" class="form-label">Nazwa produktu *</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description" class="form-label">Opis</label>
                        <textarea id="description" name="description" class="form-control"></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="price" class="form-label">Cena (zł) *</label>
                            <input type="number" id="price" name="price" class="form-control" step="0.01" min="0" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="old_price" class="form-label">Stara cena (zł)</label>
                            <input type="number" id="old_price" name="old_price" class="form-control" step="0.01" min="0">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="category_id" class="form-label">Kategoria *</label>
                            <select id="category_id" name="category_id" class="form-control" required>
                                <option value="">Wybierz kategorię</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                                <?php if (empty($categories)): ?>
                                    <option value="1">Odzież damska</option>
                                    <option value="2">Odzież męska</option>
                                    <option value="3">Obuwie</option>
                                    <option value="4">Akcesoria</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="image" class="form-label">Zdjęcie produktu</label>
                            <input type="file" id="image" name="image" class="form-control" accept="image/*">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="size" class="form-label">Rozmiary (oddzielone przecinkiem)</label>
                            <input type="text" id="size" name="size" class="form-control" placeholder="np. S,M,L,XL">
                        </div>
                        
                        <div class="form-group">
                            <label for="color" class="form-label">Kolory (oddzielone przecinkiem)</label>
                            <input type="text" id="color" name="color" class="form-control" placeholder="np. czarny,czerwony,niebieski">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Zapisz produkt
                    </button>
                </form>
                
                <a href="panel.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> Powrót do panelu
                </a>
            </div>
        </div>
    </div>
</body>
</html>