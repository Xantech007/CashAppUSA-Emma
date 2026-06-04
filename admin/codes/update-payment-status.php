<?php
session_start();
include('../../config/dbcon.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $status = intval($_POST['status']);

    if ($id > 0) {
        $query = "UPDATE user_payment_methods SET status = ? WHERE id = ?";
        $stmt = mysqli_prepare($con, $query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ii", $status, $id);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['status'] = "Payment status updated successfully!";
                $_SESSION['status_code'] = "success";
            } else {
                $_SESSION['status'] = "Failed to update status.";
                $_SESSION['status_code'] = "error";
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $_SESSION['status'] = "Invalid request.";
        $_SESSION['status_code'] = "error";
    }
}

header("Location: ../manage-deposits.php");
exit();
?>
