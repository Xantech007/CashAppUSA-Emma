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

<!-- ===================== -->
<!-- ALERTS -->
<!-- ===================== -->
<?php if (isset($_SESSION['error'])) { ?>
<div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
<?php unset($_SESSION['error']); } ?>

<?php if (isset($_SESSION['success'])) { ?>
<div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
<?php unset($_SESSION['success']); } ?>

<style>
.form-control {
    border: 1px solid #ccc;
    padding: 10px;
}
.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 5px rgba(13,110,253,0.3);
}
</style>

<!-- ===================== -->
<!-- ADD REGION MODAL -->
<!-- ===================== -->
<div class="d-flex justify-content-center my-3">
    <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#addRegionModal">
        Add New Region
    </button>
</div>

<div class="modal fade" id="addRegionModal" tabindex="-1">
<div class="modal-dialog modal-lg">
<div class="modal-content">

<div class="modal-header">
    <h5 class="modal-title">Add Region</h5>
    <button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<form action="codes/region_settings.php" method="POST">

<div class="modal-body">

    <div class="row">
        <div class="col-md-6">
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

        <div class="col-md-6">
            <label>Currency</label>
            <input type="text" name="currency" class="form-control" required>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-6">
            <label>Gift Card Type</label>
            <input type="text" name="gift_card_type" class="form-control" required>
        </div>

        <div class="col-md-6">
            <label>Gift Card Amount</label>
            <input type="number" step="0.01" name="amount" class="form-control" required>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-6">
            <label>Payment Fee (Link Fee)</label>
            <input type="number" step="0.01" name="payment_amount" class="form-control" required>
        </div>

        <div class="col-md-6">
            <label>Rate</label>
            <input type="number" step="0.01" name="rate" class="form-control" required>
        </div>
    </div>

    <hr>

    <!-- PAYMENT LINK SETTINGS -->
    <h6>Payment Link Settings</h6>

    <?php
    $pls = mysqli_query($con, "SELECT * FROM payment_link_settings ORDER BY id DESC");
    ?>

    <div class="row mt-2">
        <div class="col-md-12">
            <label>Select Payment Link Package</label>
            <select name="payment_link_id" class="form-control">
                <option value="">None</option>
                <?php while ($p = mysqli_fetch_assoc($pls)) { ?>
                    <option value="<?= $p['id'] ?>">
                        <?= htmlspecialchars($p['name']) ?> - <?= number_format($p['amount'], 2) ?>
                    </option>
                <?php } ?>
            </select>
        </div>
    </div>

</div>

<div class="modal-footer">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
    <button type="submit" name="add_region" class="btn btn-secondary">
        Save Region
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
    <th>Gift Card</th>
    <th>Gift Amount</th>
    <th>Payment Fee</th>
    <th>Rate</th>
    <th>Payment Link</th>
    <th>Action</th>
</tr>
</thead>

<tbody>

<?php
$query = "SELECT r.*, p.name AS link_name, p.amount AS link_amount
          FROM region_settings r
          LEFT JOIN payment_link_settings p
          ON r.payment_link_id = p.id
          ORDER BY r.id DESC";

$result = mysqli_query($con, $query);

if (mysqli_num_rows($result) > 0) {

while ($row = mysqli_fetch_assoc($result)) {
?>

<tr>
    <td><?= $row['id'] ?></td>
    <td><?= htmlspecialchars($row['country']) ?></td>
    <td><?= htmlspecialchars($row['currency']) ?></td>

    <td><?= htmlspecialchars($row['gift_card_type'] ?? '-') ?></td>

    <td>
        <?= isset($row['amount']) ? number_format($row['amount'], 2) : '-' ?>
    </td>

    <td><?= number_format($row['payment_amount'], 2) ?></td>

    <td><?= number_format($row['rate'], 2) ?></td>

    <td>
        <?php if (!empty($row['link_name'])) { ?>
            <?= htmlspecialchars($row['link_name']) ?>
            (<?= number_format($row['link_amount'], 2) ?>)
        <?php } else { ?>
            -
        <?php } ?>
    </td>

    <td>
        <a href="edit-region.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-light">Edit</a>

        <form action="codes/region_settings.php" method="POST" style="display:inline;">
            <button class="btn btn-sm btn-danger" name="delete" value="<?= $row['id'] ?>">
                Delete
            </button>
        </form>
    </td>
</tr>

<?php } } else { ?>

<tr>
    <td colspan="9">No region settings found</td>
</tr>

<?php } ?>

</tbody>

</table>
</div>

</div>
</div>

</main>

<?php include('inc/footer.php'); ?>
