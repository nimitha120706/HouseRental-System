<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
include 'db.php';
$admin_id = (int)$_SESSION['admin_id'];

// Search filters
$where = "WHERE mr.admin_id = $admin_id";
if (isset($_GET['property']) && !empty($_GET['property'])) {
    $property = mysqli_real_escape_string($conn, $_GET['property']);
    $where .= " AND p.address LIKE '%$property%'";
}
if (isset($_GET['tenant']) && !empty($_GET['tenant'])) {
    $tenant = mysqli_real_escape_string($conn, $_GET['tenant']);
    $where .= " AND t.full_name LIKE '%$tenant%'";
}
if (isset($_GET['description']) && !empty($_GET['description'])) {
    $description = mysqli_real_escape_string($conn, $_GET['description']);
    $where .= " AND mr.description LIKE '%$description%'";
}

$maintenance = mysqli_query($conn, "SELECT mr.*, p.address, t.full_name FROM maintenance_requests mr LEFT JOIN properties p ON mr.property_id = p.property_id LEFT JOIN tenants t ON mr.tenant_id = t.tenant_id $where ORDER BY mr.request_id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance - House Rental Management</title>
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
                    <a class="nav-link active" href="maintenance.php"><i class="bi bi-tools"></i> Maintenance</a>
                    <a class="nav-link" href="payments.php"><i class="bi bi-cash"></i> Payments</a>
                    <a class="nav-link" href="reports.php"><i class="bi bi-bar-chart"></i> Reports</a>
                    <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Maintenance Requests</h2>
                    <a href="add_maintenance.php" class="btn btn-primary">Add Request</a>
                </div>
                
                <!-- Search Form -->
                <div class="card form-card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Search Requests</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="">
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="text" class="form-control" name="property" placeholder="Search by Property" value="<?php echo isset($_GET['property']) ? $_GET['property'] : ''; ?>">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" name="tenant" placeholder="Search by Tenant" value="<?php echo isset($_GET['tenant']) ? $_GET['tenant'] : ''; ?>">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" name="description" placeholder="Search by Description" value="<?php echo isset($_GET['description']) ? $_GET['description'] : ''; ?>">
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">Search</button>
                                    <a href="maintenance.php" class="btn btn-secondary">Clear</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Maintenance Table -->
                <div class="card table-card">
                    <div class="card-header">
                        <h5 class="mb-0">All Maintenance Requests</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Property</th>
                                        <th>Tenant</th>
                                        <th>Description</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($maintenance)): ?>
                                    <tr>
                                        <td><?php echo $row['request_id']; ?></td>
                                        <td><?php echo $row['address']; ?></td>
                                        <td><?php echo $row['full_name']; ?></td>
                                        <td><?php echo $row['description']; ?></td>
                                        <td><?php echo $row['request_date']; ?></td>
                                        <td><span class="status-<?php echo strtolower($row['status']); ?>"><?php echo $row['status']; ?></span></td>
                                        <td>
                                            <?php if ($row['status'] == 'Pending'): ?>
                                                <a href="resolve_request.php?id=<?php echo $row['request_id']; ?>" class="btn btn-sm btn-success btn-action">Resolve</a>
                                            <?php endif; ?>
                                            <a href="delete_request.php?id=<?php echo $row['request_id']; ?>" class="btn btn-sm btn-danger btn-action" onclick="return confirmDelete()">Delete</a>
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
