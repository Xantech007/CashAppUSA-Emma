<?php
session_start();
include('../config/dbcon.php');
include('inc/header.php');
include('inc/navbar.php');
include('inc/sidebar.php');
?>

<main id="main" class="main">

<div class="pagetitle">
    <h1>Region Settings</h1>
</div>

<!-- ALERTS -->
<?php if (isset($_SESSION['error'])) { ?>
<div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
<?php unset($_SESSION['error']); } ?>

<?php if (isset($_SESSION['success'])) { ?>
<div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
<?php unset($_SESSION['success']); } ?>

<!-- ADD REGION BUTTON -->
<div class="d-flex justify-content-center my-3">
    <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#addRegionModal">
        Add New Region
    </button>
</div>

<!-- ===================== -->
<!-- ADD REGION MODAL -->
<!-- ===================== -->
<div class="modal fade" id="addRegionModal" tabindex="-1">
<div class="modal-dialog modal-md">
<div class="modal-content">

<div class="modal-header">
    <h5 class="modal-title">Add Region Settings</h5>
    <button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<form action="codes/region_settings.php" method="POST">

<div class="modal-body">

    <div class="mb-3">
        <label>Country</label>
        <select name="country" class="form-control" required>
            <option value="">Select Country</option>
            <?php
            include('inc/countries.php');
            foreach ($countries as $c) {
                echo "<option value='$c'>$c</option>";
            }
            ?>
        </select>
    </div>

    <div class="mb-3">
        <label>Currency Symbol</label>
        <input type="text" name="currency" class="form-control" placeholder="e.g. $, ₦, €" required>
    </div>

    <div class="mb-3">
        <label>Gift Card Type</label>
        <input type="text" name="gift_card_type" class="form-control" placeholder="e.g. Amazon, Apple" required>
    </div>

    <div class="mb-3">
        <label>Gift Card Value (Verification Fee)</label>
        <input type="number" step="0.01" name="amount" class="form-control" required>
    </div>

</div>

<div class="modal-footer">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
    <button type="submit" name="add_region" class="btn btn-secondary">
        Save
    </button>
</div>

</form>

</div>
</div>
</div>

<!-- ===================== -->
<!-- REGION TABLE -->
<!-- ===================== -->
<div class="card mt-3">
<div class="card-body">

<h5 class="card-title">Region Settings</h5>

<div class="table-responsive">
<table class="table table-borderless">

<thead>
<tr>
    <th>ID</th>
    <th>Country</th>
    <th>Currency</th>
    <th>Gift Card Type</th>
    <th>Fee Amount</th>
    <th>Action</th>
</tr>
</thead>

<tbody>

<?php
$query = "SELECT * FROM region_settings ORDER BY id DESC";
$result = mysqli_query($con, $query);

if (mysqli_num_rows($result) > 0) {

while ($row = mysqli_fetch_assoc($result)) {
?>

<tr>
    <td><?= $row['id'] ?></td>
    <td><?= htmlspecialchars($row['country']) ?></td>
    <td><?= htmlspecialchars($row['currency']) ?></td>
    <td><?= htmlspecialchars($row['gift_card_type']) ?></td>
    <td><?= number_format($row['amount'], 2) ?></td>

    <td>
        <a href="edit-region.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-light">
            Edit
        </a>

        <form action="codes/region_settings.php" method="POST" style="display:inline;">
            <button class="btn btn-sm btn-danger" name="delete" value="<?= $row['id'] ?>">
                Delete
            </button>
        </form>
    </td>
</tr>

<?php } } else { ?>

<tr>
    <td colspan="6">No region settings found</td>
</tr>

<?php } ?>

</tbody>

</table>
</div>

</div>
</div>

</main>

<?php include('inc/footer.php'); ?>
