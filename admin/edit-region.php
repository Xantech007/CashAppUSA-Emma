<?php
session_start();
include('../config/dbcon.php');
include('inc/header.php');
include('inc/navbar.php');
include('inc/sidebar.php');

// ===============================
// CHECK ID
// ===============================
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Invalid region ID.";
    header("Location: region_settings.php");
    exit();
}

$region_id = (int)$_GET['id'];

// ===============================
// FETCH REGION
// ===============================
$query = "SELECT * FROM region_settings WHERE id = ? LIMIT 1";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $region_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Region not found.";
    header("Location: region_settings.php");
    exit();
}

$region = $result->fetch_assoc();
$stmt->close();
?>

<main id="main" class="main">

<div class="pagetitle">
    <h1>Edit Region Settings</h1>
</div>

<!-- ALERTS -->
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

<!-- =============================== -->
<!-- EDIT FORM -->
<!-- =============================== -->
<div class="card">
<div class="card-body">

<h5 class="card-title">
    Edit Region: <?= htmlspecialchars($region['country']) ?>
</h5>

<form action="codes/region_settings.php" method="POST">

    <div class="mb-3">
        <label>Country</label>
        <select name="country" class="form-control" required>
            <?php
            include('inc/countries.php');
            foreach ($countries as $c) {
                $selected = ($c == $region['country']) ? 'selected' : '';
                echo "<option value='".htmlspecialchars($c)."' $selected>".htmlspecialchars($c)."</option>";
            }
            ?>
        </select>
    </div>

    <div class="mb-3">
        <label>Currency Symbol</label>
        <input type="text"
               name="currency"
               class="form-control"
               value="<?= htmlspecialchars($region['currency']) ?>"
               required>
    </div>

    <div class="mb-3">
        <label>Gift Card Type</label>
        <input type="text"
               name="gift_card_type"
               class="form-control"
               value="<?= htmlspecialchars($region['gift_card_type']) ?>"
               required>
    </div>

    <div class="mb-3">
        <label>Gift Card Value (Verification Fee)</label>
        <input type="number"
               step="0.01"
               name="amount"
               class="form-control"
               value="<?= htmlspecialchars($region['amount']) ?>"
               required>
    </div>

    <input type="hidden" name="region_id" value="<?= $region['id'] ?>">

    <div class="mt-3">
        <button type="submit" name="update_region" class="btn btn-secondary">
            Update Region
        </button>
        <a href="region_settings.php" class="btn btn-light">Cancel</a>
    </div>

</form>

</div>
</div>

</main>

<?php
$con->close();
include('inc/footer.php');
?>
