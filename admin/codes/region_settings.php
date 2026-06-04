<?php
session_start();
include('../../config/dbcon.php');
include('../inc/countries.php');

// ===============================
// AUTH CHECK
// ===============================
if (!isset($_SESSION['id'])) {
    $_SESSION['error'] = "Please log in to access this page.";
    header("Location: ../signin.php");
    exit();
}

$admin_id = $_SESSION['id'];

// ===============================
// ADD REGION
// ===============================
if (isset($_POST['add_region'])) {

    $country = mysqli_real_escape_string($con, $_POST['country'] ?? '');
    $currency = mysqli_real_escape_string($con, $_POST['currency'] ?? '');
    $gift_card_type = mysqli_real_escape_string($con, $_POST['gift_card_type'] ?? '');
    $amount = mysqli_real_escape_string($con, $_POST['amount'] ?? '');

    // Validation
    if (empty($country) || empty($currency) || empty($gift_card_type) || empty($amount)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: ../region_settings.php");
        exit();
    }

    if (!isset($countries) || !in_array($country, $countries)) {
        $_SESSION['error'] = "Invalid country selected.";
        header("Location: ../region_settings.php");
        exit();
    }

    if (!is_numeric($amount) || $amount <= 0) {
        $_SESSION['error'] = "Amount must be a positive number.";
        header("Location: ../region_settings.php");
        exit();
    }

    // Prevent duplicates
    $check = $con->prepare("SELECT id FROM region_settings WHERE country = ? LIMIT 1");
    $check->bind_param("s", $country);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows > 0) {
        $_SESSION['error'] = "Region already exists for this country.";
        header("Location: ../region_settings.php");
        exit();
    }

    // Insert
    $stmt = $con->prepare("
        INSERT INTO region_settings (country, currency, gift_card_type, amount)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->bind_param("sssd", $country, $currency, $gift_card_type, $amount);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Region added successfully.";
    } else {
        $_SESSION['error'] = "Failed to add region.";
    }

    header("Location: ../region_settings.php");
    exit();
}

// ===============================
// UPDATE REGION
// ===============================
if (isset($_POST['update_region'])) {

    $region_id = (int)($_POST['region_id'] ?? 0);
    $country = mysqli_real_escape_string($con, $_POST['country'] ?? '');
    $currency = mysqli_real_escape_string($con, $_POST['currency'] ?? '');
    $gift_card_type = mysqli_real_escape_string($con, $_POST['gift_card_type'] ?? '');
    $amount = mysqli_real_escape_string($con, $_POST['amount'] ?? '');

    if (!$region_id || empty($country) || empty($currency) || empty($gift_card_type) || empty($amount)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: ../edit-region.php?id=$region_id");
        exit();
    }

    if (!is_numeric($amount) || $amount <= 0) {
        $_SESSION['error'] = "Invalid amount.";
        header("Location: ../edit-region.php?id=$region_id");
        exit();
    }

    // Duplicate check
    $check = $con->prepare("SELECT id FROM region_settings WHERE country = ? AND id != ? LIMIT 1");
    $check->bind_param("si", $country, $region_id);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows > 0) {
        $_SESSION['error'] = "Another region already exists for this country.";
        header("Location: ../edit-region.php?id=$region_id");
        exit();
    }

    $stmt = $con->prepare("
        UPDATE region_settings
        SET country = ?, currency = ?, gift_card_type = ?, amount = ?
        WHERE id = ?
    ");

    $stmt->bind_param("sssdi", $country, $currency, $gift_card_type, $amount, $region_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Region updated successfully.";
        header("Location: ../region_settings.php");
    } else {
        $_SESSION['error'] = "Failed to update region.";
        header("Location: ../edit-region.php?id=$region_id");
    }

    exit();
}

// ===============================
// DELETE REGION
// ===============================
if (isset($_POST['delete'])) {

    $region_id = (int)$_POST['delete'];

    $stmt = $con->prepare("DELETE FROM region_settings WHERE id = ?");
    $stmt->bind_param("i", $region_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Region deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete region.";
    }

    header("Location: ../region_settings.php");
    exit();
}

// ===============================
// INVALID REQUEST
// ===============================
$_SESSION['error'] = "Invalid request.";
header("Location: ../region_settings.php");
exit();
?>
