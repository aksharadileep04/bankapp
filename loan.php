<?php
require 'config.php';
if (!isset($_GET['customer_id'])) {
    die("Customer ID is required.");
}

$customer_id = $_GET['customer_id'];

$sql = "SELECT loan.*, employee.employee_name FROM loan 
        JOIN employee ON loan.sanctioning_employee_id = employee.employee_id
        WHERE loan.customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$customer_id]);
$loans = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Details</title>
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
                        url('illustration-graphic-cartoon-character-of-loan-vector-removebg-preview.png');
            background-size: 40%;
            background-position: right 80px bottom 40px;
            background-repeat: no-repeat;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .loan-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(25, 118, 210, 0.1);
            transition: all 0.3s ease;
            margin-bottom: 2rem;
            padding: 2rem;
        }

        .loan-icon {
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

        .amount-display {
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
            margin-top: 2rem;
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
                <h2 class="text-primary mb-0"><i class="fas fa-hand-holding-usd me-2"></i>Loan Portfolio</h2>
                <span class="info-badge bg-primary text-white px-3 py-1 rounded-pill">
                    Customer ID: <?= htmlspecialchars($customer_id) ?>
                </span>
            </div>

            <?php if (!$loans): ?>
                <div class="loan-card text-center">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        No active loans found
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($loans as $loan): ?>
                    <div class="loan-card">
                        <div class="d-flex align-items-center mb-3">
                            <div class="loan-icon">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>
                            <h4 class="mb-0 ms-3">Loan ID: <?= $loan['loan_id'] ?></h4>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="detail-item">
                                    <h6 class="text-muted mb-2"><i class="fas fa-tags me-2"></i>Loan Type</h6>
                                    <h5 class="text-dark"><?= $loan['loan_type'] ?></h5>
                                </div>
                                
                                <?php if (isset($loan['sanction_date'])): ?>
                                <div class="detail-item">
                                    <h6 class="text-muted mb-2"><i class="fas fa-calendar me-2"></i>Sanction Date</h6>
                                    <h5 class="text-dark"><?= date('d M Y', strtotime($loan['sanction_date'])) ?></h5>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="detail-item">
                                    <h6 class="text-muted mb-2"><i class="fas fa-rupee-sign me-2"></i>Loan Amount</h6>
                                    <div class="amount-display">₹<?= number_format($loan['loan_amount'], 2) ?></div>
                                </div>
                                
                                <div class="detail-item">
                                    <h6 class="text-muted mb-2"><i class="fas fa-user-tie me-2"></i>Sanctioned By</h6>
                                    <h5 class="text-dark"><?= $loan['employee_name'] ?></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="help-section">
                <h4 class="text-primary mb-3"><i class="fas fa-question-circle me-2"></i>Need Help?</h4>
                <p>For loan-related queries and assistance, please contact our loan department:</p>
                <div class="alert alert-info">
                    <i class="fas fa-phone-alt me-2"></i>
                    <strong>Loan Helpline:</strong> +91 9188835621
                </div>
                <p class="mb-0">Available Monday to Saturday, 9:00 AM to 6:00 PM</p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>