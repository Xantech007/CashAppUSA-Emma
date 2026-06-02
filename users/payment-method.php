<?php
session_start();
include('../config/dbcon.php');
include('inc/header.php');
include('inc/navbar.php');

// Check if user is logged in
if (!isset($_SESSION['auth'])) {
    $_SESSION['error'] = "Please log in to access this page.";
    header("Location: ../signin.php");
    exit(0);
}
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Link Payment Method</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index">Home</a></li>
                <li class="breadcrumb-item active">Link</li>
            </ol>
        </nav>
    </div>

    <?php if (isset($_SESSION['error'])) { ?>
        <div class="modal fade show" id="errorModal" tabindex="-1" style="display:block;" aria-modal="true" role="dialog">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Error</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <?= htmlspecialchars($_SESSION['error']); ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="window.location.href='payment-method.php'">
                            OK
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    <?php
        unset($_SESSION['error']);
    }
    ?>

    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-md-6">

                <div class="card">
                    <div class="card-header">
                        Select Payment Method
                    </div>

                    <div class="card-body mt-3">

                        <?php
                        $query = "SELECT * FROM payment_method WHERE status='1' ORDER BY id ASC";
                        $query_run = mysqli_query($con, $query);
                        ?>

                        <?php if ($query_run && mysqli_num_rows($query_run) > 0) { ?>

                            <form action="link-payment-method.php" method="POST">

                                <div class="mb-3">
                                    <select class="form-select" name="verification_method" required>
                                        <option value="" selected disabled>
                                            Select a payment method
                                        </option>

                                        <?php while ($row = mysqli_fetch_assoc($query_run)) { ?>
                                            <option value="<?= htmlspecialchars($row['method_name']); ?>">
                                                <?= htmlspecialchars($row['method_name']); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary mt-3">
                                    Proceed
                                </button>

                            </form>

                        <?php } else { ?>

                            <div class="alert alert-warning">
                                No payment methods available.
                            </div>

                        <?php } ?>

                    </div>
                </div>

            </div>
        </div>
    </div>
</main>

<?php include('inc/footer.php'); ?>

<style>
html,
body {
    height: 100%;
    margin: 0;
}

body {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

#main {
    flex: 1 0 auto;
    display: flex;
    flex-direction: column;
}

.container {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.footer {
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    background-color: #f8f9fa;
    z-index: 1000;
    text-align: center;
    padding: 10px 0;
}

body {
    padding-bottom: 60px;
}

@media (max-width: 576px) {
    .footer {
        padding: 5px 0;
        font-size: 14px;
    }
}
</style>

</html>
