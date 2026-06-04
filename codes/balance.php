<?php
session_start();
include('../config/dbcon.php');

// Check if user is logged in
if (!isset($_SESSION['auth'])) {
    $_SESSION['error'] = "Please log in to add balance.";
    header("Location: ../signin.php");
    exit(0);
}

$currency_symbol = "$";  // Default fallback

// Check if the form is submitted
if (isset($_POST['add_balance']) && isset($_POST['id']) && isset($_POST['cashtag'])) {
    
    $package_id = trim($_POST['id']);
    $cashtag = trim($_POST['cashtag']);
    $email = $_SESSION['email'];

    // Fetch user_id AND country
    $user_query = "SELECT id, country FROM users WHERE email = ? LIMIT 1";
    $stmt = $con->prepare($user_query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user_result = $stmt->get_result();

    if ($user_result && $user_result->num_rows > 0) {
        $user_data = $user_result->fetch_assoc();
        $user_id = $user_data['id'];
        $user_country = $user_data['country'];

        // Fetch dynamic currency symbol from region_settings
        if ($user_country) {
            $region_query = "SELECT currency FROM region_settings WHERE country = ? LIMIT 1";
            $region_stmt = $con->prepare($region_query);
            $region_stmt->bind_param("s", $user_country);
            $region_stmt->execute();
            $region_result = $region_stmt->get_result();

            if ($region_result && $region_row = $region_result->fetch_assoc()) {
                $currency_symbol = $region_row['currency'] ?: '$';
            }
            $region_stmt->close();
        }
        $stmt->close();

        // Fetch the package details
        $query = "SELECT max_a FROM packages WHERE id = ? AND cashtag = ? AND status = '0'";
        $stmt = $con->prepare($query);
        $stmt->bind_param("is", $package_id, $cashtag);
        $stmt->execute();
        $query_run = $stmt->get_result();

        if ($query_run && $query_run->num_rows > 0) {
            $row = $query_run->fetch_assoc();
            $amount = (float)$row['max_a'];

            // Check if CashTag has been used by this user
            $usage_query = "SELECT COUNT(*) as count FROM cashtag_usage WHERE user_id = ? AND cashtag = ?";
            $stmt = $con->prepare($usage_query);
            $stmt->bind_param("is", $user_id, $cashtag);
            $stmt->execute();
            $result = $stmt->get_result();
            $usage = $result->fetch_assoc();

            if ($usage['count'] > 0) {
                $_SESSION['error'] = "This CashTag has already been used.";
                header("Location: ../users/scan.php");
                exit(0);
            }
            $stmt->close();

            // Update the user's balance
            $update_query = "UPDATE users SET balance = balance + ? WHERE id = ?";
            $stmt = $con->prepare($update_query);
            $stmt->bind_param("di", $amount, $user_id);
            $update_success = $stmt->execute();
            $stmt->close();

            if ($update_success) {
                // Record CashTag usage
                $insert_query = "INSERT INTO cashtag_usage (user_id, cashtag) VALUES (?, ?)";
                $stmt = $con->prepare($insert_query);
                $stmt->bind_param("is", $user_id, $cashtag);
                $insert_success = $stmt->execute();
                $stmt->close();

                if ($insert_success) {
                    $_SESSION['success'] = "Balance updated successfully! Added " . 
                                           $currency_symbol . number_format($amount, 2) . ".";
                    
                    header("Location: ../users/index.php");
                    exit(0);
                } else {
                    $_SESSION['error'] = "Failed to record CashTag usage.";
                }
            } else {
                $_SESSION['error'] = "Failed to update balance.";
            }
        } else {
            $_SESSION['error'] = "Invalid or inactive package selected.";
        }
    } else {
        $_SESSION['error'] = "User not found.";
    }
} else {
    $_SESSION['error'] = "Invalid request.";
}

// Final redirect
header("Location: ../users/index.php");
exit(0);
?>
