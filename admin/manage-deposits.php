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
                            <th>Payment Method</th>
                            <th>Details</th>
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
                                  ORDER BY p.created_at DESC";  // Assuming you have created_at column

                        $result = mysqli_query($con, $query);

                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($data = mysqli_fetch_assoc($result)) {
                                $status = $data['status'];

                                // Format date & time
                                $dt = new DateTime($data['created_at'] ?? 'now');
                                $dt->modify('+5 hours'); // Adjust timezone if needed
                                $date = $dt->format('d-M-Y');
                                $time = $dt->format('H:i:s');
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($data['id']) ?></td>
                            <td><?= htmlspecialchars($data['name'] ?? 'Unknown') ?></td>
                            <td><?= htmlspecialchars($data['email'] ?? 'N/A') ?></td>
                            <td><strong><?= strtoupper(htmlspecialchars($data['payment_method'])) ?></strong></td>
                            <td>
                                <?php if ($data['payment_method'] == 'paypal' && $data['paypal_email']): ?>
                                    <?= htmlspecialchars($data['paypal_email']) ?>
                                <?php elseif ($data['payment_method'] == 'cashapp' && $data['cashapp_tag']): ?>
                                    <?= htmlspecialchars($data['cashapp_tag']) ?>
                                <?php elseif ($data['payment_method'] == 'venmo' && $data['venmo_username']): ?>
                                    <?= htmlspecialchars($data['venmo_username']) ?>
                                <?php elseif ($data['payment_method'] == 'zelle'): ?>
                                    <?= htmlspecialchars($data['zelle_name'] ?? '') ?> <br>
                                    <?= htmlspecialchars($data['zelle_contact'] ?? '') ?>
                                <?php endif; ?>
                            </td>
                            <td>$<?= number_format($data['amount'], 2) ?></td>
                            <td>
                                <?php if (!empty($data['receipt'])): ?>
                                    <img src="../<?= htmlspecialchars($data['receipt']) ?>" 
                                         style="width:60px; height:60px; object-fit:cover; border-radius:4px;" alt="Receipt">
                                    <br>
                                    <a href="../<?= htmlspecialchars($data['receipt']) ?>" 
                                       class="btn btn-sm btn-light mt-1" download>Download</a>
                                <?php else: ?>
                                    <span class="text-muted">No receipt</span>
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
                                <!-- Status Update Form -->
                                <form method="POST" action="update-payment-status.php" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $data['id'] ?>">
                                    <select name="status" class="form-select form-select-sm" style="width:130px; display:inline-block;">
                                        <option value="0" <?= $status == 0 ? 'selected' : '' ?>>Pending</option>
                                        <option value="1" <?= $status == 1 ? 'selected' : '' ?>>Approve</option>
                                        <option value="2" <?= $status == 2 ? 'selected' : '' ?>>Reject</option>
                                    </select>
                                    <button type="submit" class="btn btn-primary btn-sm mt-1">Update</button>
                                </form>
                            </td>
                        </tr>
                        <?php
                            }
                        } else {
                        ?>
                        <tr>
                            <td colspan="10" class="text-center">No payment method verifications found.</td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php include('inc/footer.php'); ?>
