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

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch(PDOException $e) {
    $pdo = null;
}


$products_count = 0;
$orders_count = 0;
$total_revenue = 0;

if ($pdo) {
    try {
        
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
        $result = $stmt->fetch();
        $products_count = $result['count'] ?? 0;
        
        
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
        $result = $stmt->fetch();
        $orders_count = $result['count'] ?? 0;
        
        
        $stmt = $pdo->query("SELECT SUM(total_price) as total FROM orders");
        $result = $stmt->fetch();
        $total_revenue = $result['total'] ?? 0;
    } catch(PDOException $e) {
       
    }
}


$recent_orders = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5");
        $recent_orders = $stmt->fetchAll();
    } catch(PDOException $e) {
     
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel administracyjny - Fashion Store</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-container {
            min-height: 100vh;
            background: #f5f5f5;
        }
        
        .admin-header {
            background: #000;
            color: white;
            padding: 20px 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .admin-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .admin-nav h1 {
            margin: 0;
            font-size: 24px;
        }
        
        .admin-nav a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
        }
        
        .admin-content {
            padding: 30px 0;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .stat-card i {
            font-size: 40px;
            margin-bottom: 15px;
            color: #000;
        }
        
        .stat-card h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .stat-card .value {
            font-size: 32px;
            font-weight: bold;
            color: #000;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 40px;
        }
        
        .action-btn {
            background: white;
            border: 2px solid #000;
            color: #000;
            padding: 20px;
            text-align: center;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }
        
        .action-btn:hover {
            background: #000;
            color: white;
        }
        
        .action-btn i {
            font-size: 30px;
        }
        
        .recent-orders {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .orders-table th,
        .orders-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .orders-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        .status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-processing {
            background: #cce5ff;
            color: #004085;
        }
        
        .status-shipped {
            background: #d4edda;
            color: #155724;
        }
        
        .logout-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .logout-btn:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <div class="container">
                <nav class="admin-nav">
                    <h1><i class="fas fa-cog"></i> Panel administracyjny</h1>
                    <div>
                        <span>Witaj, <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Administrator'); ?></span>
                        <form method="POST" action="logout.php" style="display: inline;">
                            <button type="submit" class="logout-btn">Wyloguj</button>
                        </form>
                    </div>
                </nav>
            </div>
        </header>
        
        <div class="admin-content container">
           
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-tshirt"></i>
                    <h3>Produkty</h3>
                    <div class="value"><?php echo $products_count; ?></div>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Zamówienia</h3>
                    <div class="value"><?php echo $orders_count; ?></div>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-chart-line"></i>
                    <h3>Przychód</h3>
                    <div class="value"><?php echo number_format($total_revenue, 2, ',', ' '); ?> zł</div>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <h3>Administratorzy</h3>
                    <div class="value">1</div>
                </div>
            </div>
            
          
            <div class="quick-actions">
                <a href="add_product.php" class="action-btn">
                    <i class="fas fa-plus-circle"></i>
                    Dodaj produkt
                </a>
                
                <a href="products.php" class="action-btn">
                    <i class="fas fa-boxes"></i>
                    Zarządzaj produktami
                </a>
                
                <a href="orders.php" class="action-btn">
                    <i class="fas fa-clipboard-list"></i>
                    Zarządzaj zamówieniami
                </a>
                
                <a href="categories.php" class="action-btn">
                    <i class="fas fa-tags"></i>
                    Zarządzaj kategoriami
                </a>
            </div>
            
        
            <div class="recent-orders">
                <h2><i class="fas fa-history"></i> Ostatnie zamówienia</h2>
                
                <?php if (empty($recent_orders)): ?>
                    <p>Brak zamówień</p>
                <?php else: ?>
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Klient</th>
                                <th>Data</th>
                                <th>Wartość</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td>#<?php echo htmlspecialchars($order['id']); ?></td>
                                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                    <td><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></td>
                                    <td><?php echo number_format($order['total_price'], 2, ',', ' '); ?> zł</td>
                                    <td>
                                        <span class="status status-pending">Oczekujące</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>