<?php
session_start();


$valid_username = 'admin';
$valid_password = 'admin123'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username === $valid_username && $password === $valid_password) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header('Location: panel.php');
        exit;
    } else {
        $error = 'Nieprawidłowe dane logowania';
    }
}


if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: panel.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logowanie administratora - Fashion Store</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f5f5f5;
        }
        
        .login-box {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        
        .login-title {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #666;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .btn-login {
            width: 100%;
            padding: 12px;
            background: #000;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn-login:hover {
            background: #333;
        }
        
        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h2 class="login-title">Logowanie administratora</h2>
            
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Nazwa użytkownika</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Hasło</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn-login">Zaloguj się</button>
                
                <a href="../index.html" class="back-link">Powrót do sklepu</a>
            </form>
            
            <div style="margin-top: 30px; padding: 15px; background: #f8f9fa; border-radius: 4px; font-size: 14px; color: #666;">
                <strong>Dane testowe:</strong><br>
                Login: admin<br>
                Hasło: admin123
            </div>
        </div>
    </div>
</body>
</html>