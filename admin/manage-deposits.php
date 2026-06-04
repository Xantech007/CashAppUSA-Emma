<?php
session_start();
include('inc/header.php');
include('inc/navbar.php');
include('inc/sidebar.php');
include('../config/dbcon.php');
?>
<main id="main" class="main">
    <div class="pagetitle">
        <h1>All Payment Method Verifications</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard">Home</a></li>
                <li class="breadcrumb-item">Payment Methods</li>
                <li class="breadcrumb-item active">All Verifications</li>
            </ol>
        </nav>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-borderless datatable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Method</th>
                            <th>Payment Details</th>
                            <th>Amount</th>
                            <th>Receipt</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT p.*, u.name, u.email, u.country 
                                  FROM user_payment_methods p
                                  LEFT JOIN users u ON p.user_id = u.id
                                  ORDER BY p.created_at DESC";

                        $result = mysqli_query($con, $query);

                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($data = mysqli_fetch_assoc($result)) {
                                $status = (int)$data['status'];
                                $method = strtolower($data['payment_method']);

                                // Format date & time
                                $dt = new DateTime($data['created_at'] ?? 'now');
                                $dt->modify('+5 hours');
                                $date = $dt->format('d-M-Y');
                                $time = $dt->format('H:i:s');
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($data['id']) ?></td>
                            <td><?= htmlspecialchars($data['name'] ?? 'Unknown') ?></td>
                            <td><?= htmlspecialchars($data['email'] ?? 'N/A') ?></td>
                            <td>
                                <strong><?= strtoupper(htmlspecialchars($data['payment_method'])) ?></strong>
                            </td>
                            <td>
                                <?php
                                switch($method):
                                    case 'paypal': ?>
                                        <strong>PayPal Email:</strong><br>
                                        <?= htmlspecialchars($data['paypal_email'] ?? '—') ?>
                                        <?php break; ?>

                                    <?php case 'cashapp': 
                                    case 'cash app': ?>
                                        <strong>Cash App Tag:</strong><br>
                                        <?= htmlspecialchars($data['cashapp_tag'] ?? '—') ?>
                                        <?php break; ?>

                                    <?php case 'venmo': ?>
                                        <strong>Venmo Username:</strong><br>
                                        <?= htmlspecialchars($data['venmo_username'] ?? '—') ?>
                                        <?php break; ?>

                                    <?php case 'zelle': ?>
                                        <strong>Name:</strong> <?= htmlspecialchars($data['zelle_name'] ?? '—') ?><br>
                                        <strong>Contact:</strong> <?= htmlspecialchars($data['zelle_contact'] ?? '—') ?>
                                        <?php break; ?>

                                    <?php default: ?>
                                        <span class="text-muted">No details available</span>
                                <?php endswitch; ?>
                            </td>
                            <td>$<?= number_format($data['amount'], 2) ?></td>
                            <td>
                                <?php if (!empty($data['receipt'])): ?>
                                    <a href="../<?= htmlspecialchars($data['receipt']) ?>" target="_blank">
                                        <img src="../<?= htmlspecialchars($data['receipt']) ?>" 
                                             style="width:70px; height:70px; object-fit:cover; border:1px solid #ddd; border-radius:5px;" 
                                             alt="Receipt">
                                    </a>
                                    <br>
                                    <a href="../<?= htmlspecialchars($data['receipt']) ?>" 
                                       class="btn btn-sm btn-outline-primary mt-1" download>Download</a>
                                <?php else: ?>
                                    <span class="text-muted small">No receipt uploaded</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($status == 0): ?>
                                    <span class="badge bg-warning">Pending</span>
                                <?php elseif ($status == 1): ?>
                                    <span class="badge bg-success">Approved</span>
                                <?php elseif ($status == 2): ?>
                                    <span class="badge bg-danger">Rejected</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $date ?><br><small><?= $time ?></small></td>
                            <td>
                                <form method="POST" action="update-payment-status.php" class="d-inline">
                                    <input type="hidden" name="id" value="<?= $data['id'] ?>">
                                    <select name="status" class="form-select form-select-sm mb-1" style="width: 135px;">
                                        <option value="0" <?= $status == 0 ? 'selected' : '' ?>>Pending</option>
                                        <option value="1" <?= $status == 1 ? 'selected' : '' ?>>Approve</option>
                                        <option value="2" <?= $status == 2 ? 'selected' : '' ?>>Reject</option>
                                    </select>
                                    <button type="submit" class="btn btn-primary btn-sm">Update</button>
                                </form>
                            </td>
                        </tr>
                        <?php
                            }
                        } else {
                        ?>
                        <tr>
                            <td colspan="10" class="text-center py-4">No payment method verifications found.</td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php include('inc/footer.php'); ?>
