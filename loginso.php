<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'config.php';

// Check for registration success message
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    try {
        $stmt = $conn->prepare("SELECT * FROM customer WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['customer_id'];
                $_SESSION['user_name'] = $user['customer_name'];
                header("Location: dashboard.php");
                exit;
            } else {
                $error_message = "❌ Incorrect password!";
            }
        } else {
            $error_message = "❌ User not found!";
        }
    } catch (PDOException $e) {
        $error_message = "⚠️ Database error. Please try again later.";
    }
}

// Forgot password handling
if (isset($_POST['forgot_password'])) {
    $username = trim($_POST['forgot_username']);
    $customer_id = trim($_POST['forgot_customer_id']);
    
    try {
        $stmt = $conn->prepare("SELECT * FROM customer WHERE username = ? AND customer_id = ?");
        $stmt->execute([$username, $customer_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Generate a temporary password
            $temp_password = bin2hex(random_bytes(4));
            $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);
            
            // Update the password in database
            $updateStmt = $conn->prepare("UPDATE customer SET password = ? WHERE customer_id = ?");
            $updateStmt->execute([$hashed_password, $customer_id]);
            
            $_SESSION['success_message'] = "✅ Temporary password generated: <strong>$temp_password</strong>. Please login and change it immediately.";
            header("Location: login.php");
            exit;
        } else {
            $_SESSION['error_message'] = "❌ No account found with those credentials!";
            header("Location: login.php");
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "⚠️ System error. Please try again later.";
        header("Location: login.php");
        exit;
    }
}

// Retrieve error message from session if exists
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bankly - Private Banking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@300;400;600&display=swap">
    <link rel="icon" href="istockphoto-1301055567-612x612__1_-removebg-preview.png" type="image/png">
    <style>
        :root {
            --primary-color: #4a6fa5;
            --secondary-color: #3a5a8a;
            --accent-gold: #d4af37;
            --light-color: #f8faff;
            --dark-color: #2d3748;
        }
        
        body {
            margin: 0;
            padding: 0;
            font-family: 'Montserrat', sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f8faff;
            overflow: hidden;
            position: relative;
        }
        
        .bg-animation {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                url('istockphoto-1301055567-612x612__1_-removebg-preview.png') center/contain no-repeat;
            opacity: 0.15;
            animation: float 30s infinite ease-in-out;
            z-index: -1;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translate(0, 0) scale(1);
            }
            25% {
                transform: translate(-5%, -3%) scale(0.98);
            }
            50% {
                transform: translate(3%, 5%) scale(1.02);
            }
            75% {
                transform: translate(5%, -2%) scale(0.99);
            }
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.92);
            border-radius: 8px;
            width: 100%;
            max-width: 450px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 10px 30px rgba(74, 111, 165, 0.1);
            backdrop-filter: blur(3px);
            transform-style: preserve-3d;
            perspective: 1000px;
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .login-header::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--accent-gold);
            animation: shine 3s infinite;
        }
        
        @keyframes shine {
            0% { opacity: 0.7; }
            50% { opacity: 1; }
            100% { opacity: 0.7; }
        }
        
        .app-name {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }
        
        .app-name img {
            height: 48px;
            margin-right: 15px;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
            transition: transform 0.5s ease;
        }
        
        .app-name:hover img {
            transform: rotate(5deg) scale(1.05);
        }
        
        .app-name span {
            font-family: 'Playfair Display', serif;
            font-size: 2.4rem;
            font-weight: 700;
            letter-spacing: 1px;
            color: white;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .login-subtitle {
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--accent-gold);
            margin-bottom: 5px;
        }
        
        .login-body {
            padding: 35px;
        }
        
        .form-control {
            transition: all 0.3s;
            box-shadow: none !important;
        }
        
        .form-control:focus {
            border-color: var(--accent-gold);
        }
        
        .btn-login {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }
        
        .btn-login::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -60%;
            width: 200%;
            height: 200%;
            background: rgba(255,255,255,0.1);
            transform: rotate(30deg);
            transition: all 0.3s;
        }
        
        .btn-login:hover::after {
            left: 100%;
        }
        
        @media (max-width: 768px) {
            .bg-animation {
                background-size: cover;
                animation: float-mobile 40s infinite ease-in-out;
            }
            
            @keyframes float-mobile {
                0%, 100% {
                    transform: translate(0, 0) scale(1.2);
                }
                25% {
                    transform: translate(-10%, -5%) scale(1.15);
                }
                50% {
                    transform: translate(5%, 10%) scale(1.25);
                }
                75% {
                    transform: translate(10%, -5%) scale(1.18);
                }
            }
        }
    </style>
</head>
<body>
    <div class="bg-animation"></div>
    
    <div class="login-container">
        <div class="login-header">
            <div class="app-name">
                <img src="istockphoto-1301055567-612x612__1_-removebg-preview.png" alt="Bankly">
                <span>Bankly</span>
            </div>
            <div class="login-subtitle">Private Banking</div>
            <div style="font-size: 0.9rem; letter-spacing: 1px;">SECURE CLIENT PORTAL</div>
        </div>
        
        <div class="login-body">
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger mb-4">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success mb-4">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-4">
                    <label class="form-label">USERNAME</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                        <input type="text" class="form-control" name="username" placeholder="Enter your username" required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">PASSWORD</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" class="form-control" name="password" placeholder="Enter your password" required>
                        <button class="btn btn-outline-secondary toggle-password" type="button">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-login btn-block" name="login" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); border: none; padding: 12px; color: white; font-weight: 600;">
                    <i class="bi bi-box-arrow-in-right me-2"></i> SIGN IN
                </button>
                
                <div class="login-links d-flex justify-content-between mt-4">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal" style="color: var(--primary-color); text-decoration: none;">Forgot Password?</a>
                    <a href="register.php" style="color: var(--primary-color); text-decoration: none;">Create Account</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="forgotPasswordModalLabel">Reset Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="forgotPasswordForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="forgotUsername" class="form-label">Username</label>
                            <input type="text" class="form-control" id="forgotUsername" name="forgot_username" required>
                        </div>
                        <div class="mb-3">
                            <label for="forgotCustomerId" class="form-label">Customer ID</label>
                            <input type="text" class="form-control" id="forgotCustomerId" name="forgot_customer_id" required>
                        </div>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle-fill me-2"></i> A temporary password will be generated for you.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="forgot_password" class="btn btn-primary">Reset Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.querySelector('.toggle-password');
            const passwordInput = document.querySelector('input[name="password"]');
            
            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    this.innerHTML = type === 'password' ? 
                        '<i class="bi bi-eye-slash"></i>' : 
                        '<i class="bi bi-eye"></i>';
                });
            }
            
            // Add subtle tilt effect on mouse move
            const loginContainer = document.querySelector('.login-container');
            if (loginContainer) {
                document.addEventListener('mousemove', (e) => {
                    const xAxis = (window.innerWidth / 2 - e.pageX) / 25;
                    const yAxis = (window.innerHeight / 2 - e.pageY) / 25;
                    loginContainer.style.transform = `rotateY(${xAxis}deg) rotateX(${yAxis}deg)`;
                });
                
                // Reset position when mouse leaves
                document.addEventListener('mouseleave', () => {
                    loginContainer.style.transform = 'rotateY(0deg) rotateX(0deg)';
                });
            }
        });
    </script>
</body>
</html>