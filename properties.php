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
    $property_id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM properties WHERE property_id = $property_id AND admin_id = $admin_id");
    header("Location: properties.php");
    exit();
}

// Handle status update
if (isset($_GET['mark_available'])) {
    $property_id = (int)$_GET['mark_available'];
    mysqli_query($conn, "UPDATE properties SET status = 'Available' WHERE property_id = $property_id AND admin_id = $admin_id");
    mysqli_query($conn, "UPDATE leases SET status = 'Terminated' WHERE property_id = $property_id AND admin_id = $admin_id AND status = 'Active'");
    header("Location: properties.php");
    exit();
}

if (isset($_GET['mark_rented'])) {
    $property_id = (int)$_GET['mark_rented'];
    mysqli_query($conn, "UPDATE properties SET status = 'Rented' WHERE property_id = $property_id AND admin_id = $admin_id");
    $lease = mysqli_fetch_assoc(mysqli_query($conn, "SELECT lease_id FROM leases WHERE property_id = $property_id AND admin_id = $admin_id ORDER BY lease_id DESC LIMIT 1"));
    if ($lease) {
        mysqli_query($conn, "UPDATE leases SET status = 'Active' WHERE lease_id = " . $lease['lease_id'] . " AND admin_id = $admin_id");
    }
    header("Location: properties.php");
    exit();
}

// Search filters
$where = "WHERE p.admin_id = $admin_id";
if (isset($_GET['city']) && !empty($_GET['city'])) {
    $city = mysqli_real_escape_string($conn, $_GET['city']);
    $where .= " AND city LIKE '%$city%'";
}
if (isset($_GET['min_rent']) && !empty($_GET['min_rent'])) {
    $min_rent = mysqli_real_escape_string($conn, $_GET['min_rent']);
    $where .= " AND rent >= $min_rent";
}
if (isset($_GET['max_rent']) && !empty($_GET['max_rent'])) {
    $max_rent = mysqli_real_escape_string($conn, $_GET['max_rent']);
    $where .= " AND rent <= $max_rent";
}

$properties = mysqli_query($conn, "SELECT p.*, c.category_name FROM properties p LEFT JOIN categories c ON p.category_id = c.category_id $where ORDER BY p.property_id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Properties - House Rental Management</title>
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
                    <a class="nav-link active" href="properties.php"><i class="bi bi-house"></i> Properties</a>
                    <a class="nav-link" href="tenants.php"><i class="bi bi-people"></i> Tenants</a>
                    <a class="nav-link" href="leases.php"><i class="bi bi-file-text"></i> Leases</a>
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
                    <h2>Properties</h2>
                    <a href="add_property.php" class="btn btn-primary">Add Property</a>
                </div>
                
                <!-- Search Form -->
                <div class="card form-card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Search Properties</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="">
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="text" class="form-control" name="city" placeholder="Search by City" value="<?php echo isset($_GET['city']) ? $_GET['city'] : ''; ?>">
                                </div>
                                <div class="col-md-3">
                                    <input type="number" class="form-control" name="min_rent" placeholder="Min Rent" value="<?php echo isset($_GET['min_rent']) ? $_GET['min_rent'] : ''; ?>">
                                </div>
                                <div class="col-md-3">
                                    <input type="number" class="form-control" name="max_rent" placeholder="Max Rent" value="<?php echo isset($_GET['max_rent']) ? $_GET['max_rent'] : ''; ?>">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">Search</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Properties Table -->
                <div class="card table-card">
                    <div class="card-header">
                        <h5 class="mb-0">All Properties</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Address</th>
                                        <th>City</th>
                                        <th>Owner</th>
                                        <th>Category</th>
                                        <th>Bed/Bath</th>
                                        <th>Area</th>
                                        <th>Rent</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($properties)): ?>
                                    <tr>
                                        <td><?php echo $row['property_id']; ?></td>
                                        <td><?php echo $row['address']; ?></td>
                                        <td><?php echo $row['city']; ?></td>
                                        <td><?php echo $row['owner_name']; ?></td>
                                        <td><?php echo $row['category_name']; ?></td>
                                        <td><?php echo $row['bedrooms']; ?>B/<?php echo $row['bathrooms']; ?>B</td>
                                        <td><?php echo $row['area_sqft'] ? $row['area_sqft'] . ' sqft' : '-'; ?></td>
                                        <td>₹<?php echo number_format($row['rent'], 2); ?></td>
                                        <td><span class="status-<?php echo strtolower($row['status']); ?>"><?php echo $row['status']; ?></span></td>
                                        <td>
                                            <?php if ($row['status'] != 'Available'): ?>
                                                <a href="properties.php?mark_available=<?php echo $row['property_id']; ?>" class="btn btn-sm btn-success btn-action">Available</a>
                                            <?php endif; ?>
                                            <?php if ($row['status'] != 'Rented'): ?>
                                                <a href="properties.php?mark_rented=<?php echo $row['property_id']; ?>" class="btn btn-sm btn-warning btn-action">Rented</a>
                                            <?php endif; ?>
                                            <a href="properties.php?delete=<?php echo $row['property_id']; ?>" class="btn btn-sm btn-danger btn-action" onclick="return confirmDelete()">Delete</a>
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
