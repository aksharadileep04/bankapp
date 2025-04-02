<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_POST['customer_id'];
    $paid = $_POST['paid'];
    $indebt = $_POST['indebt'];

    foreach ($paid as $loan_id => $paid_amount) {
        $indebt_amount = $indebt[$loan_id];

        // Update the loan record
        $sql = "UPDATE loan SET paid = ?, indebt = ? WHERE loan_id = ? AND customer_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$paid_amount, $indebt_amount, $loan_id, $customer_id]);
    }

    // Redirect back to the loan records page
    header("Location: loan.php?customer_id=" . urlencode($customer_id));
    exit;
}
?>
