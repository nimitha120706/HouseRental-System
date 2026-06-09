<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
include 'db.php';
$admin_id = (int)$_SESSION['admin_id'];

// Get current month and year
$current_month = date('F');
$current_year = date('Y');

// Calculate statistics
$total_properties = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM properties WHERE admin_id = $admin_id"))['count'];
$total_tenants = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM tenants WHERE admin_id = $admin_id"))['count'];
$active_leases = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM leases WHERE admin_id = $admin_id AND status = 'Active'"))['count'];
$available_properties = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM properties WHERE admin_id = $admin_id AND status = 'Available'"))['count'];

// Calculate total rent collected this month
$total_rent_collected = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(amount), 0) as total FROM rent_collection WHERE admin_id = $admin_id AND month = '$current_month' AND year = '$current_year'"))['total'];

// Calculate total rent collected all time
$total_rent_all_time = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(amount), 0) as total FROM rent_collection WHERE admin_id = $admin_id"))['total'];

// Calculate pending maintenance
$pending_maintenance = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM maintenance_requests WHERE admin_id = $admin_id AND status = 'Pending'"))['count'];

// Calculate occupancy rate
$occupancy_rate = $total_properties > 0 ? round(($active_leases / $total_properties) * 100, 2) : 0;

// Get recent rent collections
$recent_rent = mysqli_query($conn, "SELECT rc.*, t.full_name FROM rent_collection rc LEFT JOIN leases l ON rc.lease_id = l.lease_id LEFT JOIN tenants t ON l.tenant_id = t.tenant_id WHERE rc.admin_id = $admin_id ORDER BY rc.payment_date DESC LIMIT 5");

// Get property distribution by city
$property_cities = mysqli_query($conn, "SELECT city, COUNT(*) as count FROM properties WHERE admin_id = $admin_id GROUP BY city");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - House Rental Management</title>
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
                    <a class="nav-link" href="payments.php"><i class="bi bi-cash"></i> Payments</a>
                    <a class="nav-link active" href="reports.php"><i class="bi bi-bar-chart"></i> Reports</a>
                    <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Reports & Analytics</h2>
                    <span class="text-muted"><?php echo $current_month . ' ' . $current_year; ?></span>
                </div>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Properties</h5>
                                <h2 class="card-icon"><?php echo $total_properties; ?></h2>
                                <small>Available: <?php echo $available_properties; ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Active Leases</h5>
                                <h2 class="card-icon"><?php echo $active_leases; ?></h2>
                                <small>Occupancy: <?php echo $occupancy_rate; ?>%</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Rent This Month</h5>
                                <h2 class="card-icon">₹<?php echo number_format($total_rent_collected, 0); ?></h2>
                                <small>All Time: ₹<?php echo number_format($total_rent_all_time, 0); ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-warning text-dark">
                            <div class="card-body">
                                <h5 class="card-title">Pending Maintenance</h5>
                                <h2 class="card-icon"><?php echo $pending_maintenance; ?></h2>
                                <small>Total Tenants: <?php echo $total_tenants; ?></small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Charts & Tables -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card table-card">
                            <div class="card-header">
                                <h5 class="mb-0">Property Distribution by City</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>City</th>
                                                <th>Count</th>
                                                <th>Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = mysqli_fetch_assoc($property_cities)): ?>
                                            <tr>
                                                <td><?php echo $row['city']; ?></td>
                                                <td><?php echo $row['count']; ?></td>
                                                <td><?php echo $total_properties > 0 ? round(($row['count'] / $total_properties) * 100, 1) : 0; ?>%</td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card table-card">
                            <div class="card-header">
                                <h5 class="mb-0">Recent Rent Collections</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Tenant</th>
                                                <th>Month</th>
                                                <th>Amount</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = mysqli_fetch_assoc($recent_rent)): ?>
                                            <tr>
                                                <td><?php echo $row['full_name']; ?></td>
                                                <td><?php echo $row['month']; ?></td>
                                                <td>₹<?php echo number_format($row['amount'], 0); ?></td>
                                                <td><?php echo $row['payment_date']; ?></td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Summary Section -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card table-card">
                            <div class="card-header">
                                <h5 class="mb-0">Summary Report</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <h6>Property Status</h6>
                                        <p><strong>Total Properties:</strong> <?php echo $total_properties; ?></p>
                                        <p><strong>Available:</strong> <?php echo $available_properties; ?></p>
                                        <p><strong>Rented (Active Leases):</strong> <?php echo $active_leases; ?></p>
                                        <p><strong>Occupancy Rate:</strong> <?php echo $occupancy_rate; ?>%</p>
                                    </div>
                                    <div class="col-md-4">
                                        <h6>Financial Summary</h6>
                                        <p><strong>Rent Collected This Month:</strong> ₹<?php echo number_format($total_rent_collected, 2); ?></p>
                                        <p><strong>Total Rent Collected:</strong> ₹<?php echo number_format($total_rent_all_time, 2); ?></p>
                                        <p><strong>Total Tenants:</strong> <?php echo $total_tenants; ?></p>
                                    </div>
                                    <div class="col-md-4">
                                        <h6>Maintenance Status</h6>
                                        <p><strong>Pending Requests:</strong> <?php echo $pending_maintenance; ?></p>
                                        <p><strong>Current Period:</strong> <?php echo $current_month . ' ' . $current_year; ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</body>
</html>
