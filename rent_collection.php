<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
include 'db.php';
$admin_id = (int)$_SESSION['admin_id'];

// Handle delete
if (isset($_GET['delete'])) {
    $rent_id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM rent_collection WHERE rent_id = $rent_id AND admin_id = $admin_id");
    header("Location: rent_collection.php");
    exit();
}

// Handle add rent collection
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_rent'])) {
    $lease_id = mysqli_real_escape_string($conn, $_POST['lease_id']);
    $month = mysqli_real_escape_string($conn, $_POST['month']);
    $year = mysqli_real_escape_string($conn, $_POST['year']);
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);
    $payment_date = mysqli_real_escape_string($conn, $_POST['payment_date']);
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    
    // Generate receipt number
    $last_receipt = mysqli_fetch_assoc(mysqli_query($conn, "SELECT receipt_no FROM rent_collection WHERE admin_id = $admin_id ORDER BY rent_id DESC LIMIT 1"));
    if ($last_receipt) {
        $last_num = intval(substr($last_receipt['receipt_no'], 3));
        $receipt_no = 'RCP' . str_pad($last_num + 1, 3, '0', STR_PAD_LEFT);
    } else {
        $receipt_no = 'RCP001';
    }
    
    $lease = mysqli_fetch_assoc(mysqli_query($conn, "SELECT lease_id FROM leases WHERE lease_id = '$lease_id' AND admin_id = $admin_id AND status = 'Active'"));
    if (!$lease) {
        $error = "Please select one of your active leases.";
    } else {
        $sql = "INSERT INTO rent_collection (admin_id, lease_id, month, year, amount, payment_date, payment_method, receipt_no) VALUES ('$admin_id', '$lease_id', '$month', '$year', '$amount', '$payment_date', '$payment_method', '$receipt_no')";
    
        if (mysqli_query($conn, $sql)) {
            header("Location: rent_collection.php");
            exit();
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}

$rent_collections = mysqli_query($conn, "SELECT rc.*, l.tenant_id, l.property_id, t.full_name, p.address FROM rent_collection rc LEFT JOIN leases l ON rc.lease_id = l.lease_id LEFT JOIN tenants t ON l.tenant_id = t.tenant_id LEFT JOIN properties p ON l.property_id = p.property_id WHERE rc.admin_id = $admin_id ORDER BY rc.payment_date DESC");
$leases = mysqli_query($conn, "SELECT l.*, t.full_name, p.address FROM leases l LEFT JOIN tenants t ON l.tenant_id = t.tenant_id LEFT JOIN properties p ON l.property_id = p.property_id WHERE l.admin_id = $admin_id AND l.status = 'Active'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent Collection - House Rental Management</title>
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
                    <a class="nav-link active" href="rent_collection.php"><i class="bi bi-cash-coin"></i> Rent Collection</a>
                    <a class="nav-link" href="maintenance.php"><i class="bi bi-tools"></i> Maintenance</a>
                    <a class="nav-link" href="payments.php"><i class="bi bi-cash"></i> Payments</a>
                    <a class="nav-link" href="reports.php"><i class="bi bi-bar-chart"></i> Reports</a>
                    <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Rent Collection</h2>
                </div>
                
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <!-- Add Rent Collection Form -->
                <div class="card form-card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Collect Rent</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-3">
                                    <select class="form-select" name="lease_id" required>
                                        <option value="">Select Lease</option>
                                        <?php while ($l = mysqli_fetch_assoc($leases)): ?>
                                            <option value="<?php echo $l['lease_id']; ?>"><?php echo $l['full_name']; ?> - <?php echo $l['address']; ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select" name="month" required>
                                        <option value="">Month</option>
                                        <option value="January">January</option>
                                        <option value="February">February</option>
                                        <option value="March">March</option>
                                        <option value="April">April</option>
                                        <option value="May">May</option>
                                        <option value="June">June</option>
                                        <option value="July">July</option>
                                        <option value="August">August</option>
                                        <option value="September">September</option>
                                        <option value="October">October</option>
                                        <option value="November">November</option>
                                        <option value="December">December</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" class="form-control" name="year" placeholder="Year" value="<?php echo date('Y'); ?>" required>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" step="0.01" class="form-control" name="amount" placeholder="Amount (₹)" required>
                                </div>
                                <div class="col-md-2">
                                    <input type="date" class="form-control" name="payment_date" value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select" name="payment_method" required>
                                        <option value="Cash">Cash</option>
                                        <option value="Bank Transfer">Bank Transfer</option>
                                        <option value="UPI">UPI</option>
                                        <option value="Cheque">Cheque</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-12">
                                    <button type="submit" name="add_rent" class="btn btn-primary">Collect Rent</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Rent Collection Table -->
                <div class="card table-card">
                    <div class="card-header">
                        <h5 class="mb-0">Rent Collection History</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Receipt No</th>
                                        <th>Tenant</th>
                                        <th>Property</th>
                                        <th>Month</th>
                                        <th>Year</th>
                                        <th>Amount</th>
                                        <th>Payment Date</th>
                                        <th>Method</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($rent_collections)): ?>
                                    <tr>
                                        <td><strong><?php echo $row['receipt_no']; ?></strong></td>
                                        <td><?php echo $row['full_name']; ?></td>
                                        <td><?php echo $row['address']; ?></td>
                                        <td><?php echo $row['month']; ?></td>
                                        <td><?php echo $row['year']; ?></td>
                                        <td>₹<?php echo number_format($row['amount'], 2); ?></td>
                                        <td><?php echo $row['payment_date']; ?></td>
                                        <td><?php echo $row['payment_method']; ?></td>
                                        <td><span class="status-<?php echo strtolower($row['status']); ?>"><?php echo $row['status']; ?></span></td>
                                        <td>
                                            <a href="rent_collection.php?delete=<?php echo $row['rent_id']; ?>" class="btn btn-sm btn-danger btn-action" onclick="return confirmDelete()">Delete</a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</body>
</html>
