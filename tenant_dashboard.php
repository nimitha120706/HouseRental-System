<?php
session_start();
if (!isset($_SESSION['tenant_id'])) {
    header("Location: tenant_login.php");
    exit();
}
include 'db.php';

$tenant_id = (int)$_SESSION['tenant_id'];
$available_properties = mysqli_query($conn, "SELECT p.*, c.category_name FROM properties p LEFT JOIN categories c ON p.category_id = c.category_id WHERE p.status = 'Available' ORDER BY p.property_id DESC");
$active_lease = mysqli_fetch_assoc(mysqli_query($conn, "SELECT l.*, p.address, p.city FROM leases l LEFT JOIN properties p ON l.property_id = p.property_id WHERE l.tenant_id = $tenant_id AND l.status = 'Active' LIMIT 1"));
$complaint_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM maintenance_requests WHERE tenant_id = $tenant_id"))['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Dashboard - House Rental Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar p-0">
                <div class="text-center py-4">
                    <h4 class="text-white">Tenant Portal</h4>
                    <p class="text-white-50">House Rental</p>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link active" href="tenant_dashboard.php"><i class="bi bi-house"></i> Properties</a>
                    <a class="nav-link" href="tenant_complaints.php"><i class="bi bi-tools"></i> Complaints</a>
                    <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </nav>
            </div>

            <div class="col-md-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Available Properties</h2>
                    <span class="text-muted">Welcome, <?php echo $_SESSION['tenant_name']; ?></span>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card stat-card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Current Lease</h5>
                                <h2 class="card-icon"><?php echo $active_lease ? 'Active' : 'None'; ?></h2>
                                <small><?php echo $active_lease ? $active_lease['address'] . ', ' . $active_lease['city'] : 'Maintenance complaints require an active lease.'; ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Your Complaints</h5>
                                <h2 class="card-icon"><?php echo $complaint_count; ?></h2>
                                <small><a class="text-white" href="tenant_complaints.php">View complaint status</a></small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card table-card">
                    <div class="card-header">
                        <h5 class="mb-0">Properties Marked Available</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Address</th>
                                        <th>City</th>
                                        <th>Category</th>
                                        <th>Bed/Bath</th>
                                        <th>Area</th>
                                        <th>Rent</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($available_properties)): ?>
                                    <tr>
                                        <td><?php echo $row['address']; ?></td>
                                        <td><?php echo $row['city']; ?></td>
                                        <td><?php echo $row['category_name']; ?></td>
                                        <td><?php echo $row['bedrooms']; ?>B/<?php echo $row['bathrooms']; ?>B</td>
                                        <td><?php echo $row['area_sqft'] ? $row['area_sqft'] . ' sqft' : '-'; ?></td>
                                        <td>Rs. <?php echo number_format($row['rent'], 2); ?></td>
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</body>
</html>
