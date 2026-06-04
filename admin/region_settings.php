<?php
session_start();
include('../config/dbcon.php');
include('inc/header.php');
include('inc/navbar.php');
include('inc/sidebar.php');
?>

<main id="main" class="main">

<div class="pagetitle">
    <h1>Payment Link Settings</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard">Home</a></li>
            <li class="breadcrumb-item">Settings</li>
            <li class="breadcrumb-item active">Payment Link Settings</li>
        </ol>
    </nav>
</div>

<!-- Messages -->
<?php if (isset($_SESSION['error'])) { ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
<?php unset($_SESSION['error']); } ?>

<?php if (isset($_SESSION['success'])) { ?>
    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
<?php unset($_SESSION['success']); } ?>

<style>
.add-btn {
    display:flex;
    justify-content:center;
    margin:15px 0;
}
.form-control {
    border:1px solid #ccc;
    padding:10px;
    border-radius:5px;
    width:100%;
}
.form-control:focus {
    border-color:#0d6efd;
    box-shadow:0 0 5px rgba(13,110,253,0.3);
}
</style>

<!-- ADD MODAL -->
<div class="add-btn">
    <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#addModal">
        Add Payment Link Setting
    </button>
</div>

<div class="modal fade" id="addModal" tabindex="-1">
<div class="modal-dialog modal-lg">
<div class="modal-content">

<div class="modal-header">
    <h5 class="modal-title">Add Payment Link Setting</h5>
    <button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<form action="codes/payment_link_settings.php" method="POST">

    <div class="row">

        <div class="col-md-6">
            <label>Payment Method</label>
            <input type="text" name="payment_method" class="form-control" required>
        </div>

        <div class="col-md-6">
            <label>Payment Amount (Fee)</label>
            <input type="number" step="0.01" name="amount" class="form-control" required>
        </div>

    </div>

    <br>

    <button class="btn btn-secondary" name="add">
        Save
    </button>

</form>

</div>

</div>
</div>
</div>

<!-- TABLE -->
<div class="card">
<div class="card-body">

<h5 class="card-title">Payment Link Settings</h5>

<div class="table-responsive">

<table class="table table-borderless">

<thead>
<tr>
    <th>ID</th>
    <th>Payment Method</th>
    <th>Amount</th>
    <th>Created</th>
    <th>Updated</th>
    <th>Action</th>
</tr>
</thead>

<tbody>

<?php
$query = "SELECT * FROM payment_link_settings ORDER BY id DESC";
$result = mysqli_query($con, $query);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
?>

<tr>
    <td><?= $row['id'] ?></td>
    <td><?= htmlspecialchars($row['payment_method']) ?></td>
    <td><?= number_format($row['amount'], 2) ?></td>
    <td><?= $row['created_at'] ?></td>
    <td><?= $row['updated_at'] ?></td>

    <td>
        <form action="codes/payment_link_settings.php" method="POST">
            <button class="btn btn-danger btn-sm" name="delete" value="<?= $row['id'] ?>">
                Delete
            </button>
        </form>
    </td>
</tr>

<?php
    }
} else {
    echo "<tr><td colspan='6'>No records found</td></tr>";
}
?>

</tbody>

</table>

</div>

</div>
</div>

</main>

<?php include('inc/footer.php'); ?>
