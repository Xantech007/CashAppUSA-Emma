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
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard">Home</a></li>
            <li class="breadcrumb-item">Users</li>
            <li class="breadcrumb-item active">All Deposits</li>
        </ol>
    </nav>
</div>

<div class="card">
<div class="card-body">

<div class="table-responsive">
<table class="table table-borderless">

<thead>
<tr>
    <th>Amount</th>
    <th>Currency</th>
    <th>Name</th>
    <th>Email</th>
    <th>Payment Proof</th>
    <th>Status</th>
    <th>Date</th>
    <th>Time</th>
    <th>Actions</th>
</tr>
</thead>

<tbody>

<?php
$query = "SELECT d.amount, d.name, d.email, d.image, d.status, d.created_at, u.id AS user_id
          FROM deposits d
          LEFT JOIN users u ON d.email = u.email
          ORDER BY d.created_at DESC";

$result = mysqli_query($con, $query);

if ($result && mysqli_num_rows($result) > 0) {

    while ($data = mysqli_fetch_assoc($result)) {

        $amount = $data['amount'];
        $name = $data['name'];
        $email = $data['email'];
        $image = $data['image'];
        $status = $data['status'];
        $user_id = $data['user_id'];

        $currency = '$';

        // ==============================
        // GET USER COUNTRY
        // ==============================
        if (!empty($email)) {

            $stmt = $con->prepare("SELECT country FROM users WHERE email = ? LIMIT 1");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $user_res = $stmt->get_result();

            if ($user_res && $user_res->num_rows > 0) {
                $user = $user_res->fetch_assoc();
                $country = $user['country'];

                // ==============================
                // GET REGION CURRENCY ONLY
                // ==============================
                $rstmt = $con->prepare("SELECT currency FROM region_settings WHERE country = ? LIMIT 1");
                $rstmt->bind_param("s", $country);
                $rstmt->execute();
                $region_res = $rstmt->get_result();

                if ($region_res && $region_res->num_rows > 0) {
                    $region = $region_res->fetch_assoc();
                    $currency = $region['currency'] ?? '$';
                }

                $rstmt->close();
            }

            $stmt->close();
        }

        // ==============================
        // TIME FORMAT
        // ==============================
        $dt = new DateTime($data['created_at']);
        $dt->modify('+5 hours');

        $date = $dt->format('d-M-Y');
        $time = $dt->format('H:i:s');
?>

<tr>

    <td><?= htmlspecialchars($currency) ?><?= number_format($amount, 2) ?></td>

    <td><?= htmlspecialchars($currency) ?></td>

    <td><?= htmlspecialchars($name) ?></td>

    <td><?= htmlspecialchars($email ?: 'No Email') ?></td>

    <td>
        <?php if (!empty($image)) { ?>
            <img src="../Uploads/<?= htmlspecialchars($image) ?>"
                 style="width:50px;height:50px">
        <?php } else { ?>
            No Image
        <?php } ?>
    </td>

    <td>
        <?php if ($status == 0) { ?>
            <span class="badge bg-warning">Pending</span>
        <?php } elseif ($status == 1) { ?>
            <span class="badge bg-danger">Rejected</span>
        <?php } else { ?>
            <span class="badge bg-success">Completed</span>
        <?php } ?>
    </td>

    <td><?= $date ?></td>
    <td><?= $time ?></td>

    <td>
        <?php if (!empty($image)) { ?>
            <a href="../Uploads/<?= htmlspecialchars($image) ?>"
               class="btn btn-light btn-sm"
               download>
                Download
            </a>
        <?php } ?>

        <?php if (!empty($user_id)) { ?>
            <a href="edit-user.php?id=<?= $user_id ?>"
               class="btn btn-light btn-sm">
                Edit
            </a>
        <?php } ?>
    </td>

</tr>

<?php
    }

} else {
?>
<tr>
    <td colspan="9" class="text-center">No deposits found</td>
</tr>
<?php } ?>

</tbody>
</table>

</div>

</div>
</div>

</main>

<?php include('inc/footer.php'); ?>
