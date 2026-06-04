<?php
session_start();
include('../config/dbcon.php');

if (isset($_POST['withdraw'])) {

    // ===============================
    // USER INPUT
    // ===============================
    $email  = mysqli_real_escape_string($con, $_POST['email']);
    $amount = floatval($_POST['amount']);
    $balance = floatval($_POST['balance']);

    // ===============================
    // GET USER INFO
    // ===============================
    $verify_query = "SELECT id, verify, country FROM users WHERE email = ? LIMIT 1";
    $stmt = $con->prepare($verify_query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result || $result->num_rows === 0) {
        $_SESSION['error'] = "User not found.";
        header("Location: ../users/withdrawals.php");
        exit();
    }

    $user = $result->fetch_assoc();
    $user_id = $user['id'];
    $country = $user['country'];
    $verify_status = (int)$user['verify'];

    if ($verify_status != 2) {
        $_SESSION['error'] = "You are not verified to withdraw.";
        header("Location: ../users/withdrawals.php");
        exit();
    }

    // ===============================
    // GET LINKED PAYMENT METHOD
    // ===============================
    $pm_query = "SELECT * FROM user_payment_methods WHERE user_id = ? LIMIT 1";
    $stmt = $con->prepare($pm_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $pm_result = $stmt->get_result();

    if (!$pm_result || $pm_result->num_rows === 0) {
        $_SESSION['error'] = "No payment method linked.";
        header("Location: ../users/withdrawals.php");
        exit();
    }

    $pm = $pm_result->fetch_assoc();

    $payment_method = $pm['payment_method'];
    $paypal_email = $pm['paypal_email'];
    $cashapp_tag = $pm['cashapp_tag'];
    $venmo_username = $pm['venmo_username'];
    $zelle_name = $pm['zelle_name'];
    $zelle_contact = $pm['zelle_contact'];

    // ===============================
    // MINIMUM CHECK
    // ===============================
    if ($amount < 50) {
        $_SESSION['error'] = "Minimum withdrawal is $50";
        header("Location: ../users/withdrawals.php");
        exit();
    }

    if ($amount > $balance) {
        $_SESSION['error'] = "Insufficient balance.";
        header("Location: ../users/withdrawals.php");
        exit();
    }

    // ===============================
    // REGION SETTINGS (OPTIONAL)
    // ===============================
    $region_query = "SELECT currency FROM region_settings WHERE country = ? LIMIT 1";
    $stmt = $con->prepare($region_query);
    $stmt->bind_param("s", $country);
    $stmt->execute();
    $region_result = $stmt->get_result();

    $currency = "USD";

    if ($region_result && $region_result->num_rows > 0) {
        $region = $region_result->fetch_assoc();
        $currency = $region['currency'] ?? "USD";
    }

    // ===============================
    // INSERT WITHDRAWAL
    // ===============================
    $insert = "INSERT INTO withdrawals 
    (email, amount, currency, payment_method, paypal_email, cashapp_tag, venmo_username, zelle_name, zelle_contact, status, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, '0', NOW())";

    $stmt = $con->prepare($insert);
    $stmt->bind_param(
        "sdsssssss",
        $email,
        $amount,
        $currency,
        $payment_method,
        $paypal_email,
        $cashapp_tag,
        $venmo_username,
        $zelle_name,
        $zelle_contact
    );

    if ($stmt->execute()) {

        // deduct balance
        $new_balance = $balance - $amount;

        $update = "UPDATE users SET balance = ? WHERE email = ?";
        $stmt2 = $con->prepare($update);
        $stmt2->bind_param("ds", $new_balance, $email);
        $stmt2->execute();

        $_SESSION['success'] = "Withdrawal request submitted successfully.";
        header("Location: ../users/withdrawals.php");
        exit();

    } else {
        $_SESSION['error'] = "Failed to submit withdrawal.";
        header("Location: ../users/withdrawals.php");
        exit();
    }
}


// ===============================
// DELETE WITHDRAWAL
// ===============================
if (isset($_POST['delete'])) {

    $id = (int) $_POST['delete'];

    $stmt = $con->prepare("DELETE FROM withdrawals WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Withdrawal deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete withdrawal.";
    }

    header("Location: ../users/withdrawals.php");
    exit();
}

$con->close();
?>
