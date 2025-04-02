<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'config.php';

// Check for success or error messages
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

if (isset($_POST['login'])) {
    $employee_id = trim($_POST['employee_id']);
    $employee_name = trim($_POST['employee_name']);
    $branch_id = trim($_POST['branch_id']);

    try {
        $stmt = $conn->prepare("SELECT * FROM employee WHERE employee_id = ? AND employee_name = ? AND branch_id = ?");
        $stmt->execute([$employee_id, $employee_name, $branch_id]);
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($employee) {
            $_SESSION['employee_id'] = $employee['employee_id'];
            $_SESSION['employee_name'] = $employee['employee_name'];
            $_SESSION['branch_id'] = $employee['branch_id'];
            header("Location: empdashboard.php");
            exit;
        } else {
            $error_message = "❌ Invalid credentials or employee not found!";
        }
    } catch (PDOException $e) {
        $error_message = "⚠️ Database error. Please try again later: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bankly - Loan Officer Portal</title>
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
            background: linear-gradient(135deg, #2c3e50, #4a6fa5);
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
        
        .back-link {
            margin-top: 20px;
            text-align: center;
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
            <div class="login-subtitle">Loan Officer Portal</div>
            <div style="font-size: 0.9rem; letter-spacing: 1px;">SECURE EMPLOYEE ACCESS</div>
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
                    <label class="form-label">EMPLOYEE ID</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person-badge-fill"></i></span>
                        <input type="text" class="form-control" name="employee_id" placeholder="Enter your employee ID" required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">EMPLOYEE NAME</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                        <input type="text" class="form-control" name="employee_name" placeholder="Enter your name" required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">BRANCH ID</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-building-fill"></i></span>
                        <input type="text" class="form-control" name="branch_id" placeholder="Enter your branch ID" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-login btn-block w-100" name="login" style="background: linear-gradient(135deg, #2c3e50, #4a6fa5); border: none; padding: 12px; color: white; font-weight: 600;">
                    <i class="bi bi-box-arrow-in-right me-2"></i> SIGN IN
                </button>
                
                <div class="back-link">
                    <a href="welcome.php" style="color: var(--primary-color); text-decoration: none;">
                        <i class="bi bi-arrow-left"></i> Back to Welcome Page
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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