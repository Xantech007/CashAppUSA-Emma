<?php
session_start();
include('inc/header.php');
include('inc/sidebar.php');
include('inc/navbar.php');
include('../config/dbcon.php');
?>

<main id="main" class="main">

<div class="pagetitle">
    <h1>Payment Method Verifications</h1>
</div>

<div class="card">
<div class="card-body">

<div class="table-responsive">
<table class="table table-borderless">

<thead>
<tr>
    <th>User</th>
    <th>Email</th>
    <th>Method</th>
    <th>Details</th>
    <th>Amount</th>
    <th>Receipt</th>
    <th>Status</th>
    <th>Date</th>
    <th>Action</th>
</tr>
</thead>

<tbody>

<?php
$query = "SELECT p.*, u.name, u.email 
          FROM user_payment_methods p
          LEFT JOIN users u ON p.user_id = u.id
          ORDER BY p.created_at DESC";

$result = mysqli_query($con, $query);

if ($result && mysqli_num_rows($result) > 0) {

    while ($data = mysqli_fetch_assoc($result)) {

        $status = (int)$data['status'];

        $dt = new DateTime($data['created_at']);
        $date = $dt->format('d-M-Y');
?>

<tr>

    <td><?= htmlspecialchars($data['name'] ?? 'Unknown') ?></td>

    <td><?= htmlspecialchars($data['email'] ?? 'N/A') ?></td>

    <td>
        <?= htmlspecialchars(strtoupper($data['payment_method'])) ?>
    </td>

    <td>
        <?php
        $method = strtolower(trim($data['payment_method']));

        if ($method === 'paypal') {
            echo "PayPal: " . htmlspecialchars($data['paypal_email'] ?? '—');

        } elseif ($method === 'cashapp' || $method === 'cash app') {
            echo "CashApp: " . htmlspecialchars($data['cashapp_tag'] ?? '—');

        } elseif ($method === 'venmo') {
            echo "Venmo: " . htmlspecialchars($data['venmo_username'] ?? '—');

        } elseif ($method === 'zelle') {
            echo "Zelle: " . htmlspecialchars($data['zelle_name'] ?? '—') . " / " .
                 htmlspecialchars($data['zelle_contact'] ?? '—');

        } else {
            echo "<span class='text-muted'>N/A</span>";
        }
        ?>
    </td>

    <td>
        $<?= number_format($data['amount'], 2) ?>
    </td>

    <td>
        <?php if (!empty($data['receipt'])): ?>
            <a href="../<?= htmlspecialchars($data['receipt']) ?>" target="_blank">
                View
            </a>
        <?php else: ?>
            N/A
        <?php endif; ?>
    </td>

    <td>
        <?php if ($status == 0): ?>
            <span class="badge bg-warning">Pending</span>
        <?php elseif ($status == 1): ?>
            <span class="badge bg-success">Approved</span>
        <?php else: ?>
            <span class="badge bg-danger">Rejected</span>
        <?php endif; ?>
    </td>

    <td>
        <?= $date ?>
    </td>

    <td>
        <form action="codes/update-payment-status.php" method="POST">
            <input type="hidden" name="id" value="<?= $data['id'] ?>">

            <select name="status" class="form-select form-select-sm mb-1">
                <option value="0" <?= $status == 0 ? 'selected' : '' ?>>Pending</option>
                <option value="1" <?= $status == 1 ? 'selected' : '' ?>>Approve</option>
                <option value="2" <?= $status == 2 ? 'selected' : '' ?>>Reject</option>
            </select>

            <button class="btn btn-primary btn-sm">
                Update
            </button>
        </form>
    </td>

</tr>

<?php
    }

} else {
?>

<tr>
    <td colspan="9" class="text-center">
        No payment verifications found.
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
