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

// Process loan approval form submission
if (isset($_POST['approve_loan'])) {
    $customer_id = $_POST['customer_id'];
    $loan_amount = $_POST['loan_amount'];
    $loan_type = $_POST['loan_type'];
    $employee_id = $_SESSION['employee_id'];
    
    try {
        $stmt = $conn->prepare("INSERT INTO loan (customer_id, sanction_employee_id, loan_amount, loan_type) 
                                VALUES (?, ?, ?, ?)");
        $result = $stmt->execute([$customer_id, $employee_id, $loan_amount, $loan_type]);
        
        if ($result) {
            $success_message = "✅ Loan approved successfully!";
        } else {
            $error_message = "❌ Failed to approve loan!";
        }
    } catch(PDOException $e) {
        $error_message = "⚠️ Database error: " . $e->getMessage();
    }
}

// Get all customers
try {
    $stmt = $conn->prepare("SELECT * FROM customer ORDER BY customer_id DESC");
    $stmt->execute();
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $customer_error = "Error fetching customer data: " . $e->getMessage();
    $customers = []; // Initialize as empty array when there's an error
}

// Get all branches
try {
    $stmt = $conn->prepare("SELECT * FROM branch ORDER BY branch_id");
    $stmt->execute();
    $branches = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $branch_list_error = "Error fetching branches: " . $e->getMessage();
    $branches = []; // Initialize as empty array when there's an error
}

// Get loans data
try {
    $stmt = $conn->prepare("
        SELECT l.*, c.customer_name, lse.employee_name 
        FROM loan l
        JOIN customer c ON l.customer_id = c.customer_id
        JOIN loan_sanctioning_employee lse ON l.sanctioning_employee_id = lse.employee_id
        ORDER BY l.loan_id DESC
    ");
    $stmt->execute();
    $loans = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $loans_error = "Error fetching loans data: " . $e->getMessage();
    $loans = []; // Initialize as empty array when there's an error
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Officer Dashboard - Bankly</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@300;400;600&display=swap">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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

        .branch-info {
            background-color: rgba(255,255,255,0.7);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
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
                        <a class="nav-link active" href="empdashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="customer_management.php">
                            <i class="bi bi-people"></i> Customers
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="loan_applications.php">
                            <i class="bi bi-currency-dollar"></i> Loan Applications
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="profile_emp.php">
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
                    <h1 class="mb-3">Welcome, <?php echo $_SESSION['employee_name']; ?>!</h1>
                    <p class="text-muted">Here's your activity overview for today - <?php echo date('F d, Y'); ?></p>
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

                <!-- Branch Information -->
                <?php if(isset($branch) && $branch): ?>
                <div class="branch-info">
                    <h5><i class="bi bi-building"></i> Branch Information</h5>
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <p><strong>Branch:</strong> <?php echo $branch['branch_name']; ?></p>
                        </div>
                        
                        <div class="col-md-4">
                            <p><strong>Location:</strong> <?php echo $branch['branch_location']; ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Stats Cards -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="card stats-card">
                            <i class="bi bi-people-fill"></i>
                            <h2><?php echo isset($customers) ? count($customers) : 0; ?></h2>
                            <p>Total Customers</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stats-card">
                            <i class="bi bi-currency-dollar"></i>
                            <h2><?php echo isset($loans) ? count($loans) : 0; ?></h2>
                            <p>Active Loans</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stats-card">
                            <i class="bi bi-bank"></i>
                            <h2><?php echo isset($branches) ? count($branches) : 0; ?></h2>
                            <p>Bank Branches</p>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <!-- Loan Approval Form -->
                    <div class="col-md-5">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-plus-circle me-2"></i> Approve New Loan
                            </div>
                            <div class="card-body">
                                <form class="loan-form" method="POST" action="">
                                    <div class="mb-3">
                                        <label for="customer_id" class="form-label">Customer ID</label>
                                        <input type="number" class="form-control" id="customer_id" name="customer_id" required placeholder="Enter Customer ID">
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
                                    <button type="submit" name="approve_loan" class="btn btn-primary w-100">
                                        <i class="bi bi-check-circle me-2"></i>Approve Loan
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Loans -->
                    <div class="col-md-7">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-list-check me-2"></i> Recent Loans
                            </div>
                            <div class="card-body">
                                <?php if(isset($loans) && !empty($loans)): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Customer</th>
                                                <th>Type</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Approved By</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach(array_slice($loans, 0, 5) as $loan): ?>
                                            <tr>
                                                <td><?php echo $loan['loan_id']; ?></td>
                                                <td><?php echo $loan['customer_name']; ?></td>
                                                <td><?php echo $loan['loan_type']; ?></td>
                                                <td>$<?php echo number_format($loan['loan_amount'], 2); ?></td>
                                                <td>
                                                    <span class="badge bg-success">Active</span>
                                                </td>
                                                <td><?php echo $loan['employee_name']; ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-end mt-3">
                                    <a href="loan_applications.php" class="btn btn-sm btn-outline-primary">View All Loans</a>
                                </div>
                                <?php else: ?>
                                <p class="text-center text-muted">No loans have been approved yet.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer List -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-people me-2"></i> Recent Customers
                            </div>
                            <div class="card-body">
                                <?php if(isset($customers) && !empty($customers)): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Phone</th>
                                                <th>Address</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach(array_slice($customers, 0, 5) as $customer): ?>
                                            <tr>
                                                <td><?php echo $customer['customer_id']; ?></td>
                                                <td><?php echo $customer['customer_name']; ?></td>
                                                <td><?php echo isset($customer['email']) ? $customer['email'] : 'N/A'; ?></td>
                                                <td><?php echo isset($customer['phone']) ? $customer['phone'] : 'N/A'; ?></td>
                                                <td>
                                                    <?php 
                                                    $address_parts = [];
                                                    if(isset($customer['street']) && !empty($customer['street'])) $address_parts[] = $customer['street'];
                                                    if(isset($customer['city']) && !empty($customer['city'])) $address_parts[] = $customer['city'];
                                                    if(isset($customer['country']) && !empty($customer['country'])) $address_parts[] = $customer['country'];
                                                    echo !empty($address_parts) ? implode(', ', $address_parts) : 'N/A';
                                                    ?>
                                                </td>
                                                <td>
                                                    <a href="customer_details.php?id=<?php echo $customer['customer_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-end mt-3">
                                    <a href="customer_management.php" class="btn btn-sm btn-outline-primary">View All Customers</a>
                                </div>
                                <?php else: ?>
                                <p class="text-center text-muted">No customers found.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Enable Bootstrap tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    </script>
</body>
</html>