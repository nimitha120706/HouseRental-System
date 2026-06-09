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
    $lease_id = (int)$_GET['delete'];
    // Get property_id before deleting
    $lease = mysqli_fetch_assoc(mysqli_query($conn, "SELECT property_id FROM leases WHERE lease_id = $lease_id AND admin_id = $admin_id"));
    mysqli_query($conn, "DELETE FROM leases WHERE lease_id = $lease_id AND admin_id = $admin_id");
    // Update property status to available
    if ($lease) {
        mysqli_query($conn, "UPDATE properties SET status = 'Available' WHERE property_id = " . $lease['property_id'] . " AND admin_id = $admin_id");
    }
    header("Location: leases.php");
    exit();
}

// Handle status update
if (isset($_GET['activate'])) {
    $lease_id = (int)$_GET['activate'];
    mysqli_query($conn, "UPDATE leases SET status = 'Active' WHERE lease_id = $lease_id AND admin_id = $admin_id");
    $lease = mysqli_fetch_assoc(mysqli_query($conn, "SELECT property_id FROM leases WHERE lease_id = $lease_id AND admin_id = $admin_id"));
    if ($lease) {
        mysqli_query($conn, "UPDATE properties SET status = 'Rented' WHERE property_id = " . $lease['property_id'] . " AND admin_id = $admin_id");
    }
    header("Location: leases.php");
    exit();
}

if (isset($_GET['terminate'])) {
    $lease_id = (int)$_GET['terminate'];
    mysqli_query($conn, "UPDATE leases SET status = 'Terminated' WHERE lease_id = $lease_id AND admin_id = $admin_id");
    // Update property status to available
    $lease = mysqli_fetch_assoc(mysqli_query($conn, "SELECT property_id FROM leases WHERE lease_id = $lease_id AND admin_id = $admin_id"));
    if ($lease) {
        mysqli_query($conn, "UPDATE properties SET status = 'Available' WHERE property_id = " . $lease['property_id'] . " AND admin_id = $admin_id");
    }
    header("Location: leases.php");
    exit();
}

$leases = mysqli_query($conn, "SELECT l.*, t.full_name, p.address, p.city FROM leases l LEFT JOIN tenants t ON l.tenant_id = t.tenant_id LEFT JOIN properties p ON l.property_id = p.property_id WHERE l.admin_id = $admin_id ORDER BY l.lease_id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leases - House Rental Management</title>
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
                    <a class="nav-link active" href="leases.php"><i class="bi bi-file-text"></i> Leases</a>
                    <a class="nav-link" href="rent_collection.php"><i class="bi bi-cash-coin"></i> Rent Collection</a>
                    <a class="nav-link" href="maintenance.php"><i class="bi bi-tools"></i> Maintenance</a>
                    <a class="nav-link" href="payments.php"><i class="bi bi-cash"></i> Payments</a>
                    <a class="nav-link" href="reports.php"><i class="bi bi-bar-chart"></i> Reports</a>
                    <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Lease Management</h2>
                    <a href="tenants.php" class="btn btn-primary">Create New Lease</a>
                </div>
                
                <!-- Leases Table -->
                <div class="card table-card">
                    <div class="card-header">
                        <h5 class="mb-0">All Leases</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tenant</th>
                                        <th>Property</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Monthly Rent</th>
                                        <th>Security Deposit</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($leases)): ?>
                                    <tr>
                                        <td><?php echo $row['lease_id']; ?></td>
                                        <td><?php echo $row['full_name']; ?></td>
                                        <td><?php echo $row['address']; ?>, <?php echo $row['city']; ?></td>
                                        <td><?php echo $row['start_date']; ?></td>
                                        <td><?php echo $row['end_date']; ?></td>
                                        <td>₹<?php echo number_format($row['monthly_rent'], 2); ?></td>
                                        <td>₹<?php echo number_format($row['security_deposit'], 2); ?></td>
                                        <td><span class="status-<?php echo strtolower($row['status']); ?>"><?php echo $row['status']; ?></span></td>
                                        <td>
                                            <?php if ($row['status'] != 'Active'): ?>
                                                <a href="leases.php?activate=<?php echo $row['lease_id']; ?>" class="btn btn-sm btn-success btn-action">Activate</a>
                                            <?php endif; ?>
                                            <?php if ($row['status'] == 'Active'): ?>
                                                <a href="leases.php?terminate=<?php echo $row['lease_id']; ?>" class="btn btn-sm btn-warning btn-action">Terminate</a>
                                            <?php endif; ?>
                                            <a href="leases.php?delete=<?php echo $row['lease_id']; ?>" class="btn btn-sm btn-danger btn-action" onclick="return confirmDelete()">Delete</a>
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
