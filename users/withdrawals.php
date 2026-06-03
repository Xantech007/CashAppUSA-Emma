<?php
session_start();
include('../config/dbcon.php');
include('inc/header.php');
include('inc/navbar.php');

$email = mysqli_real_escape_string($con, $_SESSION['email']);

/*
|--------------------------------------------------------------------------
| FETCH USER
|--------------------------------------------------------------------------
*/
$userQuery = mysqli_query(
    $con,
    "SELECT id, balance, message, country, convert_currency
     FROM users
     WHERE email='$email'
     LIMIT 1"
);

$user = mysqli_fetch_assoc($userQuery);

if (!$user) {
    $_SESSION['error'] = "User not found.";
    header("Location: ../signin.php");
    exit();
}

$user_id = $user['id'];
$balance = (float)$user['balance'];
$message = $user['message'] ?? '';
$user_country = $user['country'] ?? '';
$convert_currency = (int)($user['convert_currency'] ?? 0);

/*
|--------------------------------------------------------------------------
| FETCH PAYMENT METHOD
|--------------------------------------------------------------------------
*/
$paymentQuery = mysqli_query(
    $con,
    "SELECT *
     FROM user_payment_methods
     WHERE user_id='$user_id'
     LIMIT 1"
);

$link_status = -1;
$payment_method = null;
$display_label = '';
$display_value = '';

if ($row = mysqli_fetch_assoc($paymentQuery)) {

    $link_status = (int)$row['status'];
    $payment_method = $row['payment_method'];

    if ($link_status == 2) { // APPROVED ONLY

        if ($payment_method == 'PayPal') {
            $display_label = "PayPal Email";
            $display_value = $row['paypal_email'];
        }

        elseif ($payment_method == 'Cash App') {
            $display_label = "Cash App";
            $display_value = $row['cashapp_tag'];
        }

        elseif ($payment_method == 'Venmo') {
            $display_label = "Venmo Username";
            $display_value = $row['venmo_username'];
        }

        elseif ($payment_method == 'Zelle') {
            $display_label = "Zelle";
            $display_value = $row['zelle_name'] . " - " . $row['zelle_contact'];
        }
    }
}

/*
|--------------------------------------------------------------------------
| REGION SETTINGS (UNCHANGED LOGIC)
|--------------------------------------------------------------------------
*/
$rate = 1.0;
$display_balance = $balance;
$currency_symbol = '$';
$currency = 'USD';

if ($convert_currency === 1 && !empty($user_country)) {

    $region_query = "SELECT rate, currency FROM region_settings WHERE country='$user_country' LIMIT 1";
    $region_result = mysqli_query($con, $region_query);

    if ($region = mysqli_fetch_assoc($region_result)) {
        $rate = (float)$region['rate'];
        if ($rate <= 0) $rate = 1.0;

        $display_balance = round($balance * $rate, 2);
        $currency = $region['currency'];
        $currency_symbol = $currency;
    }
}

$min_withdrawal = 50;
?>

<main id="main" class="main">

<div class="pagetitle">
    <h1>Available Balance: <?= htmlspecialchars($currency) ?><?= number_format($display_balance, 2) ?></h1>
</div>

<?php if (!empty($message)): ?>
<div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<?php if ($link_status == -1): ?>

    <a href="payment-method.php" class="btn btn-warning">
        Link Payment Method First
    </a>

<?php elseif ($link_status == 1): ?>

    <button class="btn btn-secondary" disabled>
        Payment Method Pending Approval
    </button>

<?php elseif ($link_status == 2): ?>

    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#withdrawModal">
        Request Withdrawal
    </button>

<?php endif; ?>


<!-- ================= WITHDRAW MODAL ================= -->
<div class="modal fade" id="withdrawModal" tabindex="-1">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">

<div class="modal-header">
    <h5 class="modal-title">Withdraw Funds</h5>
</div>

<div class="modal-body">

    <form action="../codes/withdrawals.php" method="POST" id="form">

        <input type="number" name="amount" class="form-control"
               min="<?= $min_withdrawal ?>" required>

        <hr>

        <!-- ONLY ONE CLEAN DISPLAY -->
        <p>
            <strong><?= htmlspecialchars($display_label) ?>:</strong><br>
            <?= htmlspecialchars($display_value) ?>
        </p>

        <input type="hidden" name="payment_method" value="<?= htmlspecialchars($payment_method) ?>">
        <input type="hidden" name="payment_value" value="<?= htmlspecialchars($display_value) ?>">
        <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">

    </form>

</div>

<div class="modal-footer">
    <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
    <button class="btn btn-success" form="form" name="withdraw">Submit</button>
</div>

</div>
</div>
</div>


<!-- ================= HISTORY ================= -->
<div class="card mt-4">
<div class="card-body">

<table class="table">
<thead>
<tr>
    <th>Amount</th>
    <th>Payment Method</th>
    <th>Details</th>
    <th>Status</th>
    <th>Date</th>
</tr>
</thead>

<tbody>

<?php
$q = mysqli_query($con,
"SELECT amount, payment_method, channel, status, created_at
 FROM withdrawals
 WHERE email='$email'
 ORDER BY id DESC");

while ($r = mysqli_fetch_assoc($q)) { ?>
<tr>
    <td><?= $currency_symbol . number_format($r['amount'],2) ?></td>
    <td><?= htmlspecialchars($r['payment_method']) ?></td>
    <td><?= htmlspecialchars($r['channel']) ?></td>
    <td>
        <?= $r['status']==0 ? 'Pending' : 'Completed' ?>
    </td>
    <td><?= $r['created_at'] ?></td>
</tr>
<?php } ?>

</tbody>
</table>

</div>
</div>

</main>

<?php include('inc/footer.php'); ?>
