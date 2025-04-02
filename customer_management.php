<?php
session_start();
if (!isset($_SESSION['employee_id'])) {
    header("Location: loginso.php");
    exit();
}

require 'config.php';

$error_message = '';
$success_message = '';

// Get all customers
try {
    $stmt = $conn->prepare("SELECT c.*, b.branch_name, b.branch_location 
                            FROM customer c 
                            LEFT JOIN branch b ON c.branch_id = b.branch_id 
                            ORDER BY c.customer_id");
    $stmt->execute();
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error_message = "Error fetching customer data: " . $e->getMessage();
    $customers = []; // Initialize as empty array when there's an error
}

// Get all branches
try {
    $stmt = $conn->prepare("SELECT b.*, 
                            (SELECT COUNT(*) FROM customer c WHERE c.branch_id = b.branch_id) AS customer_count
                            FROM branch b 
                            ORDER BY b.branch_id");
    $stmt->execute();
    $branches = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error_message = "Error fetching branch data: " . $e->getMessage();
    $branches = []; // Initialize as empty array when there's an error
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers & Branches - Bankly</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@300;400;600&display=swap">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
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

        .page-header {
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

        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
            border: none;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
        }

        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .nav-tabs .nav-link {
            color: var(--dark-color);
            border: none;
            font-weight: 500;
            padding: 12px 20px;
        }

        .nav-tabs .nav-link.active {
            color: var(--primary-color);
            border-bottom: 3px solid var(--primary-color);
            background-color: transparent;
        }

        .nav-tabs {
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 20px;
        }

        .tab-content {
            padding-top: 20px;
        }

        .badge-branch {
            background-color: var(--accent-gold);
            color: var(--dark-color);
            font-weight: 500;
            padding: 6px 12px;
            border-radius: 20px;
        }

        .badge-count {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
            padding: 6px 12px;
            border-radius: 20px;
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

        .address-compact {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current, 
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: var(--primary-color);
            color: white !important;
            border: 1px solid var(--primary-color);
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: var(--light-color);
            color: var(--primary-color) !important;
            border: 1px solid var(--primary-color);
        }

        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 5px 10px;
        }

        .branch-card {
            border-left: 5px solid var(--accent-gold);
        }

        .branch-icon {
            font-size: 2rem;
            color: var(--primary-color);
        }

        .counter-card {
            text-align: center;
            padding: 20px;
        }

        .counter-card i {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .counter-card h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--dark-color);
        }

        .counter-card p {
            color: #666;
            font-size: 1rem;
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
                        <a class="nav-link" href="empdashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="customer_management.php">
                            <i class="bi bi-people"></i> Customers
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="loan_applications.php">
                            <i class="bi bi-currency-dollar"></i> Loan Applications
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="branch_central.php">
                            <i class="bi bi-building"></i> Branch info
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
                <div class="page-header">
                    <h1>Customers & Branches Overview</h1>
                    <p class="text-muted">View and manage all customers and branches in the system</p>
                </div>

                <?php if(isset($error_message) && !empty($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?php echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if(isset($success_message) && !empty($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <?php echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Stats Overview -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card counter-card">
                            <i class="bi bi-people"></i>
                            <h2><?php echo count($customers); ?></h2>
                            <p>Total Customers</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card counter-card">
                            <i class="bi bi-building"></i>
                            <h2><?php echo count($branches); ?></h2>
                            <p>Total Branches</p>
                        </div>
                    </div>
                </div>

                <!-- Tabs Navigation -->
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="customers-tab" data-bs-toggle="tab" data-bs-target="#customers" type="button" role="tab" aria-controls="customers" aria-selected="true">
                            <i class="bi bi-people me-2"></i>All Customers
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="branches-tab" data-bs-toggle="tab" data-bs-target="#branches" type="button" role="tab" aria-controls="branches" aria-selected="false">
                            <i class="bi bi-building me-2"></i>All Branches
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="myTabContent">
                    <!-- Customers Tab -->
                    <div class="tab-pane fade show active" id="customers" role="tabpanel" aria-labelledby="customers-tab">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-people me-2"></i>Customer List</span>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="customersTable">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Username</th>
                                                <th>Address</th>
                                                <th>Branch</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($customers as $customer): ?>
                                            <tr>
                                                <td><?php echo $customer['customer_id']; ?></td>
                                                <td><?php echo htmlspecialchars($customer['customer_name']); ?></td>
                                                <td><?php echo $customer['username'] ? htmlspecialchars($customer['username']) : 'N/A'; ?></td>
                                                <td title="<?php 
                                                    $address_parts = [];
                                                    if(isset($customer['street']) && !empty($customer['street'])) $address_parts[] = $customer['street'];
                                                    if(isset($customer['city']) && !empty($customer['city'])) $address_parts[] = $customer['city'];
                                                    if(isset($customer['country']) && !empty($customer['country'])) $address_parts[] = $customer['country'];
                                                    echo !empty($address_parts) ? htmlspecialchars(implode(', ', $address_parts)) : 'No address';
                                                ?>">
                                                    <div class="address-compact">
                                                        <?php 
                                                        echo !empty($address_parts) ? htmlspecialchars(implode(', ', $address_parts)) : 'No address';
                                                        ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if(isset($customer['branch_name']) && !empty($customer['branch_name'])): ?>
                                                        <span class="badge badge-branch">
                                                            <?php echo htmlspecialchars($customer['branch_name']); ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Not Assigned</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Branches Tab -->
                    <div class="tab-pane fade" id="branches" role="tabpanel" aria-labelledby="branches-tab">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-building me-2"></i>Branch List</span>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="branchesTable">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Branch Name</th>
                                                <th>Location</th>
                                                <th>Customers</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($branches as $branch): ?>
                                            <tr>
                                                <td><?php echo $branch['branch_id']; ?></td>
                                                <td><?php echo htmlspecialchars($branch['branch_name']); ?></td>
                                                <td><?php echo htmlspecialchars($branch['branch_location']); ?></td>
                                                <td>
                                                    <span class="badge badge-count">
                                                        <?php echo $branch['customer_count']; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Branch Cards View -->
                        <div class="row mt-4">
                            <?php foreach($branches as $branch): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card branch-card h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-light rounded-circle p-3 me-3">
                                                <i class="bi bi-building branch-icon"></i>
                                            </div>
                                            <div>
                                                <h5 class="card-title mb-1"><?php echo htmlspecialchars($branch['branch_name']); ?></h5>
                                                <p class="card-text text-muted">
                                                    <i class="bi bi-geo-alt me-1"></i> 
                                                    <?php echo htmlspecialchars($branch['branch_location']); ?>
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                            <div>
                                                <span class="badge badge-count">
                                                    <i class="bi bi-people me-1"></i> 
                                                    <?php echo $branch['customer_count']; ?> Customers
                                                </span>
                                            </div>
                                            <div>
                                                <span class="badge bg-secondary">
                                                    Branch #<?php echo $branch['branch_id']; ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Initialize DataTables
            $('#customersTable').DataTable({
                "pageLength": 10,
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "order": [[ 0, "asc" ]]
            });
            
            $('#branchesTable').DataTable({
                "pageLength": 10,
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "order": [[ 0, "asc" ]]
            });
            
            // Enable Bootstrap tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
        });
    </script>
</body>
</html>