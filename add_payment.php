<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
include 'db.php';
$admin_id = (int)$_SESSION['admin_id'];

// Generate next invoice number
$last_invoice = mysqli_fetch_assoc(mysqli_query($conn, "SELECT invoice_no FROM payments WHERE admin_id = $admin_id ORDER BY payment_id DESC LIMIT 1"));
if ($last_invoice) {
    $last_num = intval(substr($last_invoice['invoice_no'], 3));
    $next_invoice = 'INV' . str_pad($last_num + 1, 3, '0', STR_PAD_LEFT);
} else {
    $next_invoice = 'INV001';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $lease_id = mysqli_real_escape_string($conn, $_POST['lease_id']);
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);
    $payment_date = mysqli_real_escape_string($conn, $_POST['payment_date']);
    $invoice_no = mysqli_real_escape_string($conn, $_POST['invoice_no']);
    
    $lease = mysqli_fetch_assoc(mysqli_query($conn, "SELECT lease_id FROM leases WHERE lease_id = '$lease_id' AND admin_id = $admin_id"));
    if (!$lease) {
        $error = "Please select one of your leases.";
    } else {
        $sql = "INSERT INTO payments (admin_id, lease_id, amount, payment_date, invoice_no) VALUES ('$admin_id', '$lease_id', '$amount', '$payment_date', '$invoice_no')";
    
        if (mysqli_query($conn, $sql)) {
            header("Location: payments.php");
            exit();
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}

$leases = mysqli_query($conn, "SELECT l.*, t.full_name, p.address FROM leases l LEFT JOIN tenants t ON l.tenant_id = t.tenant_id LEFT JOIN properties p ON l.property_id = p.property_id WHERE l.admin_id = $admin_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Payment - House Rental Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-0">
                <div class="text-center py-4">
                    <h4 class="text-white">House Rental</h4>
                    <p class="text-white-50">Management System</p>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link" href="index.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                    <a class="nav-link" href="properties.php"><i class="bi bi-house"></i> Properties</a>
                    <a class="nav-link" href="tenants.php"><i class="bi bi-people"></i> Tenants</a>
                    <a class="nav-link" href="leases.php"><i class="bi bi-file-text"></i> Leases</a>
                    <a class="nav-link" href="rent_collection.php"><i class="bi bi-cash-coin"></i> Rent Collection</a>
                    <a class="nav-link" href="maintenance.php"><i class="bi bi-tools"></i> Maintenance</a>
                    <a class="nav-link active" href="payments.php"><i class="bi bi-cash"></i> Payments</a>
                    <a class="nav-link" href="reports.php"><i class="bi bi-bar-chart"></i> Reports</a>
                    <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Add Payment</h2>
                    <a href="payments.php" class="btn btn-secondary">Back to Payments</a>
                </div>
                
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="card form-card">
                    <div class="card-header">
                        <h5 class="mb-0">Payment Details</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="lease_id" class="form-label">Lease</label>
                                        <select class="form-select" id="lease_id" name="lease_id" required>
                                            <option value="">Select Lease</option>
                                            <?php while ($l = mysqli_fetch_assoc($leases)): ?>
                                                <option value="<?php echo $l['lease_id']; ?>"><?php echo $l['full_name']; ?> - <?php echo $l['address']; ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="invoice_no" class="form-label">Invoice Number</label>
                                        <input type="text" class="form-control" id="invoice_no" name="invoice_no" value="<?php echo $next_invoice; ?>" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="amount" class="form-label">Amount (₹)</label>
                                        <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="payment_date" class="form-label">Payment Date</label>
                                        <input type="date" class="form-control" id="payment_date" name="payment_date" required>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Payment</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</body>
</html>
