<?php
session_start();
include('../../config/dbcon.php');

// 🔒 Basic admin check (optional but recommended)
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// ✅ Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../manage-deposits.php");
    exit;
}

// ✅ Get and sanitize inputs
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$status = isset($_POST['status']) ? intval($_POST['status']) : -1;

// ✅ Validate data
if ($id <= 0 || !in_array($status, [0, 1, 2])) {
    $_SESSION['error'] = "Invalid request.";
    header("Location: ../manage-deposits.php");
    exit;
}

// 🔒 Optional CSRF check (only if you added token in form)
/*
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = "Invalid security token.";
    header("Location: ../manage-deposits.php");
    exit;
}
*/

// ✅ Update using prepared statement
$stmt = $con->prepare("UPDATE user_payment_methods SET status = ? WHERE id = ?");
$stmt->bind_param("ii", $status, $id);

if ($stmt->execute()) {

    // Optional: log admin action (recommended for auditing)
    /*
    $admin_id = $_SESSION['admin_id'];
    $log = $con->prepare("INSERT INTO admin_logs (admin_id, action) VALUES (?, ?)");
    $action = "Updated payment ID $id to status $status";
    $log->bind_param("is", $admin_id, $action);
    $log->execute();
    */

    $_SESSION['success'] = "Status updated successfully.";

} else {
    $_SESSION['error'] = "Failed to update status.";
}

$stmt->close();

// 🔁 Redirect back
header("Location: ../manage-deposits.php");
exit;
?>
