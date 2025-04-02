<?php
require 'config.php';

if (!isset($_GET['customer_id'])) {
    die("Customer ID is required.");
}

$customer_id = $_GET['customer_id'];

// Fetch loan details
$sql = "SELECT loan.*, employee.employee_name FROM loan 
        JOIN employee ON loan.sanction_employee_id = employee.employee_id
        WHERE loan.customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$customer_id]);
$loans = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($_POST['loans'] as $loan_id => $loan_data) {
        $paid = $loan_data['paid'];
        
        // Now we only need to update the paid amount - the trigger will handle indebt
        $updateSql = "UPDATE loan SET paid = ? WHERE loan_id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->execute([$paid, $loan_id]);
    }
    header("Location: loan.php?customer_id=" . $customer_id);
    exit;
}
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
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --accent-color: #e3f2fd;
            --background-color: #f8f9fa;
        }
        body {
            background-color: var(--background-color);
            font-family: 'Poppins', sans-serif;
        }
        .loan-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            transition: transform 0.3s ease-in-out;
        }
        .loan-card:hover {
            transform: translateY(-5px);
        }
        .loan-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--primary-color);
        }
        .amount-display {
            font-size: 1.8rem;
            font-weight: bold;
            color: #28a745;
        }
        .paid-display {
            font-size: 1.8rem;
            font-weight: bold;
            color: #007bff;
        }
        .indebt-display {
            font-size: 1.8rem;
            font-weight: bold;
            color: #dc3545;
        }
        .detail-item {
            background: var(--accent-color);
            padding: 1rem;
            border-radius: 8px;
            margin: 0.5rem 0;
        }
        .help-section {
            background-color: #fff;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            margin-top: 2rem;
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="loan-header">
                <h2 class="text-primary"><i class="fas fa-hand-holding-usd me-2"></i> Loan Portfolio</h2>
                <span class="badge bg-primary px-3 py-2">Customer ID: <?php echo htmlspecialchars($customer_id); ?></span>
            </div>
            
            <form method="POST">
                <?php foreach ($loans as $loan): ?>
                <div class="loan-card mt-4">
                    <h4 class="mb-3">Loan ID: <?php echo htmlspecialchars($loan['loan_id']); ?></h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <h6 class="text-muted mb-1">Loan Type</h6>
                                <h5 class="text-dark"><?php echo htmlspecialchars($loan['loan_type']); ?></h5>
                            </div>
                            <div class="detail-item">
                                <h6 class="text-muted mb-1">Sanction Date</h6>
                                <h5 class="text-dark"><?php echo htmlspecialchars($loan['sanction_date']); ?></h5>
                            </div>
                            <div class="detail-item">
                                <h6 class="text-muted mb-1">Amount Paid</h6>
                                <div class="paid-display">₹<?php echo number_format($loan['paid'], 2); ?></div>
                            </div>
                            <div class="detail-item">
                                <h6 class="text-muted mb-1">Update Amount Paid</h6>
                                <input type="number" step="0.01" name="loans[<?php echo $loan['loan_id']; ?>][paid]" value="<?php echo number_format($loan['paid'], 2); ?>" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-item">
                                <h6 class="text-muted mb-1">Loan Amount</h6>
                                <div class="amount-display">₹<?php echo number_format($loan['loan_amount'], 2); ?></div>
                            </div>
                            <div class="detail-item">
                                <h6 class="text-muted mb-1">Sanctioned By</h6>
                                <h5 class="text-dark"><?php echo htmlspecialchars($loan['employee_name']); ?></h5>
                            </div>
                            <div class="detail-item">
                                <h6 class="text-muted mb-1">Remaining Debt</h6>
                                <div class="indebt-display">₹<?php echo number_format($loan['indebt'], 2); ?></div>
                            </div>
                            <div class="detail-item">
        <h6 class="text-muted mb-1">Remaining Debt</h6>
        <div class="indebt-display">₹<?php echo number_format($loan['indebt'], 2); ?></div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary">Update Loan Details</button>
                </div>
            </form>
            
            <div class="help-section mt-4">
                <h4 class="text-primary mb-3"><i class="fas fa-question-circle me-2"></i> Need Help?</h4>
                <p>For loan-related queries, contact our loan department:</p>
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
