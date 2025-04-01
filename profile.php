<?php
require 'config.php';
session_start();

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['change_password'])) {
        $customer_id = $_POST['customer_id'];
        $old_password = $_POST['old_password'];
        $new_password = $_POST['new_password'];
        
        // Verify old password
        $stmt = $conn->prepare("SELECT * FROM customer WHERE customer_id = ? AND password = ?");
        $stmt->execute([$customer_id, $old_password]);
        
        if ($stmt->rowCount() > 0) {
            // Update password
            $update = $conn->prepare("UPDATE customer SET password = ? WHERE customer_id = ?");
            $update->execute([$new_password, $customer_id]);
            $_SESSION['message'] = "Password changed successfully!";
        } else {
            $_SESSION['error'] = "Old password is incorrect!";
        }
    }
    
    // Handle forgot password
    if (isset($_POST['forgot_password'])) {
        $customer_id = $_POST['customer_id'];
        $username = $_POST['username'];
        $branch_id = $_POST['branch_id'];
        
        // Verify credentials
        $stmt = $conn->prepare("SELECT * FROM customer WHERE customer_id = ? AND username = ? AND branch_id = ?");
        $stmt->execute([$customer_id, $username, $branch_id]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['reset_customer'] = $customer_id;
            $_SESSION['message'] = "Please set your new password";
        } else {
            $_SESSION['error'] = "Verification failed! Please check your details.";
        }
    }
    
    // Handle password reset
    if (isset($_POST['reset_password'])) {
        $customer_id = $_SESSION['reset_customer'];
        $new_password = $_POST['new_password'];
        
        $update = $conn->prepare("UPDATE customer SET password = ? WHERE customer_id = ?");
        $update->execute([$new_password, $customer_id]);
        
        unset($_SESSION['reset_customer']);
        $_SESSION['message'] = "Password reset successfully!";
    }
    
    header("Location: ".$_SERVER['PHP_SELF']."?customer_id=".$customer_id);
    exit();
}

if (!isset($_GET['customer_id'])) {
    die("Customer ID is required.");
}

$customer_id = $_GET['customer_id'];

$sql = "SELECT customer.customer_id, customer.customer_name, customer.username, 
               customer.branch_id, branch.branch_name 
        FROM customer  
        JOIN branch ON customer.branch_id = branch.branch_id
        WHERE customer.customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$customer_id]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

// Get initials for profile
$initials = '';
if ($customer) {
    $names = explode(' ', $customer['customer_name']);
    $initials = strtoupper(substr($names[0], 0, 1));
    if (count($names) > 1) {
        $initials .= strtoupper(substr(end($names), 0, 1));
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | Digital Banking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1976d2;
            --secondary-color: #64b5f6;
            --accent-color: #e3f2fd;
            --light-bg: #f5fbff;
            --dark-blue: #0d47a1;
        }
        
        body {
            background: linear-gradient(rgba(245, 251, 255, 0.95), rgba(245, 251, 255, 0.97)),
                        url('elegant-banking-background.png');
            background-size: 40%;
            background-position: right 80px bottom 40px;
            background-repeat: no-repeat;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .profile-header {
            background: linear-gradient(135deg, var(--dark-blue), var(--primary-color));
            color: white;
            border-radius: 0 0 20px 20px;
            padding: 1.5rem 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .profile-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(25, 118, 210, 0.1);
            transition: all 0.3s ease;
            border: none;
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .profile-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
            box-shadow: 0 5px 15px rgba(25, 118, 210, 0.2);
            margin: 0 auto;
        }

        .detail-item {
            border-left: 3px solid var(--secondary-color);
            padding-left: 1rem;
            margin: 1rem 0;
            transition: all 0.3s ease;
        }

        .info-badge {
            background-color: var(--accent-color);
            color: var(--dark-blue);
            padding: 6px 12px;
            border-radius: 50px;
            font-weight: 500;
            font-size: 0.8rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 0.5rem 1.25rem;
            border-radius: 8px;
            font-weight: 500;
        }

        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 0.5rem 1.25rem;
            border-radius: 8px;
            font-weight: 500;
        }

        .modal-header {
            background-color: var(--primary-color);
            color: white;
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

<div class="profile-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="mb-0 fs-4"><i class="fas fa-user-circle me-2"></i>My Profile</h1>
            </div>
            <div class="col-md-6 text-md-end mt-2 mt-md-0">
                <span class="info-badge">
                    <i class="fas fa-id-card me-1"></i>ID: <?= htmlspecialchars($customer_id) ?>
                </span>
            </div>
        </div>
    </div>
</div>

<div class="container mb-5">
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <?php if (!$customer): ?>
                <div class="alert alert-danger text-center py-4">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Customer profile not found</strong>
                </div>
            <?php else: ?>
                <div class="profile-card p-4">
                    <div class="text-center mb-4">
                        <div class="profile-icon"><?= $initials ?></div>
                        <h5 class="mt-3 mb-1"><?= htmlspecialchars($customer['customer_name']) ?></h5>
                        <small class="text-muted"><?= htmlspecialchars($customer['branch_name']) ?> Branch</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6 class="text-muted mb-3"><i class="fas fa-user me-2"></i>Account Details</h6>
                                <div class="detail-item">
                                    <small class="text-muted">Username</small>
                                    <p class="mb-0"><?= htmlspecialchars($customer['username'] ?? 'Not Set') ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6 class="text-muted mb-3"><i class="fas fa-university me-2"></i>Branch Info</h6>
                                <div class="detail-item">
                                    <small class="text-muted">Branch ID</small>
                                    <p class="mb-0"><?= htmlspecialchars($customer['branch_id']) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-center gap-2 mt-4">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                            <i class="fas fa-edit me-1"></i> Edit Profile
                        </button>
                        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">
                            <i class="fas fa-key me-1"></i> Forgot Password
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Password</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="customer_id" value="<?= $customer_id ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Old Password</label>
                        <input type="password" name="old_password" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="change_password" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Forgot Password Modal -->
<div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reset Password</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <?php if (!isset($_SESSION['reset_customer'])): ?>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="customer_id" value="<?= $customer_id ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Branch ID</label>
                        <input type="text" name="branch_id" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="forgot_password" class="btn btn-primary">Verify</button>
                </div>
            </form>
            <?php else: ?>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="reset_password" class="btn btn-primary">Set New Password</button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Show modal if there's a reset session
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (isset($_SESSION['reset_customer'])): ?>
            var modal = new bootstrap.Modal(document.getElementById('forgotPasswordModal'));
            modal.show();
        <?php endif; ?>
    });
</script>
</body>
</html>