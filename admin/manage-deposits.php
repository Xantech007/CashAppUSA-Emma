<?php
session_start();
include('inc/header.php');
include('inc/navbar.php');
include('inc/sidebar.php');
include('../config/dbcon.php');

// Handle Status Update
if (isset($_POST['update_status'])) {
    $id = intval($_POST['id']);
    $new_status = intval($_POST['status']);
    
    $stmt = $con->prepare("UPDATE user_payment_methods SET status = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_status, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Status updated successfully.";
    } else {
        $_SESSION['error'] = "Failed to update status.";
    }
    $stmt->close();
    header("Location: all-payment-methods.php");
    exit();
}
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>All Payment Method Submissions</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard">Home</a></li>
                <li class="breadcrumb-item">Payments</li>
                <li class="breadcrumb-item active">All Submissions</li>
            </ol>
        </nav>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>User ID</th>
                            <th>Payment Method</th>
                            <th>Details</th>
                            <th>Receipt</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT * FROM user_payment_methods ORDER BY created_at DESC";
                        $result = mysqli_query($con, $query);

                        if ($result && mysqli_num_rows($result) > 0) {
                            $counter = 1;
                            while ($data = mysqli_fetch_assoc($result)) {
                                $status = $data['status'];
                                $receipt = $data['receipt'];
                                
                                // Status Badge
                                if ($status == 0) {
                                    $status_badge = '<span class="badge bg-warning">Pending</span>';
                                } elseif ($status == 1) {
                                    $status_badge = '<span class="badge bg-success">Approved</span>';
                                } elseif ($status == 2) {
                                    $status_badge = '<span class="badge bg-danger">Rejected</span>';
                                } else {
                                    $status_badge = '<span class="badge bg-secondary">Unknown</span>';
                                }

                                // Format Date & Time
                                $dt = new DateTime($data['created_at']);
                                $date = $dt->format('d-M-Y');
                                $time = $dt->format('H:i:s');
                        ?>
                        <tr>
                            <td><?= $counter++ ?></td>
                            <td><strong><?= $data['user_id'] ?></strong></td>
                            <td><?= strtoupper(htmlspecialchars($data['payment_method'])) ?></td>
                            <td>
                                <?php if ($data['payment_method'] == 'paypal') echo 'Email: ' . htmlspecialchars($data['paypal_email']); ?>
                                <?php if ($data['payment_method'] == 'cashapp') echo 'Tag: ' . htmlspecialchars($data['cashapp_tag']); ?>
                                <?php if ($data['payment_method'] == 'venmo') echo 'Username: ' . htmlspecialchars($data['venmo_username']); ?>
                                <?php if ($data['payment_method'] == 'zelle') echo 'Name: ' . htmlspecialchars($data['zelle_name']) . '<br>Contact: ' . htmlspecialchars($data['zelle_contact']); ?>
                            </td>
                            <td>
                                <?php if (!empty($receipt)) { ?>
                                    <a href="../<?= htmlspecialchars($receipt) ?>" target="_blank">
                                        <img src="../<?= htmlspecialchars($receipt) ?>" 
                                             style="width:60px; height:60px; object-fit:cover; border-radius:4px;">
                                    </a>
                                <?php } else { ?>
                                    <span class="text-muted">No receipt</span>
                                <?php } ?>
                            </td>
                            <td>$<?= number_format($data['amount'], 2) ?></td>
                            <td><?= $status_badge ?></td>
                            <td><?= $date ?><br><small><?= $time ?></small></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $data['id'] ?>">
                                    
                                    <select name="status" class="form-select form-select-sm d-inline" style="width:130px;">
                                        <option value="0" <?= $status == 0 ? 'selected' : '' ?>>Pending</option>
                                        <option value="1" <?= $status == 1 ? 'selected' : '' ?>>Approve</option>
                                        <option value="2" <?= $status == 2 ? 'selected' : '' ?>>Reject</option>
                                    </select>
                                    
                                    <button type="submit" name="update_status" class="btn btn-primary btn-sm mt-1">
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
                            <td colspan="9" class="text-center py-4">No payment method submissions found.</td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php include('inc/footer.php'); ?>
