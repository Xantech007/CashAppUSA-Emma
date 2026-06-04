<?php
session_start();
include('inc/header.php');
include('inc/sidebar.php');
include('inc/navbar.php');
include('../config/dbcon.php');
?>

<main id="main" class="main">

<div class="pagetitle">
    <h1>Pending Withdrawals</h1>
</div>

<div class="card">
<div class="card-body">

<div class="table-responsive">
<table class="table table-borderless">

<thead>
<tr>
    <th>Amount</th>
    <th>Payment Method</th>
    <th>Status</th>
    <th>Date</th>
    <th>Action</th>
</tr>
</thead>

<tbody>

<?php
$query = "SELECT id, email, amount, payment_method, status, created_at 
          FROM withdrawals 
          WHERE status = '0'
          ORDER BY id DESC";

$result = mysqli_query($con, $query);

if (mysqli_num_rows($result) > 0) {

    while ($data = mysqli_fetch_assoc($result)) {

        $email = $data['email'];

        // Get user country
        $stmt = $con->prepare("SELECT country FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user_res = $stmt->get_result();

        $currency = '$';

        if ($user_res->num_rows > 0) {
            $user = $user_res->fetch_assoc();
            $country = $user['country'];

            // Get currency from region settings
            $stmt2 = $con->prepare("SELECT currency FROM region_settings WHERE country = ? LIMIT 1");
            $stmt2->bind_param("s", $country);
            $stmt2->execute();
            $reg_res = $stmt2->get_result();

            if ($reg_res->num_rows > 0) {
                $region = $reg_res->fetch_assoc();
                $currency = $region['currency'];
            }

            $stmt2->close();
        }

        $stmt->close();
?>

<tr>
    <td>
        <?= htmlspecialchars($currency) ?>
        <?= number_format($data['amount'], 2) ?>
    </td>

    <td>
        <?= htmlspecialchars($data['payment_method'] ?: 'N/A') ?>
    </td>

    <td>
        <span class="badge bg-warning">Pending</span>
    </td>

    <td>
        <?= date('d-M-Y', strtotime($data['created_at'])) ?>
    </td>

    <td>
        <form action="codes/withdrawals.php" method="POST">
            <button class="btn btn-success btn-sm"
                    name="complete"
                    value="<?= $data['id'] ?>">
                Complete
            </button>
        </form>
    </td>
</tr>

<?php
    }
} else {
?>

<tr>
    <td colspan="5" class="text-center">
        No pending withdrawals found.
    </td>
</tr>

<?php } ?>

</tbody>

</table>
</div>

</div>
</div>

</main>

<?php include('inc/footer.php'); ?>
