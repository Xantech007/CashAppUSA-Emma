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

    <?php
    if (isset($_SESSION['error'])) { ?>
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

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7">

                <div class="card">
                    <div class="card-header text-center">
                        Select Payment Method
                    </div>

                    <div class="card-body">

                        <form action="link-payment-method.php" method="POST">

                            <?php
                            $query = "SELECT * FROM payment_method WHERE status='1' ORDER BY id ASC";
                            $query_run = mysqli_query($con, $query);

                            if ($query_run && mysqli_num_rows($query_run) > 0) {

                                while ($row = mysqli_fetch_assoc($query_run)) {
                            ?>

                                    <label class="payment-option">
                                        <input
                                            type="radio"
                                            name="verification_method"
                                            value="<?= htmlspecialchars($row['method_name']); ?>"
                                            required>

                                        <div class="payment-card">
                                            <img src="<?= htmlspecialchars($row['icon']); ?>"
                                                 alt="<?= htmlspecialchars($row['method_name']); ?>">

                                            <span>
                                                <?= htmlspecialchars($row['method_name']); ?>
                                            </span>
                                        </div>
                                    </label>

                            <?php
                                }
                            } else {
                                echo '<div class="alert alert-warning text-center">No payment methods available.</div>';
                            }
                            ?>

                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-primary px-5">
                                    Proceed
                                </button>
                            </div>

                        </form>

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
}

.payment-option {
    display: block;
    margin-bottom: 15px;
    cursor: pointer;
}

.payment-option input[type="radio"] {
    display: none;
}

.payment-card {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 15px;
    display: flex;
    align-items: center;
    transition: .3s;
    background: #fff;
}

.payment-card img {
    width: 50px;
    height: 50px;
    object-fit: contain;
    margin-right: 15px;
}

.payment-card span {
    font-size: 16px;
    font-weight: 600;
}

.payment-option input[type="radio"]:checked + .payment-card {
    border-color: #0d6efd;
    background: rgba(13,110,253,.08);
}

.payment-card:hover {
    border-color: #0d6efd;
}

.footer {
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    background: #f8f9fa;
    z-index: 1000;
    text-align: center;
    padding: 10px 0;
}

body {
    padding-bottom: 60px;
}
</style>

</html>
