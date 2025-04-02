<?php
session_start();
if (!isset($_SESSION['employee_id'])) {
    header("Location: loginso.php");
    exit();
}

require 'config.php';

// Get branch information
try {
    $stmt = $conn->prepare("SELECT * FROM branch WHERE branch_id = ?");
    $stmt->execute([$_SESSION['branch_id']]);
    $branch = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $branch_error = "Error fetching branch data: " . $e->getMessage();
}

// Process loan status update
if (isset($_POST['update_loan'])) {
    $loan_id = $_POST['loan_id'];
    $new_status = $_POST['new_status'];
    $payment_frequency = $_POST['payment_frequency'];
    $end_date = $_POST['end_date'];
    
    try {
        $stmt = $conn->prepare("UPDATE loan SET payment_frequency = ?, end_date = ? WHERE loan_id = ?");
        $result = $stmt->execute([$payment_frequency, $end_date, $loan_id]);
        
        if ($result) {
            $success_message = "✅ Loan #$loan_id updated successfully!";
        } else {
            $error_message = "❌ Failed to update loan!";
        }
    } catch(PDOException $e) {
        $error_message = "⚠️ Database error: " . $e->getMessage();
    }
}

// Process new loan approval
if (isset($_POST['approve_loan'])) {
    $customer_id = $_POST['customer_id'];
    $loan_amount = $_POST['loan_amount'];
    $loan_type = $_POST['loan_type'];
    $employee_id = $_SESSION['employee_id'];
    $branch_id = $_SESSION['branch_id'];
    $payment_frequency = $_POST['payment_frequency'];
    $borrow_date = date('Y-m-d'); // Today's date
    $end_date = $_POST['end_date'];
    
    try {
        $stmt = $conn->prepare("INSERT INTO loan (customer_id, sanction_employee_id, loan_amount, loan_type, payment_frequency, borrow_date, end_date, branch_id, sanction_date) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $result = $stmt->execute([$customer_id, $employee_id, $loan_amount, $loan_type, $payment_frequency, $borrow_date, $end_date, $branch_id, $borrow_date]);
        
        if ($result) {
            $success_message = "✅ Loan approved successfully!";
        } else {
            $error_message = "❌ Failed to approve loan!";
        }
    } catch(PDOException $e) {
        $error_message = "⚠️ Database error: " . $e->getMessage();
    }
}

// Get all customers for dropdown
try {
    $stmt = $conn->prepare("SELECT customer_id, customer_name FROM customer ORDER BY customer_name");
    $stmt->execute();
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $customer_error = "Error fetching customers: " . $e->getMessage();
    $customers = [];
}

// Fetch all loans with related data
try {
    $stmt = $conn->prepare("
        SELECT l.*, 
               c.customer_name, 
               c.email AS customer_email, 
               c.phone AS customer_phone,
               e.employee_name AS sanctioned_by, 
               b.branch_name
        FROM loan l
        LEFT JOIN customer c ON l.customer_id = c.customer_id
        LEFT JOIN loan_sanctioning_employee e ON l.sanction_employee_id = e.employee_id
        LEFT JOIN branch b ON l.branch_id = b.branch_id
        ORDER BY l.loan_id DESC
    ");
    $stmt->execute();
    $loans = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $loans_error = "Error fetching loans data: " . $e->getMessage();
    $loans = [];
}

// Calculate loan statistics
$total_loans = count($loans);
$total_amount = 0;
$total_paid = 0;
$total_outstanding = 0;

foreach ($loans as $loan) {
    $total_amount += $loan['loan_amount'];
    $total_paid += isset($loan['paid']) ? $loan['paid'] : 0;
    $total_outstanding += isset($loan['indebt']) ? $loan['indebt'] : 
                          (isset($loan['loan_amount']) ? $loan['loan_amount'] : 0) - 
                          (isset($loan['paid']) ? $loan['paid'] : 0);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Applications - Bankly</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@300;400;600&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
        :root {
            --primary-color: #4a6fa5;
            --secondary-color: #3a5a8a;
            --accent-gold: #d4af37;
            --light-color: #f8faff;
            --dark-color: #2d3748;
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(rgba(248, 249, 250, 0.9), rgba(248, 249, 250, 0.9)),
                        url('istockphoto-1659072548-612x612-removebg-preview.png');
            background-size: cover;
            background-position: center;
            min-height: 100vh;
        }
        
        .sidebar {
            background: linear-gradient(135deg, #2c3e50, #4a6fa5);
            color: white;
            padding: 20px 0;
            height: 100vh;
            position: fixed;
            box-shadow: 3px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-header {
            text-align: center;
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        
        .sidebar-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }
        
        .sidebar-logo img {
            height: 40px;
            margin-right: 10px;
        }
        
        .sidebar-logo span {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            color: white;
        }
        
        .sidebar-title {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--accent-gold);
        }
        
        .nav-link {
            color: rgba(255,255,255,0.8);
            border-radius: 0;
            padding: 12px 20px;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover, .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .nav-link i {
            margin-right: 10px;
            font-size: 1.1rem;
        }

        .main-content {
            margin-left: 250px;
            padding: 30px;
        }

        .dashboard-header {
            margin-bottom: 30px;
        }

        .card {
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
            margin-bottom: 24px;
            border-radius: 12px;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            padding: 15px 20px;
            border-bottom: none;
        }

        .card-body {
            padding: 20px;
        }

        .stats-card {
            text-align: center;
            padding: 24px;
        }

        .stats-card i {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .stats-card h2 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--dark-color);
        }

        .stats-card p {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0;
        }

        .loan-form label {
            font-weight: 500;
            margin-bottom: 8px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 10px 20px;
            font-weight: 500;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .table {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .table thead th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
            border: none;
        }

        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .user-info {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            margin-top: auto;
        }

        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .user-name {
            font-weight: 600;
            margin-bottom: 0;
            font-size: 0.9rem;
        }

        .user-role {
            color: rgba(255,255,255,0.7);
            font-size: 0.8rem;
            margin-bottom: 0;
        }
        
        .status-active {
            background-color: #28a745;
        }
        
        .status-pending {
            background-color: #ffc107;
        }
        
        .status-closed {
            background-color: #6c757d;
        }
        
        .filter-section {
            background-color: rgba(255,255,255,0.7);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .bg-info-light {
            background-color: #e3f2fd;
        }
        
        .tab-content {
            padding-top: 20px;
        }
        
        /* Pagination styles */
        .pagination {
            justify-content: center;
            margin-top: 20px;
        }
        
        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .pagination .page-link {
            color: var(--primary-color);
        }
        
        /* Progress bar styling */
        .progress {
            height: 8px;
            margin-bottom: 5px;
        }
        
        .progress-bar {
            background-color: var(--primary-color);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="sidebar-header">
                    <div class="sidebar-logo">
                        <img src="istockphoto-1301055567-612x612__1_-removebg-preview.png" alt="Bankly Logo">
                        <span>Bankly</span>
                    </div>
                    <p class="sidebar-title">Employee Portal</p>
                </div>
                
                <ul class="nav flex-column mt-4">
                    <li class="nav-item">
                        <a class="nav-link" href="empdashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="customer_management.php">
                            <i class="bi bi-people"></i> Customers
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="loan_applications.php">
                            <i class="bi bi-currency-dollar"></i> Loan Applications
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">
                            <i class="bi bi-person-circle"></i> My Profile
                        </a>
                    </li>
                    <li class="nav-item mt-3">
                        <a class="nav-link" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
                
                <div class="user-info mt-auto">
                    <img src="profile-placeholder.jpg" alt="Profile">
                    <div>
                        <p class="user-name"><?php echo $_SESSION['employee_name']; ?></p>
                        <p class="user-role">Loan Officer</p>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="dashboard-header">
                    <h1 class="mb-3">Loan Applications</h1>
                    <p class="text-muted">Manage and track all loan applications and approvals</p>
                </div>

                <?php if(isset($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if(isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Stats Cards -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <i class="bi bi-cash-stack"></i>
                            <h2><?php echo $total_loans; ?></h2>
                            <p>Total Loans</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <i class="bi bi-currency-dollar"></i>
                            <h2>$<?php echo number_format($total_amount, 2); ?></h2>
                            <p>Total Amount</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <i class="bi bi-credit-card-2-front"></i>
                            <h2>$<?php echo number_format($total_paid, 2); ?></h2>
                            <p>Total Paid</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <i class="bi bi-hourglass-split"></i>
                            <h2>$<?php echo number_format($total_outstanding, 2); ?></h2>
                            <p>Outstanding Balance</p>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <ul class="nav nav-tabs mt-4" id="loanTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="all-loans-tab" data-bs-toggle="tab" data-bs-target="#all-loans" type="button" role="tab" aria-controls="all-loans" aria-selected="true">
                            <i class="bi bi-list me-1"></i> All Loans
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="new-loan-tab" data-bs-toggle="tab" data-bs-target="#new-loan" type="button" role="tab" aria-controls="new-loan" aria-selected="false">
                            <i class="bi bi-plus-circle me-1"></i> New Loan
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="loanTabsContent">
                    <!-- All Loans Tab -->
                    <div class="tab-pane fade show active" id="all-loans" role="tabpanel" aria-labelledby="all-loans-tab">
                        <!-- Filter Section -->
                        <div class="filter-section">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="filter-loan-type" class="form-label">Loan Type</label>
                                        <select class="form-select" id="filter-loan-type">
                                            <option value="">All Types</option>
                                            <option value="Personal">Personal Loan</option>
                                            <option value="Home">Home Loan</option>
                                            <option value="Education">Education Loan</option>
                                            <option value="Vehicle">Vehicle Loan</option>
                                            <option value="Business">Business Loan</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="filter-date-from" class="form-label">From Date</label>
                                        <input type="text" class="form-control date-picker" id="filter-date-from" placeholder="From Date">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="filter-date-to" class="form-label">To Date</label>
                                        <input type="text" class="form-control date-picker" id="filter-date-to" placeholder="To Date">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="filter-search" class="form-label">Search</label>
                                        <input type="text" class="form-control" id="filter-search" placeholder="Customer name, ID, etc.">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 text-end">
                                    <button class="btn btn-primary" id="apply-filters">
                                        <i class="bi bi-funnel me-2"></i>Apply Filters
                                    </button>
                                    <button class="btn btn-outline-secondary ms-2" id="reset-filters">
                                        <i class="bi bi-arrow-repeat me-2"></i>Reset
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Loans Table -->
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-list-check me-2"></i> Loan Applications</span>
                                <div>
                                    <button class="btn btn-sm btn-light" id="print-loans">
                                        <i class="bi bi-printer me-1"></i> Print
                                    </button>
                                    <button class="btn btn-sm btn-light ms-2" id="export-loans">
                                        <i class="bi bi-download me-1"></i> Export
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if(!empty($loans)): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover" id="loans-table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Customer</th>
                                                <th>Type</th>
                                                <th>Amount</th>
                                                <th>Progress</th>
                                                <th>Approved By</th>
                                                <th>Branch</th>
                                                <th>Dates</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($loans as $loan): 
                                                // Calculate payment progress percentage
                                                $paid = isset($loan['paid']) ? $loan['paid'] : 0;
                                                $progress = ($loan['loan_amount'] > 0) ? ($paid / $loan['loan_amount']) * 100 : 0;
                                            ?>
                                            <tr>
                                                <td><?php echo $loan['loan_id']; ?></td>
                                                <td>
                                                    <a href="customer_details.php?id=<?php echo $loan['customer_id']; ?>" data-bs-toggle="tooltip" title="View Customer Details">
                                                        <?php echo $loan['customer_name']; ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary"><?php echo $loan['loan_type']; ?></span>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span>$<?php echo number_format($loan['loan_amount'], 2); ?></span>
                                                        <?php if(isset($loan['paid']) && $loan['paid'] > 0): ?>
                                                        <small class="text-muted">
                                                            Paid: $<?php echo number_format($loan['paid'], 2); ?>
                                                        </small>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="progress" style="width: 100px;">
                                                        <div class="progress-bar" role="progressbar" style="width: <?php echo $progress; ?>%" aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                    <small><?php echo number_format($progress, 1); ?>% complete</small>
                                                </td>
                                                <td><?php echo $loan['sanctioned_by'] ?? 'N/A'; ?></td>
                                                <td><?php echo $loan['branch_name'] ?? 'N/A'; ?></td>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <small data-bs-toggle="tooltip" title="Sanction Date">
                                                            <i class="bi bi-calendar-check"></i> 
                                                            <?php echo isset($loan['sanction_date']) ? date('M d, Y', strtotime($loan['sanction_date'])) : 'N/A'; ?>
                                                        </small>
                                                        <?php if(isset($loan['end_date'])): ?>
                                                        <small data-bs-toggle="tooltip" title="End Date">
                                                            <i class="bi bi-calendar-x"></i> 
                                                            <?php echo date('M d, Y', strtotime($loan['end_date'])); ?>
                                                        </small>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-sm btn-outline-primary view-loan-btn" data-loan-id="<?php echo $loan['loan_id']; ?>" data-bs-toggle="modal" data-bs-target="#viewLoanModal">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary edit-loan-btn" data-loan-id="<?php echo $loan['loan_id']; ?>" data-bs-toggle="modal" data-bs-target="#editLoanModal">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Pagination -->
                                <nav aria-label="Loans pagination">
                                    <ul class="pagination">
                                        <li class="page-item disabled">
                                            <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                                        </li>
                                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                                        <li class="page-item">
                                            <a class="page-link" href="#">Next</a>
                                        </li>
                                    </ul>
                                </nav>
                                
                                <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>No loan applications found.
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- New Loan Tab -->
                    <div class="tab-pane fade" id="new-loan" role="tabpanel" aria-labelledby="new-loan-tab">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-plus-circle me-2"></i> Create New Loan
                            </div>
                            <div class="card-body">
                                <form class="loan-form" method="POST" action="">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="customer_id" class="form-label">Customer</label>
                                                <select class="form-select" id="customer_id" name="customer_id" required>
                                                    <option value="">-- Select Customer --</option>
                                                    <?php foreach($customers as $customer): ?>
                                                    <option value="<?php echo $customer['customer_id']; ?>">
                                                        <?php echo $customer['customer_name']; ?> (ID: <?php echo $customer['customer_id']; ?>)
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="loan_type" class="form-label">Loan Type</label>
                                                <select class="form-select" id="loan_type" name="loan_type" required>
                                                    <option value="">-- Select Loan Type --</option>
                                                    <option value="Personal">Personal Loan</option>
                                                    <option value="Home">Home Loan</option>
                                                    <option value="Education">Education Loan</option>
                                                    <option value="Vehicle">Vehicle Loan</option>
                                                    <option value="Business">Business Loan</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="loan_amount" class="form-label">Loan Amount</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" class="form-control" id="loan_amount" name="loan_amount" min="1000" step="100" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="payment_frequency" class="form-label">Payment Frequency</label>
                                                <select class="form-select" id="payment_frequency" name="payment_frequency" required>
                                                    <option value="">-- Select Payment Frequency --</option>
                                                    <option value="Weekly">Weekly</option>
                                                    <option value="Bi-Weekly">Bi-Weekly</option>
                                                    <option value="Monthly">Monthly</option>
                                                    <option value="Quarterly">Quarterly</option>
                                                    <option value="Annually">Annually</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="end_date" class="form-label">End Date</label>
                                                <input type="text" class="form-control date-picker" id="end_date" name="end_date" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Branch</label>
                                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($branch['branch_name'] ?? 'N/A'); ?>" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 text-end">
                                            <button type="submit" name="approve_loan" class="btn btn-primary">
                                                <i class="bi bi-check-circle me-2"></i> Approve Loan
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <div class="card mt-4">
                            <div class="card-header bg-info-light">
                                <i class="bi bi-info-circle me-2"></i> Loan Approval Guidelines
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">Bankly Loan Approval Policy</h5>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item bg-info-light">
                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                        Personal Loans: Minimum $1,000, Maximum $50,000
                                    </li>
                                    <li class="list-group-item bg-info-light">
                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                        Home Loans: Minimum $50,000, Maximum $2,000,000
                                    </li>
                                    <li class="list-group-item bg-info-light">
                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                        Education Loans: Minimum $5,000, Maximum $100,000
                                    </li>
                                    <li class="list-group-item bg-info-light">
                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                        Vehicle Loans: Minimum $10,000, Maximum $100,000
                                    </li>
                                    <li class="list-group-item bg-info-light">
                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                        Business Loans: Minimum $25,000, Maximum $500,000
                                    </li>
                                </ul>
                                <div class="alert alert-warning mt-3">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    <strong>Note:</strong> All loans are subject to credit approval. Please verify customer's credit score and financial history before approval.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Loan Modal -->
    <div class="modal fade" id="viewLoanModal" tabindex="-1" aria-labelledby="viewLoanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewLoanModalLabel">Loan Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="loanDetailsContent">
                    <!-- Content will be loaded via AJAX -->
                    <div class="text-center my-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="printLoanDetails">
                        <i class="bi bi-printer me-1"></i> Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Loan Modal -->
    <div class="modal fade" id="editLoanModal" tabindex="-1" aria-labelledby="editLoanModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editLoanModalLabel">Edit Loan Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body" id="editLoanContent">
                        <!-- Content will be loaded via AJAX -->
                        <div class="text-center my-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="loan_id" id="modalLoanId">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_loan" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Initialize date pickers
        flatpickr('.date-picker', {
            dateFormat: 'Y-m-d',
            minDate: 'today'
        });

        // View Loan Details
        document.querySelectorAll('.view-loan-btn').forEach(button => {
            button.addEventListener('click', function() {
                const loanId = this.getAttribute('data-loan-id');
                fetch('get_loan_details.php?id=' + loanId)
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById('loanDetailsContent').innerHTML = data;
                    });
            });
        });

        // Edit Loan Details
        document.querySelectorAll('.edit-loan-btn').forEach(button => {
            button.addEventListener('click', function() {
                const loanId = this.getAttribute('data-loan-id');
                document.getElementById('modalLoanId').value = loanId;
                
                fetch('get_loan_edit.php?id=' + loanId)
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById('editLoanContent').innerHTML = data;
                        // Reinitialize any date pickers in the modal
                        flatpickr('.modal .date-picker', {
                            dateFormat: 'Y-m-d'
                        });
                    });
            });
        });

        // Print Loan Details
        document.getElementById('printLoanDetails').addEventListener('click', function() {
            const printContent = document.getElementById('loanDetailsContent').innerHTML;
            const originalContent = document.body.innerHTML;
            
            document.body.innerHTML = printContent;
            window.print();
            document.body.innerHTML = originalContent;
            window.location.reload();
        });

        // Apply Filters
        document.getElementById('apply-filters').addEventListener('click', function() {
            const loanType = document.getElementById('filter-loan-type').value;
            const dateFrom = document.getElementById('filter-date-from').value;
            const dateTo = document.getElementById('filter-date-to').value;
            const search = document.getElementById('filter-search').value.toLowerCase();
            
            document.querySelectorAll('#loans-table tbody tr').forEach(row => {
                const rowLoanType = row.cells[2].textContent;
                const rowCustomer = row.cells[1].textContent.toLowerCase();
                const rowDates = row.cells[7].textContent;
                
                let showRow = true;
                
                // Filter by loan type
                if (loanType && rowLoanType !== loanType) {
                    showRow = false;
                }
                
                // Filter by search term
                if (search && !rowCustomer.includes(search)) {
                    showRow = false;
                }
                
                // Filter by date range (simplified for this example)
                if (dateFrom || dateTo) {
                    // In a real implementation, you would parse the dates and compare
                    // This is just a placeholder for the logic
                    showRow = true; // Would be set based on actual date comparison
                }
                
                row.style.display = showRow ? '' : 'none';
            });
        });

        // Reset Filters
        document.getElementById('reset-filters').addEventListener('click', function() {
            document.getElementById('filter-loan-type').value = '';
            document.getElementById('filter-date-from').value = '';
            document.getElementById('filter-date-to').value = '';
            document.getElementById('filter-search').value = '';
            
            document.querySelectorAll('#loans-table tbody tr').forEach(row => {
                row.style.display = '';
            });
        });

        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
</body>
</html>