<?php
session_start();
include('../../config/dbcon.php');

if (isset($_POST['add_payment_method'])) {

    $method_name = mysqli_real_escape_string($con, trim($_POST['method_name']));
    $description = mysqli_real_escape_string($con, trim($_POST['description']));
    $sort_order = (int)$_POST['sort_order'];
    $status = isset($_POST['status']) ? 1 : 0;

    if (empty($method_name)) {
        $_SESSION['error'] = "Method name is required.";
        header("Location: ../manage-payment-methods.php");
        exit();
    }

    $icon_path = '';

    if (isset($_FILES['icon']) && $_FILES['icon']['error'] == 0) {

        $upload_dir = "../../uploads/payment-icons/";

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $extension = strtolower(pathinfo($_FILES['icon']['name'], PATHINFO_EXTENSION));

        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($extension, $allowed)) {
            $_SESSION['error'] = "Invalid image format.";
            header("Location: ../manage-payment-methods.php");
            exit();
        }

        $filename = time() . '_' . uniqid() . '.' . $extension;

        if (move_uploaded_file(
            $_FILES['icon']['tmp_name'],
            $upload_dir . $filename
        )) {
            $icon_path = "uploads/payment-icons/" . $filename;
        }
    }

    $query = "INSERT INTO payment_method
    (
        method_name,
        icon,
        description,
        sort_order,
        status
    )
    VALUES
    (
        '$method_name',
        '$icon_path',
        '$description',
        '$sort_order',
        '$status'
    )";

    if (mysqli_query($con, $query)) {
        $_SESSION['success'] = "Payment method added successfully.";
    } else {
        $_SESSION['error'] = "Failed to add payment method.";
    }

    header("Location: ../manage-payment-methods.php");
    exit();
}



if (isset($_POST['delete_payment_method'])) {

    $id = (int)$_POST['delete_payment_method'];

    $query = mysqli_query(
        $con,
        "SELECT icon FROM payment_method WHERE id='$id' LIMIT 1"
    );

    if ($row = mysqli_fetch_assoc($query)) {

        if (!empty($row['icon'])) {

            $file = "../../" . $row['icon'];

            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    mysqli_query(
        $con,
        "DELETE FROM payment_method WHERE id='$id'"
    );

    $_SESSION['success'] = "Payment method deleted successfully.";

    header("Location: ../manage-payment-methods.php");
    exit();
}
?>
