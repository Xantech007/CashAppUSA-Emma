<?php
session_start();
include('../config/dbcon.php');
include('inc/header.php');
include('inc/navbar.php');

// Check if user is logged in
if (!isset($_SESSION['auth'])) {
    $_SESSION['error'] = "Please log in to access this page.";
    error_log("used-cashtag.php - User not logged in, redirecting to signin.php");
    header("Location: ../signin.php");
    exit(0);
}

// Debugging: Log session data
error_log("used-cashtag.php - Session: " . print_r($_SESSION, true));

// Get user_id from email
$email = mysqli_real_escape_string($con, $_SESSION['email']);
$user_query = "SELECT id FROM users WHERE email = ? LIMIT 1";
$stmt = $con->prepare($user_query);
$stmt->bind_param("s", $email);
$stmt->execute();
$user_query_run = $stmt->get_result();
if ($user_query_run && mysqli_num_rows($user_query_run) > 0) {
    $user_data = mysqli_fetch_assoc($user_query_run);
    $user_id = $user_data['id'];
} else {
    $_SESSION['error'] = "User not found.";
    error_log("used-cashtag.php - User not found for email: $email");
    header("Location: ../signin.php");
    exit(0);
}
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Used TeslaTags</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item">Users</li>
                <li class="breadcrumb-item active">Used TeslaTags</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Your TeslaTag History</h5>
            <!-- Plain Table -->
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th scope="col">TeslaTag</th>
                            <th scope="col">Used On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT cashtag, created_at FROM cashtag_usage WHERE user_id = ? ORDER BY created_at DESC";
                        $stmt = $con->prepare($query);
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $query_run = $stmt->get_result();
                        if (mysqli_num_rows($query_run) > 0) {
                            foreach ($query_run as $data) { ?>
                                <tr>
                                    <td><?= htmlspecialchars($data['cashtag']) ?></td>
                                    <td><?= date('d-M-Y H:i:s', strtotime($data['created_at'])) ?></td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr>
                                <td colspan="2" class="text-center">You have no used TeslaTags.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <!-- End Plain Table -->
        </div>
    </div>
</main>

<?php include('inc/footer.php'); ?>
