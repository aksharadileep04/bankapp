<?php
session_start();
if (!isset($_SESSION['employee_id'])) {
    header("Location: login.php");
    exit();
}

require 'config.php';

// Get employee information
$employee_id = $_SESSION['employee_id'];
$stmt = $conn->prepare("SELECT * FROM employee WHERE employee_id = ?");
$stmt->execute([$employee_id]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .profile-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .profile-details {
            margin-bottom: 20px;
        }
        .detail-row {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f5f5f5;
        }
        .detail-label {
            font-weight: bold;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="profile-container">
            <div class="profile-header">
                <h2>Employee Profile</h2>
            </div>
            
            <div class="profile-details">
                <div class="row detail-row">
                    <div class="col-md-3 detail-label">Employee ID:</div>
                    <div class="col-md-9"><?php echo htmlspecialchars($employee['employee_id']); ?></div>
                </div>
                
                <div class="row detail-row">
                    <div class="col-md-3 detail-label">Name:</div>
                    <div class="col-md-9"><?php echo htmlspecialchars($employee['employee_name']); ?></div>
                </div>
                
                <div class="row detail-row">
                    <div class="col-md-3 detail-label">Position:</div>
                    <div class="col-md-9"><?php echo htmlspecialchars($employee['position']); ?></div>
                </div>
                
                <div class="row detail-row">
                    <div class="col-md-3 detail-label">Branch ID:</div>
                    <div class="col-md-9"><?php echo htmlspecialchars($employee['branch_id']); ?></div>
                </div>
                
                <div class="row detail-row">
                    <div class="col-md-3 detail-label">Phone Number:</div>
                    <div class="col-md-9"><?php echo htmlspecialchars($employee['phone_number']); ?></div>
                </div>
            </div>
            
            <div class="text-center">
                <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
                <a href="edit_profile.php" class="btn btn-secondary">Edit Profile</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>