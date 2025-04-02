<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Bankly</title>
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
        
        .welcome-container {
            background: rgba(255, 255, 255, 0.92);
            border-radius: 8px;
            width: 100%;
            max-width: 800px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 10px 30px rgba(74, 111, 165, 0.1);
            backdrop-filter: blur(3px);
            transform-style: preserve-3d;
            perspective: 1000px;
        }
        
        .welcome-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .welcome-header::before {
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
        
        .welcome-subtitle {
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--accent-gold);
            margin-bottom: 5px;
        }
        
        .welcome-body {
            padding: 35px;
        }
        
        .role-cards {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }
        
        .role-card {
            flex: 1;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            height: 280px;
        }
        
        .role-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }
        
        .role-card-bg {
            height: 140px;
            background-position: center;
            background-size: cover;
            position: relative;
        }
        
        .role-card-customer-bg {
            background-image: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)), url('images-removebg-preview.png');
        }
        
        .role-card-employee-bg {
            background-image: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)), url('illustration-graphic-cartoon-character-of-loan-vector-removebg-preview.png');
        }
        
        .role-card-content {
            padding: 20px;
            text-align: center;
        }
        
        .role-card h3 {
            margin-bottom: 15px;
            color: var(--dark-color);
            font-family: 'Playfair Display', serif;
        }
        
        .role-card p {
            color: #666;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        
        .btn-role {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
            text-decoration: none;
        }
        
        .btn-role:hover {
            transform: scale(1.05);
            color: white;
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
        }
        
        .welcome-message {
            text-align: center;
            margin-bottom: 30px;
            color: var(--dark-color);
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
            
            .role-cards {
                flex-direction: column;
            }
            
            .role-card {
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="bg-animation"></div>
    
    <div class="welcome-container">
        <div class="welcome-header">
            <div class="app-name">
                <img src="istockphoto-1301055567-612x612__1_-removebg-preview.png" alt="Bankly">
                <span>Bankly</span>
            </div>
            <div class="welcome-subtitle">Loan Management System</div>
            <div style="font-size: 0.9rem; letter-spacing: 1px;">YOUR TRUSTED BANKING PARTNER</div>
        </div>
        
        <div class="welcome-body">
            <div class="welcome-message">
                <h2>Welcome to Our Banking Platform</h2>
                <p>Please select your role to continue to the appropriate portal</p>
            </div>
            
            <div class="role-cards">
                <div class="role-card">
                    <div class="role-card-bg role-card-customer-bg"></div>
                    <div class="role-card-content">
                        <h3>Customer</h3>
                        <p>Access your accounts, view loans, and manage your profile</p>
                        <a href="login.php" class="btn-role">
                            <i class="bi bi-person-fill me-2"></i>Customer Login
                        </a>
                    </div>
                </div>
                
                <div class="role-card">
                    <div class="role-card-bg role-card-employee-bg"></div>
                    <div class="role-card-content">
                        <h3>Loan Officer</h3>
                        <p>Access customer data and manage loan sanctions</p>
                        <a href="loginso.php" class="btn-role">
                            <i class="bi bi-briefcase-fill me-2"></i>Officer Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add subtle tilt effect on mouse move
            const welcomeContainer = document.querySelector('.welcome-container');
            if (welcomeContainer) {
                document.addEventListener('mousemove', (e) => {
                    const xAxis = (window.innerWidth / 2 - e.pageX) / 40;
                    const yAxis = (window.innerHeight / 2 - e.pageY) / 40;
                    welcomeContainer.style.transform = `rotateY(${xAxis}deg) rotateX(${yAxis}deg)`;
                });
                
                // Reset position when mouse leaves
                document.addEventListener('mouseleave', () => {
                    welcomeContainer.style.transform = 'rotateY(0deg) rotateX(0deg)';
                });
            }
        });
    </script>
</body>
</html>