<?php
session_start();
include('../config/dbcon.php');
include('inc/header.php');
include('inc/navbar.php');

// ===============================
// AUTH CHECK
// ===============================
if (!isset($_SESSION['auth']) || !isset($_SESSION['email'])) {
    $_SESSION['error'] = "Please login first.";
    header("Location: ../signin.php");
    exit();
}

$email = mysqli_real_escape_string($con, $_SESSION['email']);

// ===============================
// USER DATA
// ===============================
$query = "SELECT id, balance, verify, message, country, verify_time, convert_currency
          FROM users
          WHERE email = ?
          LIMIT 1";

$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) === 0) {
    $_SESSION['error'] = "User not found.";
    header("Location: ../signin.php");
    exit();
}

$user = mysqli_fetch_assoc($result);

$user_id = $user['id'];
$balance = (float)$user['balance'];
$verify = (int)$user['verify'];
$message = $user['message'] ?? '';
$user_country = $user['country'] ?? '';
$convert_currency = (int)$user['convert_currency'];

mysqli_stmt_close($stmt);

// ===============================
// VERIFY TIME LOGIC
// ===============================
if ($verify == 1 && !empty($user['verify_time'])) {

    $current_time = new DateTime('now', new DateTimeZone('Africa/Lagos'));
    $verify_time_dt = new DateTime($user['verify_time'], new DateTimeZone('Africa/Lagos'));

    $interval = $current_time->diff($verify_time_dt);
    $total_minutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;

    if ($total_minutes >= 315) {
        mysqli_query($con, "UPDATE users SET verify = 0 WHERE id = '$user_id'");
        $verify = 0;
    }
}

// ===============================
// REGION SETTINGS (simple)
// ===============================
$currency = "USD";
$currency_symbol = "$";
$display_balance = $balance;

if (!empty($user_country)) {
    $stmt = mysqli_prepare($con, "SELECT currency FROM region_settings WHERE country = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $user_country);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if ($r = mysqli_fetch_assoc($res)) {
        $currency = $r['currency'];
        $currency_symbol = $currency;
    }
    mysqli_stmt_close($stmt);
}

$min_withdrawal = 50;
$min_display = $currency_symbol . number_format($min_withdrawal, 0);

?>

<main id="main" class="main">

<div class="pagetitle">
    <h1>Available Balance: <?= htmlspecialchars($currency) ?><?= number_format($display_balance, 2) ?></h1>
</div>

<?php if (!empty($message)): ?>
<div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])) { ?>
<div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); ?></div>
<?php unset($_SESSION['error']); } ?>

<?php if (isset($_SESSION['success'])) { ?>
<div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); ?></div>
<?php unset($_SESSION['success']); } ?>

<?php
// ===============================
// PAYMENT METHOD CHECK (NEW RULE)
// 0 = pending, 1 = approved, 2 = rejected
// ===============================
$pmStmt = mysqli_prepare($con, "SELECT * FROM user_payment_methods WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($pmStmt, "i", $user_id);
mysqli_stmt_execute($pmStmt);
$pmResult = mysqli_stmt_get_result($pmStmt);
$linked = mysqli_fetch_assoc($pmResult);
mysqli_stmt_close($pmStmt);

$pm_status = $linked['status'] ?? null;
?>

<div class="card mt-3">
<div class="card-body">

<h5 class="card-title">Withdrawal Request</h5>

<?php if (!$linked || $pm_status == 0 || $pm_status == 2): ?>

    <div class="alert alert-warning">
        <?php if (!$linked): ?>
            No payment method linked.
        <?php elseif ($pm_status == 0): ?>
            Your payment method is pending approval.
        <?php elseif ($pm_status == 2): ?>
            Your payment method was rejected. Please re-link.
        <?php endif; ?>
    </div>

    <a href="payment-method.php" class="btn btn-primary">
        Link Payment Method
    </a>

<?php else: ?>

    <div class="alert alert-info">
        <strong>Approved Payment Method:</strong>
        <?= htmlspecialchars($linked['payment_method']) ?>
    </div>

    <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#withdrawModal">
        Request Withdrawal
    </button>

<?php endif; ?>

</div>
</div>

<!-- WITHDRAW MODAL -->
<div class="modal fade" id="withdrawModal" tabindex="-1">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">

<div class="modal-header">
    <h5 class="modal-title">Minimum Withdrawal: <?= $min_display ?></h5>
    <button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<form action="../codes/withdrawals.php" method="POST">

    <input type="hidden" name="email" value="<?= htmlspecialchars($_SESSION['email']) ?>">
    <input type="hidden" name="balance" value="<?= $balance ?>">

    <div class="mb-3">
        <label>Amount</label>
        <input type="number" name="amount" class="form-control"
               min="<?= $min_withdrawal ?>" required>
    </div>

    <button class="btn btn-primary" name="withdraw">
        Submit Withdrawal
    </button>

</form>

</div>

</div>
</div>
</div>

<!-- HISTORY -->
<div class="card mt-4">
<div class="card-body table-responsive">

<table class="table table-borderless">
<thead>
<tr>
    <th>Amount</th>
    <th>Method</th>
    <th>Status</th>
    <th>Date</th>
</tr>
</thead>

<tbody>

<?php
$stmt = mysqli_prepare($con, "SELECT * FROM withdrawals WHERE email = ? ORDER BY id DESC");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($res)) { ?>

<tr>
    <td><?= $currency_symbol . number_format($row['amount'], 2) ?></td>
    <td><?= htmlspecialchars($row['payment_method'] ?? '-') ?></td>
    <td>
        <?= $row['status'] == 0
            ? '<span class="badge bg-warning">Pending</span>'
            : '<span class="badge bg-success">Completed</span>'; ?>
    </td>
    <td><?= date('d-M-Y', strtotime($row['created_at'])) ?></td>
</tr>

<?php } mysqli_stmt_close($stmt); ?>

</tbody>
</table>

</div>
</div>

</main>

<?php include('inc/footer.php'); ?>
