<?php
session_start();
include('../config/dbcon.php');
include('inc/header.php');
include('inc/navbar.php');
include('inc/sidebar.php');
?>

<main id="main" class="main">

    <div class="pagetitle">
        <h1>Payment Methods</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard">Home</a></li>
                <li class="breadcrumb-item active">Payment Methods</li>
            </ol>
        </nav>
    </div>

    <?php if(isset($_SESSION['error'])) { ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $_SESSION['error']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php unset($_SESSION['error']); } ?>

    <?php if(isset($_SESSION['success'])) { ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['success']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php unset($_SESSION['success']); } ?>

    <style>
        .add-btn{
            display:flex;
            justify-content:center;
            margin:15px 0;
        }

        .icon-preview{
            width:50px;
            height:50px;
            object-fit:contain;
            border-radius:8px;
            border:1px solid #ddd;
            padding:3px;
            background:#fff;
        }

        .form-control{
            border:1px solid #ccc;
        }
    </style>

    <div class="add-btn">
        <button class="btn btn-secondary"
                data-bs-toggle="modal"
                data-bs-target="#addPaymentMethodModal">
            Add Payment Method
        </button>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addPaymentMethodModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Add Payment Method</h5>
                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"></button>
                </div>

                <form action="codes/payment_method.php"
                      method="POST"
                      enctype="multipart/form-data">

                    <div class="modal-body">

                        <div class="mb-3">
                            <label>Method Name</label>
                            <input type="text"
                                   name="method_name"
                                   class="form-control"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label>Icon</label>
                            <input type="file"
                                   name="icon"
                                   class="form-control"
                                   accept="image/*"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label>Description</label>
                            <textarea name="description"
                                      class="form-control"
                                      rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label>Sort Order</label>
                            <input type="number"
                                   name="sort_order"
                                   value="0"
                                   class="form-control">
                        </div>

                        <div class="form-check">
                            <input type="checkbox"
                                   class="form-check-input"
                                   name="status"
                                   id="status"
                                   value="1"
                                   checked>

                            <label class="form-check-label"
                                   for="status">
                                Active
                            </label>
                        </div>

                        <input type="hidden"
                               name="auth_id"
                               value="<?= $_SESSION['id']; ?>">

                    </div>

                    <div class="modal-footer">
                        <button type="button"
                                class="btn btn-light"
                                data-bs-dismiss="modal">
                            Close
                        </button>

                        <button type="submit"
                                name="add_payment_method"
                                class="btn btn-secondary">
                            Add Method
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <div class="card">

        <div class="card-body">

            <h5 class="card-title">
                Payment Methods List
            </h5>

            <div class="table-responsive">

                <table class="table table-borderless">

                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Icon</th>
                        <th>Method</th>
                        <th>Description</th>
                        <th>Sort</th>
                        <th>Status</th>
                        <th>Edit</th>
                        <th>Delete</th>
                    </tr>
                    </thead>

                    <tbody>

                    <?php

                    $user_id = $_SESSION['id'];

                    $query = "SELECT * FROM payment_method ORDER BY sort_order ASC, id ASC";
                    $query_run = mysqli_query($con,$query);

                    if(mysqli_num_rows($query_run) > 0)
                    {
                        foreach($query_run as $row)
                        {
                            ?>

                            <tr>

                                <td><?= $row['id']; ?></td>

                                <td>
                                    <?php if(!empty($row['icon'])) { ?>
                                        <img src="../<?= htmlspecialchars($row['icon']); ?>"
                                             class="icon-preview">
                                    <?php } ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['method_name']); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row['description']); ?>
                                </td>

                                <td>
                                    <?= $row['sort_order']; ?>
                                </td>

                                <td>
                                    <?= $row['status'] == 1 ? 'Active' : 'Disabled'; ?>
                                </td>

                                <td>
                                    <a href="edit-payment-method.php?id=<?= $row['id']; ?>"
                                       class="btn btn-light">
                                        Edit
                                    </a>
                                </td>

                                <td>

                                    <form action="codes/payment_method.php"
                                          method="POST">

                                        <input type="hidden"
                                               name="auth_id"
                                               value="<?= $user_id; ?>">

                                        <button type="submit"
                                                name="delete_payment_method"
                                                value="<?= $row['id']; ?>"
                                                class="btn btn-danger"
                                                onclick="return confirm('Delete this payment method?')">
                                            Delete
                                        </button>

                                    </form>

                                </td>

                            </tr>

                            <?php
                        }
                    }
                    else
                    {
                        ?>
                        <tr>
                            <td colspan="8">
                                No payment methods found.
                            </td>
                        </tr>
                        <?php
                    }
                    ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</main>

<?php include('inc/footer.php'); ?>
