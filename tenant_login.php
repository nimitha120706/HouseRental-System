<?php
session_start();
include 'db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = mysqli_real_escape_string($conn, trim($_POST['full_name']));

    $tenant = mysqli_fetch_assoc(mysqli_query($conn, "SELECT tenant_id, full_name FROM tenants WHERE LOWER(full_name) = LOWER('$full_name') LIMIT 1"));
    if ($tenant) {
        $_SESSION['tenant_id'] = $tenant['tenant_id'];
        $_SESSION['tenant_name'] = $tenant['full_name'];
        header("Location: tenant_dashboard.php");
        exit();
    } else {
        $error = "Tenant name not found";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Login - House Rental Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card login-card">
                    <div class="card-header">
                        <h4 class="text-center mb-0">Tenant Login</h4>
                    </div>
                    <div class="card-body">
                        <?php if($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Your Name</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                        <div class="text-center mt-3">
                            <a href="available_properties.php">Browse Available Properties</a>
                            <span class="text-muted mx-2">|</span>
                            <a href="login.php">Admin Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
