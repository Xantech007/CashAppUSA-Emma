<?php
session_start();
include('../config/dbcon.php');
include('inc/header.php');
include('inc/navbar.php');

if (!isset($_SESSION['auth']) || !isset($_SESSION['email'])) {
    header("Location: ../signin.php");
    exit();
}

$email = mysqli_real_escape_string($con, $_SESSION['email']);

// Get User ID and Country
$userQuery = mysqli_query($con, "SELECT id, country FROM users WHERE email='$email' LIMIT 1");
$user = mysqli_fetch_assoc($userQuery);
$user_id = $user['id'] ?? null;
$user_country = $user['country'] ?? null;

if (!$user_id) {
    $_SESSION['error'] = "User not found.";
    header("Location: ../signin.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| RECEIVE FORM DATA
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['submit_receipt'])) {
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
$payment_method = $data['verification_method'] ?? '';
$paypal_email_user = $data['paypal_email'] ?? null;
$cashapp_tag = $data['cashapp_tag'] ?? null;
$venmo_username = $data['venmo_username'] ?? null;
$zelle_name = $data['zelle_name'] ?? null;
$zelle_contact = $data['zelle_contact'] ?? null;

/*
|--------------------------------------------------------------------------
| FETCH COUNTRY-SPECIFIC SETTINGS FROM region_settings
|--------------------------------------------------------------------------
*/
$settingQuery = mysqli_query($con, 
    "SELECT payment_amount, payment_method, gift_card_type 
     FROM region_settings 
     WHERE country = '" . mysqli_real_escape_string($con, $user_country) . "' 
     LIMIT 1"
);

$setting = mysqli_fetch_assoc($settingQuery);

// Fallback if no country-specific settings found
if (!$setting) {
    $setting = [
        'payment_amount' => 50.00,   // Default fallback
        'payment_method' => 'Gift Card',
        'gift_card_type' => 'Amazon Gift Card'
    ];
}

$amount = (float)($setting['payment_amount'] ?? 50.00);
$gift_card_type = $setting['gift_card_type'] ?? 'Gift Card';

/*
|--------------------------------------------------------------------------
| SAVE RECEIPT
|--------------------------------------------------------------------------
*/
if (isset($_POST['submit_receipt'])) {
    $receipt = '';
    if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] == 0) {
        $upload_dir = "../uploads/payment-receipts/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $ext = strtolower(pathinfo($_FILES['receipt']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','pdf','webp'];

        if (!in_array($ext, $allowed)) {
            $_SESSION['error'] = "Invalid receipt file type.";
            header("Location: link-payment-method.php");
            exit();
        }

        $filename = time() . '_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['receipt']['tmp_name'], $upload_dir . $filename);
        $receipt = "uploads/payment-receipts/" . $filename;
    }

    // Delete old receipt if exists
    $oldQuery = mysqli_query($con, "SELECT receipt FROM user_payment_methods WHERE user_id='$user_id' LIMIT 1");
    if ($old = mysqli_fetch_assoc($oldQuery)) {
        if (!empty($old['receipt'])) {
            $oldFile = "../" . $old['receipt'];
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }
    }

    // Delete old record and insert new one
    mysqli_query($con, "DELETE FROM user_payment_methods WHERE user_id='$user_id'");

    $payment_method   = mysqli_real_escape_string($con, $payment_method);
    $paypal_email_user = mysqli_real_escape_string($con, $paypal_email_user);
    $cashapp_tag      = mysqli_real_escape_string($con, $cashapp_tag);
    $venmo_username   = mysqli_real_escape_string($con, $venmo_username);
    $zelle_name       = mysqli_real_escape_string($con, $zelle_name);
    $zelle_contact    = mysqli_real_escape_string($con, $zelle_contact);
    $receipt          = mysqli_real_escape_string($con, $receipt);

    mysqli_query($con, "
        INSERT INTO user_payment_methods 
        (user_id, payment_method, paypal_email, cashapp_tag, venmo_username, 
         zelle_name, zelle_contact, receipt, amount, status)
        VALUES 
        ('$user_id', '$payment_method', '$paypal_email_user', '$cashapp_tag', 
         '$venmo_username', '$zelle_name', '$zelle_contact', '$receipt', 
         '$amount', '0')
    ");

    unset($_SESSION['payment_method_data']);
   
    $_SESSION['success'] = "Payment receipt submitted successfully. Your payment is under review.";
   
    header("Location: withdrawals.php");
    exit();
}
?>

<main id="main" class="main">
<div class="pagetitle">
    <h1>Link Payment Method Verification</h1>
</div>

<div class="container">
    <div class="card">
        <div class="card-body p-4">
            <h5 class="mb-4">Payment Method Linking Fee Instructions</h5>
            
            <div class="alert alert-info">
                <h5 class="mb-3">Payment Method Verification Fee</h5>
                
                <p>
                    To complete the linking of your preferred withdrawal payment method,
                    a one-time verification fee is required.
                </p>
                
                <hr>
                
                <p class="mb-2">
                    <strong>Required Gift Card Type:</strong> 
                    <?= htmlspecialchars($gift_card_type); ?>
                </p>
                
                <p class="mb-2">
                    <strong>Required Amount:</strong> 
                    $<?= number_format($amount, 2); ?>
                </p>
                
                <hr>
                
                <h6>Instructions</h6>
                <ol class="mb-3">
                    <li>Purchase a valid <strong><?= htmlspecialchars($gift_card_type); ?></strong> worth <strong>$<?= number_format($amount, 2); ?></strong>.</li>
                    <li>Ensure the gift card is unused and valid.</li>
                    <li>Take a clear photo/screenshot of the gift card and receipt.</li>
                    <li>Upload the image below for verification.</li>
                    <li>Once approved, your payment method will be linked.</li>
                </ol>

                <div class="alert alert-warning mb-0">
                    <strong>Important:</strong> Blurry, expired, or mismatched submissions will be rejected.
                </div>
            </div>

            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label fw-bold">Upload Gift Card Image / Receipt</label>
                    <input type="file" name="receipt" class="form-control" 
                           accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                    <small class="text-muted">JPG, JPEG, PNG, WEBP, PDF only.</small>
                </div>
                <button type="submit" name="submit_receipt" class="btn btn-primary">
                    Submit for Verification
                </button>
            </form>
        </div>
    </div>
</div>
</main>

<?php include('inc/footer.php'); ?>
