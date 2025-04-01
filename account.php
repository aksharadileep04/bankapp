<?php
require 'config.php';

if (!isset($_GET['customer_id'])) {
    die("Customer ID is required.");
}

$customer_id = $_GET['customer_id'];

$sql = "SELECT account.*, branch.branch_name 
        FROM account 
        JOIN branch ON account.branch_id = branch.branch_id
        WHERE account.customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$customer_id]);
$account = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1976d2;
            --secondary-color: #64b5f6;
            --accent-color: #e3f2fd;
            --light-bg: #f5fbff;
        }
        
        body {
            background: linear-gradient(rgba(245, 251, 255, 0.95), rgba(245, 251, 255, 0.97)),
                        url('images-removebg-preview.png');
            background-size: 40%;
            background-position: right 80px bottom 40px;
            background-repeat: no-repeat;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .account-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(25, 118, 210, 0.1);
            transition: all 0.3s ease;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .account-icon {
            width: 60px;
            height: 60px;
            background-color: var(--accent-color);
            color: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .balance-display {
            font-size: 1.8rem;
            font-weight: bold;
            color: #2ecc71;
        }

        .detail-item {
            border-left: 3px solid var(--secondary-color);
            padding-left: 1rem;
            margin: 1.2rem 0;
        }

        .help-section {
            background-color: var(--light-bg);
            border-radius: 15px;
            padding: 1.5rem;
            margin-top: 1rem;
        }

        @media (max-width: 768px) {
            body {
                background-size: 70%;
                background-position: right 40px bottom 40px;
            }
        }
    </style>
</head>
<body class="bg-light">

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="text-primary mb-0"><i class="fas fa-university me-2"></i>Account Overview</h2>
                <span class="info-badge bg-primary text-white px-3 py-1 rounded-pill">
                    Customer ID: <?= htmlspecialchars($customer_id) ?>
                </span>
            </div>

            <div class="account-card">
                <?php if (!$account): ?>
                    <div class="alert alert-warning text-center">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        No account found for this user
                    </div>
                <?php else: ?>
                    <div class="d-flex align-items-center mb-3">
                        <div class="account-icon">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <h4 class="mb-0 ms-3">Account #<?= htmlspecialchars($account['account_id'] ?? 'N/A') ?></h4>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <h6 class="text-muted mb-2"><i class="fas fa-university me-2"></i>Branch</h6>
                                <h5 class="text-dark"><?= htmlspecialchars($account['branch_name'] ?? 'N/A') ?></h5>
                            </div>
                            
                            <div class="detail-item">
                                <h6 class="text-muted mb-2"><i class="fas fa-calendar me-2"></i>Opening Date</h6>
                                <h5 class="text-dark">
                                    <?php 
                                    if (isset($account['opening_date']) && !empty($account['opening_date'])) {
                                        echo date('d M Y', strtotime($account['opening_date']));
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </h5>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="detail-item">
                                <h6 class="text-muted mb-2"><i class="fas fa-wallet me-2"></i>Balance</h6>
                                <div class="balance-display">₹<?= isset($account['balance']) ? number_format($account['balance'], 2) : '0.00' ?></div>
                            </div>
                            
                            <div class="detail-item">
                                <h6 class="text-muted mb-2"><i class="fas fa-credit-card me-2"></i>Account Type</h6>
                                <h5 class="text-dark"><?= htmlspecialchars($account['account_type'] ?? 'N/A') ?></h5>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="help-section">
                <h4 class="text-primary mb-3"><i class="fas fa-question-circle me-2"></i>Need Help?</h4>
                <p>For account-related queries and assistance, please contact our customer care:</p>
                <div class="alert alert-info">
                    <i class="fas fa-phone-alt me-2"></i>
                    <strong>Account Helpline:</strong> +91 9778011968
                </div>
                <p class="mb-0">Available 24/7 for your convenience</p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>