<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'config.php';

$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    // Sanitize inputs
    $customer_id = trim($_POST['customer_id']);
    $branch_id = trim($_POST['branch_id']);
    $customer_name = trim($_POST['customer_name']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validate inputs
    if (empty($customer_id) || empty($branch_id) || empty($customer_name) || 
        empty($username) || empty($password) || empty($confirm_password)) {
        $error_message = "❌ All fields are required!";
    } elseif ($password !== $confirm_password) {
        $error_message = "❌ Passwords do not match!";
    } elseif (strlen($password) < 8) {
        $error_message = "❌ Password must be at least 8 characters!";
    } else {
        try {
            // Check if customer exists in database
            $stmt = $conn->prepare("SELECT * FROM customer WHERE customer_id = ? AND branch_id = ?");
            $stmt->execute([$customer_id, $branch_id]);
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$customer) {
                $error_message = "❌ Invalid Customer ID or Branch ID!";
            } else {
                // Check if username is available
                $stmt = $conn->prepare("SELECT * FROM customer WHERE username = ?");
                $stmt->execute([$username]);
                
                if ($stmt->fetch()) {
                    $error_message = "❌ Username already taken!";
                } else {
                    // Hash password before storing
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $updateStmt = $conn->prepare("UPDATE customer SET customer_name = ?, username = ?, password = ? WHERE customer_id = ?");
                    
                    if ($updateStmt->execute([$customer_name, $username, $hashed_password, $customer_id])) {
                        $_SESSION['success_message'] = "✅ Registration successful! Please login.";
                        header("Location: login.php");
                        exit;
                    } else {
                        $error_message = "⚠️ Registration failed. Please try again.";
                    }
                }
            }
        } catch (PDOException $e) {
            $error_message = "⚠️ Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bankly - New Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@300;400;600&display=swap">
    <style>
        :root {
            --primary-color: #4a6fa5;
            --secondary-color: #3a5a8a;
            --accent-gold: #d4af37;
            --light-bg: #f8faff;
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: var(--light-bg);
            background-image: url('istockphoto-1301055567-612x612__1_-removebg-preview.png');
            background-size: 40%;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            padding: 20px;
            margin: 0;
        }
        
        .register-container {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 15px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 10px 30px rgba(74, 111, 165, 0.15);
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .register-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }
        
        .register-header::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--accent-gold);
        }
        
        .app-name {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }
        
        .app-name img {
            height: 50px;
            margin-right: 15px;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
        }
        
        .app-name span {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            font-weight: 700;
            color: white;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .register-subtitle {
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem;
            letter-spacing: 2px;
            color: var(--accent-gold);
            margin-bottom: 5px;
        }
        
        .register-body {
            padding: 30px;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--secondary-color);
            margin-bottom: 8px;
        }
        
        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--accent-gold);
            box-shadow: 0 0 0 0.25rem rgba(212, 175, 55, 0.25);
        }
        
        .btn-register {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 14px;
            font-weight: 600;
            color: white;
            width: 100%;
            border-radius: 8px;
            transition: all 0.3s;
            margin-top: 10px;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(74, 111, 165, 0.3);
        }
        
        .password-strength {
            height: 5px;
            background: #eee;
            margin-top: 8px;
            border-radius: 3px;
            overflow: hidden;
        }
        
        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: all 0.3s;
            border-radius: 3px;
        }
        
        .alert {
            border-radius: 8px;
        }
        
        .login-link {
            color: var(--primary-color);
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .login-link:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }
        
        @media (max-width: 576px) {
            body {
                background-size: 70%;
                padding: 15px;
            }
            
            .register-container {
                max-width: 100%;
            }
            
            .register-body {
                padding: 25px;
            }
            
            .app-name img {
                height: 40px;
            }
            
            .app-name span {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <div class="app-name">
                <img src="istockphoto-1301055567-612x612__1_-removebg-preview.png" alt="Bankly">
                <span>Bankly</span>
            </div>
            <div class="register-subtitle">Account Registration</div>
        </div>
        
        <div class="register-body">
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger mb-4">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" id="registerForm">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="customer_id" class="form-label">Customer ID</label>
                        <input type="text" class="form-control" id="customer_id" name="customer_id" required
                               value="<?php echo isset($_POST['customer_id']) ? htmlspecialchars($_POST['customer_id']) : ''; ?>">
                    </div>
                    
                    <div class="col-md-6">
                        <label for="branch_id" class="form-label">Branch ID</label>
                        <input type="text" class="form-control" id="branch_id" name="branch_id" required
                               value="<?php echo isset($_POST['branch_id']) ? htmlspecialchars($_POST['branch_id']) : ''; ?>">
                    </div>
                    
                    <div class="col-12">
                        <label for="customer_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="customer_name" name="customer_name" required
                               value="<?php echo isset($_POST['customer_name']) ? htmlspecialchars($_POST['customer_name']) : ''; ?>">
                    </div>
                    
                    <div class="col-12">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    </div>
                    
                    <div class="col-md-6">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required minlength="8">
                        <div class="password-strength">
                            <div id="password-strength-bar" class="password-strength-bar"></div>
                        </div>
                        <small class="text-muted">Minimum 8 characters</small>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        <div id="password-match" class="small mt-1"></div>
                    </div>
                    
                    <div class="col-12 mt-2">
                        <button type="submit" class="btn btn-register" name="register">
                            <i class="bi bi-person-plus-fill me-2"></i> Register Account
                        </button>
                    </div>
                    
                    <div class="col-12 text-center mt-3">
                        <a href="login.php" class="login-link">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Already have an account? Login
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const passwordMatch = document.getElementById('password-match');
            const strengthBar = document.getElementById('password-strength-bar');
            const form = document.getElementById('registerForm');
            
            // Password strength indicator
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                
                // Length check
                if (password.length >= 8) strength += 1;
                if (password.length >= 12) strength += 1;
                
                // Complexity checks
                if (/[a-z]/.test(password)) strength += 1; // Lowercase
                if (/[A-Z]/.test(password)) strength += 1; // Uppercase
                if (/\d/.test(password)) strength += 1;     // Numbers
                if (/[^a-zA-Z0-9]/.test(password)) strength += 1; // Special chars
                
                // Calculate width and color
                const width = Math.min(100, (strength / 5) * 100);
                strengthBar.style.width = width + '%';
                
                // Set color based on strength
                if (strength <= 2) {
                    strengthBar.style.backgroundColor = '#ff4444'; // Red
                } else if (strength <= 4) {
                    strengthBar.style.backgroundColor = '#ffbb33'; // Yellow
                } else {
                    strengthBar.style.backgroundColor = '#00C851'; // Green
                }
            });
            
            // Password match checker
            confirmPasswordInput.addEventListener('input', function() {
                if (passwordInput.value !== this.value) {
                    passwordMatch.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle-fill me-1"></i>Passwords do not match</span>';
                } else if (passwordInput.value.length > 0) {
                    passwordMatch.innerHTML = '<span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>Passwords match</span>';
                } else {
                    passwordMatch.innerHTML = '';
                }
            });
            
            // Form validation
            form.addEventListener('submit', function(event) {
                let isValid = true;
                
                // Check password match
                if (passwordInput.value !== confirmPasswordInput.value) {
                    passwordMatch.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle-fill me-1"></i>Passwords must match</span>';
                    isValid = false;
                }
                
                // Check password length
                if (passwordInput.value.length < 8) {
                    alert('Password must be at least 8 characters long');
                    isValid = false;
                }
                
                if (!isValid) {
                    event.preventDefault();
                }
            });
        });
    </script>
</body>
</html>