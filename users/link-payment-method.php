<?php
session_start();
include('../config/dbcon.php');
include('inc/header.php');
include('inc/navbar.php');


if (!isset($_SESSION['auth'])) {
    header("Location: ../signin.php");
    exit();
}

$email = mysqli_real_escape_string($con, $_SESSION['email']);
if (!isset($_SESSION['auth'])) {
    header("Location: ../signin.php");
    exit();
}

$email = mysqli_real_escape_string($con, $_SESSION['email']);

$userQuery = mysqli_query(
    $con,
    "SELECT id FROM users WHERE email='$email' LIMIT 1"
);

$user = mysqli_fetch_assoc($userQuery);

$user_id = $user['id'];

/*
|--------------------------------------------------------------------------
| RECEIVE FORM DATA
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] == 'POST' &&
    !isset($_POST['submit_receipt'])
) {

    $_SESSION['payment_method_data'] = $_POST;

    header("Location: link-payment-method.php");
    exit();
}

if (!isset($_SESSION['payment_method_data'])) {

    $_SESSION['error'] = "Please select a payment method first.";

    header("Location: payment-method.php");
    exit();
}

$data = $_SESSION['payment_method_data'];

$payment_method = $data['verification_method'];

$paypal_email_user = $data['paypal_email'] ?? null;
$cashapp_tag = $data['cashapp_tag'] ?? null;
$venmo_username = $data['venmo_username'] ?? null;
$zelle_name = $data['zelle_name'] ?? null;
$zelle_contact = $data['zelle_contact'] ?? null;

/*
|--------------------------------------------------------------------------
| FETCH PAYMENT SETTINGS
|--------------------------------------------------------------------------
*/

$settingQuery = mysqli_query(
    $con,
    "SELECT * FROM payment_link_settings LIMIT 1"
);

$setting = mysqli_fetch_assoc($settingQuery);

$gift_card_type = $setting['gift_card_type'];
$amount = $setting['amount'];

/*
|--------------------------------------------------------------------------
| SAVE RECEIPT
|--------------------------------------------------------------------------
*/

if (isset($_POST['submit_receipt'])) {

    $receipt = '';

    if (
        isset($_FILES['receipt']) &&
        $_FILES['receipt']['error'] == 0
    ) {

        $upload_dir = "../uploads/payment-receipts/";

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $ext = strtolower(
            pathinfo(
                $_FILES['receipt']['name'],
                PATHINFO_EXTENSION
            )
        );

        $allowed = ['jpg','jpeg','png','pdf','webp'];

        if (!in_array($ext, $allowed)) {

            $_SESSION['error'] = "Invalid receipt file.";

            header("Location: link-payment-method.php");
            exit();
        }

        $filename =
            time() . '_' .
            uniqid() . '.' .
            $ext;

        move_uploaded_file(
            $_FILES['receipt']['tmp_name'],
            $upload_dir . $filename
        );

        $receipt =
            "uploads/payment-receipts/" .
            $filename;
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE OLD RECEIPT IF EXISTS
    |--------------------------------------------------------------------------
    */

    $oldQuery = mysqli_query(
        $con,
        "SELECT receipt
        FROM user_payment_methods
        WHERE user_id='$user_id'
        LIMIT 1"
    );

    if ($old = mysqli_fetch_assoc($oldQuery)) {

        if (!empty($old['receipt'])) {

            $oldFile = "../" . $old['receipt'];

            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | REPLACE USER RECORD
    |--------------------------------------------------------------------------
    */

    mysqli_query(
        $con,
        "DELETE FROM user_payment_methods
        WHERE user_id='$user_id'"
    );

    $payment_method =
        mysqli_real_escape_string(
            $con,
            $payment_method
        );

    $paypal_email_user =
        mysqli_real_escape_string(
            $con,
            $paypal_email_user
        );

    $cashapp_tag =
        mysqli_real_escape_string(
            $con,
            $cashapp_tag
        );

    $venmo_username =
        mysqli_real_escape_string(
            $con,
            $venmo_username
        );

    $zelle_name =
        mysqli_real_escape_string(
            $con,
            $zelle_name
        );

    $zelle_contact =
        mysqli_real_escape_string(
            $con,
            $zelle_contact
        );

    mysqli_query(
        $con,
        "INSERT INTO user_payment_methods
        (
            user_id,
            payment_method,
            paypal_email,
            cashapp_tag,
            venmo_username,
            zelle_name,
            zelle_contact,
            receipt,
            amount,
            status
        )
        VALUES
        (
            '$user_id',
            '$payment_method',
            '$paypal_email_user',
            '$cashapp_tag',
            '$venmo_username',
            '$zelle_name',
            '$zelle_contact',
            '$receipt',
            '$amount',
            '0'
        )"
    );

    unset($_SESSION['payment_method_data']);
    
    $_SESSION['success'] =
        "Payment receipt submitted successfully. Your payment is under review.";
    
    header("Location: withdrawals.php");
    exit();
}
?>

<main id="main" class="main">

<div class="pagetitle">
    <h1>Complete Payment</h1>
</div>

<div class="container">

    <div class="card">

        <div class="card-body p-4">

            <h5 class="mb-4">
                Gift Card Payment Instructions
            </h5>

            <div class="alert alert-info">

                <p>
                    Purchase a
                    <strong>$<?= number_format($amount, 2); ?></strong>
                    gift card.
                </p>

                <p>
                    <strong>Gift Card Type:</strong>
                    <?= htmlspecialchars($gift_card_type); ?>
                </p>

                <p>
                    Upload a clear image of the gift card receipt or card details below for verification.
                </p>

            </div>

            <form method="POST" enctype="multipart/form-data">

                <div class="mb-3">

                    <label class="form-label">
                        Upload Gift Card Receipt / Image
                    </label>

                    <input type="file"
                           name="receipt"
                           class="form-control"
                           required>

                </div>

                <button type="submit"
                        name="submit_receipt"
                        class="btn btn-primary">
                    Submit Gift Card
                </button>

            </form>

        </div>

    </div>

</div>

</main>

<?php include('inc/footer.php'); ?>
