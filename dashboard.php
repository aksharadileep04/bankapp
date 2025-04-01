<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background: linear-gradient(rgba(248, 249, 250, 0.9), rgba(248, 249, 250, 0.9)),
                        url('istockphoto-1659072548-612x612-removebg-preview.png');
            background-size: cover;
            background-position: center;
            animation: backgroundSlide 20s infinite linear;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        @keyframes backgroundSlide {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .dashboard-container {
            max-width: 900px;
            margin: auto;
            text-align: center;
            position: relative;
            z-index: 1;
            padding-top: 40px;
        }

        .welcome-text {
            font-family: 'Great Vibes', cursive;
            font-size: 3.5rem;
            letter-spacing: 2px;
            margin-bottom: 40px !important;
        }

        .card {
            border-radius: 12px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease-in-out;
            background: rgba(255, 255, 255, 0.9);
            margin-top: 30px;
        }

        .icon {
            height: 100px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }

        .bank-message {
            margin: 50px 0 30px;
            font-style: italic;
            color: #6c757d;
            font-size: 1.2rem;
        }

        .contact-info {
            background: rgba(0, 123, 255, 0.1);
            padding: 15px;
            border-radius: 10px;
            margin-top: 40px;
        }
    </style>
</head>
<body>

<div class="container mt-5 dashboard-container">
    <h2 class="welcome-text">Welcome, <span style="color:#007bff;"><?php echo $_SESSION['user_name']; ?></span></h2>

    <div class="row mt-5">  <!-- Increased margin-top -->
        <!-- Account -->
        <div class="col-md-4 mb-3">
            <div class="card text-center p-3">
                <div class="card-body">
                    <img src="images-removebg-preview.png" class="icon" alt="Account">
                    <h5 class="card-title">Account</h5>
                    <a href="account.php?customer_id=<?php echo $_SESSION['user_id']; ?>" class="btn btn-primary">View Account</a>
                </div>
            </div>
        </div>
        
        <!-- Loan -->
        <div class="col-md-4 mb-3">
            <div class="card text-center p-3">
                <div class="card-body">
                    <img src="illustration-graphic-cartoon-character-of-loan-vector-removebg-preview.png" class="icon" alt="Loans">
                    <h5 class="card-title">Loans</h5>
                    <a href="loan.php?customer_id=<?php echo $_SESSION['user_id']; ?>" class="btn btn-primary">View Loans</a>
                </div>
            </div>
        </div>

        <!-- Profile -->
        <div class="col-md-4 mb-3">
            <div class="card text-center p-3">
                <div class="card-body">
                    <img src="mobile-banking-concept-illustration_114360-13928-removebg-preview.png" class="icon" alt="Profile">
                    <h5 class="card-title">Profile</h5>
                    <a href="profile.php?customer_id=<?php echo $_SESSION['user_id']; ?>" class="btn btn-primary">View Profile</a>
                </div>
            </div>
        </div>
    </div>

    <div class="bank-message">
        "Your Trust, Our Priority - Banking Made Beautiful"
    </div>

    <div class="contact-info">
        Need help? Contact us:<br>
        📞 <a href="tel:9188835621">+91 88835 621</a> | 
        📱 <a href="tel:9778011968">+91 77801 1968</a>
    </div>

    <a href="logout.php" class="btn btn-danger mt-4">Logout</a>
</div>

</body>
</html>