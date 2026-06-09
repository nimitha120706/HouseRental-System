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
    $payment_id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM payments WHERE payment_id = $payment_id AND admin_id = $admin_id");
    header("Location: payments.php");
    exit();
}

$payments = mysqli_query($conn, "SELECT p.*, l.tenant_id, l.property_id, t.full_name, pr.address FROM payments p LEFT JOIN leases l ON p.lease_id = l.lease_id LEFT JOIN tenants t ON l.tenant_id = t.tenant_id LEFT JOIN properties pr ON l.property_id = pr.property_id WHERE p.admin_id = $admin_id ORDER BY p.payment_id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments - House Rental Management</title>
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
                    <h2>Payments</h2>
                    <a href="add_payment.php" class="btn btn-primary">Add Payment</a>
                </div>
                
                <!-- Payments Table -->
                <div class="card table-card">
                    <div class="card-header">
                        <h5 class="mb-0">Payment History</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Invoice No</th>
                                        <th>Tenant</th>
                                        <th>Property</th>
                                        <th>Amount</th>
                                        <th>Payment Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($payments)): ?>
                                    <tr>
                                        <td><?php echo $row['payment_id']; ?></td>
                                        <td><strong><?php echo $row['invoice_no']; ?></strong></td>
                                        <td><?php echo $row['full_name']; ?></td>
                                        <td><?php echo $row['address']; ?></td>
                                        <td>₹<?php echo number_format($row['amount'], 2); ?></td>
                                        <td><?php echo $row['payment_date']; ?></td>
                                        <td>
                                            <a href="payments.php?delete=<?php echo $row['payment_id']; ?>" class="btn btn-sm btn-danger btn-action" onclick="return confirmDelete()">Delete</a>
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
