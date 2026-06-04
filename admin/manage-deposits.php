<?php
session_start();
include('inc/header.php');
include('inc/navbar.php');
include('inc/sidebar.php');
include('../config/dbcon.php');
?>

<main id="main" class="main">

<div class="pagetitle">
    <h1>All Deposits</h1>
</div>

<div class="card">
<div class="card-body">

<div class="table-responsive">
<table class="table table-borderless">

<thead>
<tr>
    <th>Amount</th>
    <th>Name</th>
    <th>Email</th>
    <th>Proof</th>
    <th>Status</th>
    <th>Date</th>
    <th>Time</th>
    <th>Actions</th>
</tr>
</thead>

<tbody>

<?php
$query = "SELECT d.id, d.amount, d.name, d.email, d.image, d.status, d.created_at, u.id AS user_id
          FROM deposits d
          LEFT JOIN users u ON d.email = u.email
          ORDER BY d.created_at DESC";

$result = mysqli_query($con, $query);

if ($result && mysqli_num_rows($result) > 0) {

    while ($data = mysqli_fetch_assoc($result)) {

        $email = $data['email'];

        // Default currency
        $currency = '$';

        // Get user country
        if (!empty($email)) {

            $stmt = $con->prepare("SELECT country FROM users WHERE email = ? LIMIT 1");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $user_res = $stmt->get_result();

            if ($user_res->num_rows > 0) {

                $user = $user_res->fetch_assoc();
                $country = $user['country'];

                // Get region currency
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
        }

        // Time formatting (+5 hours like your original logic)
        $dateTime = new DateTime($data['created_at']);
        $dateTime->modify('+5 hours');

        $created_at = $dateTime->format('d-M-Y');
        $time = $dateTime->format('H:i:s');
?>

<tr>
    <td>
        <?= htmlspecialchars($currency) ?>
        <?= number_format($data['amount'], 2) ?>
    </td>

    <td><?= htmlspecialchars($data['name']) ?></td>

    <td><?= htmlspecialchars($data['email'] ?: 'No Email') ?></td>

    <td>
        <?php if (!empty($data['image'])) { ?>
            <img src="../Uploads/<?= htmlspecialchars($data['image']) ?>"
                 style="width:50px;height:50px">
        <?php } else { ?>
            No Image
        <?php } ?>
    </td>

    <td>
        <?php if ($data['status'] == 0) { ?>
            <span class="badge bg-warning">Pending</span>
        <?php } elseif ($data['status'] == 1) { ?>
            <span class="badge bg-danger">Rejected</span>
        <?php } else { ?>
            <span class="badge bg-success">Completed</span>
        <?php } ?>
    </td>

    <td><?= $created_at ?></td>
    <td><?= $time ?></td>

    <td>
        <?php if (!empty($data['image'])) { ?>
            <a href="../Uploads/<?= htmlspecialchars($data['image']) ?>"
               download
               class="btn btn-light btn-sm">
                Download
            </a>
        <?php } ?>

        <?php if (!empty($data['user_id'])) { ?>
            <a href="edit-user.php?id=<?= urlencode($data['user_id']) ?>"
               class="btn btn-light btn-sm">
                Edit
            </a>
        <?php } else { ?>
            <span class="text-muted">No User</span>
        <?php } ?>
    </td>
</tr>

<?php
    }
} else {
?>

<tr>
    <td colspan="8" class="text-center">No deposits found.</td>
</tr>

<?php } ?>

</tbody>

</table>
</div>

</div>
</div>

</main>

<?php include('inc/footer.php'); ?>
