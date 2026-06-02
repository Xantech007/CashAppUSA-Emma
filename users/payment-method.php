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
        <div class="modal fade show" id="errorModal" tabindex="-1" style="display:block;">
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
                        <button type="button" class="btn btn-primary"
                            onclick="window.location.href='payment-method.php'">
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

                            <input type="hidden"
                                name="verification_method"
                                id="verification_method"
                                required>

                            <div class="custom-dropdown">

                                <div class="dropdown-selected" id="dropdownSelected">
                                    <span>Select Payment Method</span>
                                </div>

                                <div class="dropdown-options" id="dropdownOptions">

                                    <?php
                                    $query = "SELECT * FROM payment_method WHERE status='1' ORDER BY id ASC";
                                    $query_run = mysqli_query($con, $query);

                                    if ($query_run && mysqli_num_rows($query_run) > 0) {

                                        while ($row = mysqli_fetch_assoc($query_run)) {
                                    ?>

                                            <div class="dropdown-option"
                                                data-value="<?= htmlspecialchars($row['method_name']); ?>"
                                                data-icon="<?= htmlspecialchars($row['icon']); ?>">

                                                <img src="<?= htmlspecialchars($row['icon']); ?>"
                                                    alt="<?= htmlspecialchars($row['method_name']); ?>">

                                                <span><?= htmlspecialchars($row['method_name']); ?></span>

                                            </div>

                                    <?php
                                        }
                                    } else {
                                        echo '<div class="text-center p-3">No payment methods available.</div>';
                                    }
                                    ?>

                                </div>

                            </div>

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
    padding-bottom: 60px;
}

#main {
    flex: 1 0 auto;
}

/* Custom Dropdown */

.custom-dropdown {
    position: relative;
    width: 100%;
}

.dropdown-selected {
    border: 1px solid #ced4da;
    border-radius: 8px;
    padding: 12px;
    cursor: pointer;
    background: #fff;
    display: flex;
    align-items: center;
    min-height: 55px;
}

.dropdown-selected img {
    width: 35px;
    height: 35px;
    object-fit: contain;
    margin-right: 10px;
}

.dropdown-options {
    display: none;
    position: absolute;
    width: 100%;
    background: #fff;
    border: 1px solid #ced4da;
    border-radius: 8px;
    margin-top: 5px;
    max-height: 250px;
    overflow-y: auto;
    z-index: 9999;
}

.dropdown-option {
    display: flex;
    align-items: center;
    padding: 12px;
    cursor: pointer;
}

.dropdown-option:hover {
    background: #f8f9fa;
}

.dropdown-option img {
    width: 35px;
    height: 35px;
    object-fit: contain;
    margin-right: 10px;
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
</style>

<script>
const selected = document.getElementById('dropdownSelected');
const options = document.getElementById('dropdownOptions');
const hiddenInput = document.getElementById('verification_method');

selected.addEventListener('click', () => {
    options.style.display =
        options.style.display === 'block' ? 'none' : 'block';
});

document.querySelectorAll('.dropdown-option').forEach(option => {

    option.addEventListener('click', function() {

        let value = this.dataset.value;
        let icon = this.dataset.icon;

        hiddenInput.value = value;

        selected.innerHTML = `
            <img src="${icon}" alt="">
            <span>${value}</span>
        `;

        options.style.display = 'none';
    });

});

document.addEventListener('click', function(e) {

    if (!e.target.closest('.custom-dropdown')) {
        options.style.display = 'none';
    }

});
</script>

</html>
