<?php
session_start();
include('../config/dbcon.php');
include('inc/header.php');
include('inc/navbar.php');
include('inc/sidebar.php');

if (!isset($_GET['id'])) {
    header("Location: manage-payment-methods.php");
    exit();
}

$id = (int)$_GET['id'];

$query = mysqli_query(
    $con,
    "SELECT * FROM payment_method WHERE id='$id' LIMIT 1"
);

if (mysqli_num_rows($query) == 0) {
    header("Location: manage-payment-methods.php");
    exit();
}

$data = mysqli_fetch_assoc($query);
?>

<main id="main" class="main">

<div class="pagetitle">
    <h1>Edit Payment Method</h1>
</div>

<div class="card">
<div class="card-body">

<form action="codes/update-payment-method.php"
      method="POST"
      enctype="multipart/form-data">

    <input type="hidden"
           name="id"
           value="<?= $data['id']; ?>">

    <input type="hidden"
           name="old_icon"
           value="<?= htmlspecialchars($data['icon']); ?>">

    <div class="mb-3">
        <label>Method Name</label>
        <input type="text"
               class="form-control"
               name="method_name"
               value="<?= htmlspecialchars($data['method_name']); ?>"
               required>
    </div>

    <div class="mb-3">
        <label>Current Icon</label><br>

        <?php if (!empty($data['icon'])) { ?>
            <img src="../<?= htmlspecialchars($data['icon']); ?>"
                 width="80">
        <?php } ?>
    </div>

    <div class="mb-3">
        <label>Replace Icon</label>
        <input type="file"
               name="icon"
               class="form-control">
    </div>

    <div class="mb-3">
        <label>Description</label>
        <textarea class="form-control"
                  name="description"><?= htmlspecialchars($data['description']); ?></textarea>
    </div>

    <div class="mb-3">
        <label>Sort Order</label>
        <input type="number"
               class="form-control"
               name="sort_order"
               value="<?= $data['sort_order']; ?>">
    </div>

    <div class="form-check mb-3">
        <input type="checkbox"
               class="form-check-input"
               name="status"
               value="1"
               <?= $data['status'] == 1 ? 'checked' : ''; ?>>

        <label class="form-check-label">
            Active
        </label>
    </div>

    <button type="submit"
            name="update_payment_method"
            class="btn btn-primary">
        Update
    </button>

</form>

</div>
</div>

</main>

<?php include('inc/footer.php'); ?>
