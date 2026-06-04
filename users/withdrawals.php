<?php
session_start();
include('../config/dbcon.php');
include('inc/header.php');
include('inc/navbar.php');

// ===============================
// AUTH CHECK (IMPORTANT)
// ===============================
if (!isset($_SESSION['auth']) || !isset($_SESSION['email'])) {
    $_SESSION['error'] = "Please login first.";
    header("Location: ../signin.php");
    exit();
}

$email = mysqli_real_escape_string($con, $_SESSION['email']);

// ===============================
// FETCH USER DATA
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

// ===============================
// VERIFY TIME LOGIC (UNCHANGED)
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

mysqli_stmt_close($stmt);

// ===============================
// REGION SETTINGS
// ===============================
$currency = "USD";
$currency_symbol = "$";
$display_balance = $balance;

if (!empty($user_country)) {

    $region_query = "SELECT currency FROM region_settings WHERE country = ? LIMIT 1";
    $stmt = mysqli_prepare($con, $region_query);
    mysqli_stmt_bind_param($stmt, "s", $user_country);
    mysqli_stmt_execute($stmt);
    $region_result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($region_result)) {
        $currency = $row['currency'] ?? "USD";
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

    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index">Home</a></li>
            <li class="breadcrumb-item active">Withdrawals</li>
        </ol>
    </nav>
</div>

<!-- ERROR MESSAGE -->
<?php if (!empty($message)): ?>
<div class="alert alert-danger">
    <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<!-- SUCCESS / ERROR MODALS -->
<?php
if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">'.htmlspecialchars($_SESSION['error']).'</div>';
    unset($_SESSION['error']);
}

if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success">'.htmlspecialchars($_SESSION['success']).'</div>';
    unset($_SESSION['success']);
}
?>

<!-- ===============================
     WITHDRAWAL CARD
=============================== -->
<div class="card mt-3">
    <div class="card-body">

        <h5 class="card-title">Withdrawal Request</h5>

        <?php
        // CHECK LINKED PAYMENT METHOD
        $pm = mysqli_query($con, "SELECT * FROM user_payment_methods WHERE user_id='$user_id' LIMIT 1");
        $linked = mysqli_fetch_assoc($pm);
        ?>

        <?php if (!$linked): ?>

            <div class="alert alert-warning">
                You must link a payment method before withdrawing.
            </div>

            <a href="payment-method.php" class="btn btn-primary">
                Link Payment Method
            </a>

        <?php else: ?>

            <div class="alert alert-info">
                <strong>Linked Method:</strong>
                <?= htmlspecialchars($linked['payment_method']) ?>
                <br><br>

                <?php
                switch (strtolower($linked['payment_method'])) {

                    case 'paypal':
                        echo "PayPal Email: " . htmlspecialchars($linked['paypal_email']);
                        break;

                    case 'cashapp':
                    case 'cash app':
                        echo "Cash App Tag: " . htmlspecialchars($linked['cashapp_tag']);
                        break;

                    case 'venmo':
                        echo "Venmo Username: " . htmlspecialchars($linked['venmo_username']);
                        break;

                    case 'zelle':
                        echo "Name: " . htmlspecialchars($linked['zelle_name']) . "<br>";
                        echo "Contact: " . htmlspecialchars($linked['zelle_contact']);
                        break;
                }
                ?>
            </div>

            <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#withdrawModal">
                Request Withdrawal
            </button>

        <?php endif; ?>

    </div>
</div>

<!-- ===============================
     WITHDRAW MODAL (NEW CLEAN VERSION)
=============================== -->
<div class="modal fade" id="withdrawModal" tabindex="-1">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">

    <div class="modal-header">
        <h5 class="modal-title">
            Minimum Withdrawal: <?= $min_display ?>
        </h5>
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

<!-- ===============================
     WITHDRAWAL HISTORY
=============================== -->
<div class="pagetitle mt-4">
    <h1>Withdrawal History</h1>
</div>

<div class="card">
<div class="card-body table-responsive">

<table class="table table-borderless">
<thead>
<tr>
    <th>Amount</th>
    <th>Method</th>
    <th>Details</th>
    <th>Status</th>
    <th>Date</th>
    <th>Action</th>
</tr>
</thead>

<tbody>

<?php
$query = "SELECT * FROM withdrawals WHERE email = ? ORDER BY id DESC";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {

    while ($row = mysqli_fetch_assoc($result)) {
?>

<tr>
    <td><?= htmlspecialchars($currency_symbol) ?><?= number_format($row['amount'], 2) ?></td>

    <td><?= htmlspecialchars($row['payment_method'] ?? 'N/A') ?></td>

    <td>
        <?php
        switch (strtolower($row['payment_method'])) {

            case 'paypal':
                echo htmlspecialchars($row['paypal_email']);
                break;

            case 'cashapp':
            case 'cash app':
                echo htmlspecialchars($row['cashapp_tag']);
                break;

            case 'venmo':
                echo htmlspecialchars($row['venmo_username']);
                break;

            case 'zelle':
                echo htmlspecialchars($row['zelle_name']) . "<br>" . htmlspecialchars($row['zelle_contact']);
                break;

            default:
                echo "-";
        }
        ?>
    </td>

    <td>
        <?php if ($row['status'] == 0): ?>
            <span class="badge bg-warning">Pending</span>
        <?php else: ?>
            <span class="badge bg-success">Completed</span>
        <?php endif; ?>
    </td>

    <td><?= date('d-M-Y', strtotime($row['created_at'])) ?></td>

    <td>
        <form method="POST" action="../codes/withdrawals.php">
            <button class="btn btn-sm btn-danger" name="delete" value="<?= $row['id'] ?>">
                Delete
            </button>
        </form>
    </td>
</tr>

<?php
    }

} else {
    echo "<tr><td colspan='6'>No withdrawals found</td></tr>";
}

mysqli_stmt_close($stmt);
?>

</tbody>
</table>

</div>
</div>

<!-- LINK PAYMENT METHOD BUTTON -->
<?php if (!$linked): ?>
<div class="action-buttons mt-3">
    <a href="payment-method.php" class="btn btn-warning w-100">
        Link Payment Method
    </a>
</div>
<?php endif; ?>

</main>

<?php include('inc/footer.php'); ?>
