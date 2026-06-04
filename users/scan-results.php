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

// Initialize variables
$cashtag = null;
$user_id = null;
$currency_symbol = "$";  // Default fallback

// Get user_id and country from email
$email = $_SESSION['email'];
$user_query = "SELECT id, country FROM users WHERE email = ? LIMIT 1";
$stmt = $con->prepare($user_query);
$stmt->bind_param("s", $email);
$stmt->execute();
$user_result = $stmt->get_result();

if ($user_result && $user_result->num_rows > 0) {
    $user_data = $user_result->fetch_assoc();
    $user_id = $user_data['id'];
    $user_country = $user_data['country'];

    // Fetch dynamic currency symbol from region_settings
    if ($user_country) {
        $region_query = "SELECT currency FROM region_settings WHERE country = ? LIMIT 1";
        $region_stmt = $con->prepare($region_query);
        $region_stmt->bind_param("s", $user_country);
        $region_stmt->execute();
        $region_result = $region_stmt->get_result();

        if ($region_result && $region_row = $region_result->fetch_assoc()) {
            $currency_symbol = $region_row['currency'] ?: '$';
        }
        $region_stmt->close();
    }
} else {
    $_SESSION['error'] = "User not found.";
    header("Location: ../signin.php");
    exit(0);
}
$stmt->close();

// Handle POST request with CashTag
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['scan_input']) || empty(trim($_POST['scan_input']))) {
        $_SESSION['error'] = "No CashTag provided.";
        header("Location: scan.php");
        exit(0);
    }

    $cashtag = trim($_POST['scan_input']);

    // Validate CashTag
    $cashtag_query = "SELECT COUNT(*) as count FROM packages WHERE cashtag = ? AND status = '0'";
    $stmt = $con->prepare($cashtag_query);
    $stmt->bind_param("s", $cashtag);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] == 0) {
        $_SESSION['error'] = "Invalid CashTag.";
        header("Location: scan.php");
        exit(0);
    }
    $stmt->close();

    // Check if CashTag has been used by this user
    $usage_query = "SELECT COUNT(*) as count FROM cashtag_usage WHERE user_id = ? AND cashtag = ?";
    $stmt = $con->prepare($usage_query);
    $stmt->bind_param("is", $user_id, $cashtag);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        $_SESSION['error'] = "This CashTag has already been used.";
        header("Location: scan.php");
        exit(0);
    }
    $stmt->close();
}
?>

<main id="main" class="main">
  <div class="pagetitle">
    <h1>CashTag Found! Select Amount</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="../users/index.php">Home</a></li>
        <li class="breadcrumb-item"><a href="../users/scan.php" class="text-decoration-none">Scan</a></li>
        <li class="breadcrumb-item active">Results</li>
      </ol>
    </nav>
  </div>

  <!-- Success/Error Messages -->
  <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($_SESSION['success']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <script>
      setTimeout(() => { window.location.href = '../users/users-profile.php'; }, 3000);
    </script>
    <?php unset($_SESSION['success']); ?>
  <?php endif; ?>

  <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($_SESSION['error']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <script>
      setTimeout(() => { window.location.href = '../users/users-profile.php'; }, 3000);
    </script>
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>

  <?php if ($cashtag): ?>
    <div class="container text-center">
      <div class="row">
        <?php
        $query = "SELECT * FROM packages WHERE cashtag = ? AND status = '0' ORDER BY max_a ASC";
        $stmt = $con->prepare($query);
        $stmt->bind_param("s", $cashtag);
        $stmt->execute();
        $query_run = $stmt->get_result();

        if (mysqli_num_rows($query_run) > 0) {
          while ($data = $query_run->fetch_assoc()) { ?>
            <div class="col-md-4 mb-4">
              <div class="card text-center">
                <div class="card-header">
                  <?= htmlspecialchars($data['name']) ?>
                </div>
                <div class="card-body mt-2">
                  <div class="mt-3">
                    <h6>Amount: <?= htmlspecialchars($currency_symbol) ?><?= number_format($data['max_a'], 2) ?></h6>
                  </div>
                  <div class="mt-3">
                    <form action="../codes/balance.php" method="POST">
                      <input type="hidden" name="id" value="<?= $data['id'] ?>">
                      <input type="hidden" name="cashtag" value="<?= htmlspecialchars($cashtag) ?>">
                      <button type="submit" name="add_balance" class="btn btn-outline-secondary mt-3">Add Balance</button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          <?php }
        } else {
          echo '<p>No active packages found for this CashTag.</p>';
        }
        $stmt->close();
        ?>
      </div>
    </div>
  <?php else: ?>
    <div class="container text-center">
      <p>Please submit a CashTag to view packages.</p>
    </div>
  <?php endif; ?>
</main>

<?php include('inc/footer.php'); ?>
