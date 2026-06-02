<?php
session_start();
include('../../config/dbcon.php');

if (isset($_POST['update_payment_method'])) {

    $id = (int)$_POST['id'];

    $method_name = mysqli_real_escape_string($con, $_POST['method_name']);
    $description = mysqli_real_escape_string($con, $_POST['description']);
    $sort_order = (int)$_POST['sort_order'];
    $status = isset($_POST['status']) ? 1 : 0;

    $icon = $_POST['old_icon'];

    if (
        isset($_FILES['icon']) &&
        $_FILES['icon']['error'] == 0
    ) {

        $upload_dir = "../../uploads/payment-icons/";

        $ext = strtolower(
            pathinfo(
                $_FILES['icon']['name'],
                PATHINFO_EXTENSION
            )
        );

        $new_file = time() . '_' . uniqid() . '.' . $ext;

        move_uploaded_file(
            $_FILES['icon']['tmp_name'],
            $upload_dir . $new_file
        );

        if (!empty($_POST['old_icon'])) {

            $old = "../../" . $_POST['old_icon'];

            if (file_exists($old)) {
                unlink($old);
            }
        }

        $icon = "uploads/payment-icons/" . $new_file;
    }

    mysqli_query(
        $con,
        "UPDATE payment_method SET
        method_name='$method_name',
        icon='$icon',
        description='$description',
        sort_order='$sort_order',
        status='$status'
        WHERE id='$id'"
    );

    $_SESSION['success'] = "Payment method updated successfully.";

    header("Location: ../manage-payment-methods.php");
    exit();
}
?>
