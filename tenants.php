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
    $tenant_id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM tenants WHERE tenant_id = $tenant_id AND admin_id = $admin_id");
    header("Location: tenants.php");
    exit();
}

// Handle lease assignment
if (isset($_POST['assign_lease'])) {
    $tenant_id = (int)$_POST['tenant_id'];
    $property_id = (int)$_POST['property_id'];
    $start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
    $end_date = mysqli_real_escape_string($conn, $_POST['end_date']);
    
    $security_deposit = mysqli_real_escape_string($conn, $_POST['security_deposit']);
    
    $property = mysqli_fetch_assoc(mysqli_query($conn, "SELECT rent FROM properties WHERE property_id = $property_id AND admin_id = $admin_id AND status = 'Available'"));
    $tenant = mysqli_fetch_assoc(mysqli_query($conn, "SELECT tenant_id FROM tenants WHERE tenant_id = $tenant_id AND admin_id = $admin_id"));

    if (!$property || !$tenant) {
        $error = "Please select one of your available properties and tenants.";
    } else {
        $monthly_rent = $property['rent'];
        $sql = "INSERT INTO leases (admin_id, tenant_id, property_id, start_date, end_date, monthly_rent, security_deposit) VALUES ('$admin_id', '$tenant_id', '$property_id', '$start_date', '$end_date', '$monthly_rent', '$security_deposit')";
    
        if (mysqli_query($conn, $sql)) {
            // Update property status
            mysqli_query($conn, "UPDATE properties SET status = 'Rented' WHERE property_id = $property_id AND admin_id = $admin_id");
            header("Location: tenants.php");
            exit();
        }
    }
}

$tenants = mysqli_query($conn, "SELECT * FROM tenants WHERE admin_id = $admin_id ORDER BY tenant_id DESC");
$properties = mysqli_query($conn, "SELECT * FROM properties WHERE admin_id = $admin_id AND status = 'Available'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenants - House Rental Management</title>
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
                    <a class="nav-link active" href="tenants.php"><i class="bi bi-people"></i> Tenants</a>
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
                    <h2>Tenants</h2>
                    <a href="add_tenant.php" class="btn btn-primary">Add Tenant</a>
                </div>

                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <!-- Assign Lease Form -->
                <div class="card form-card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Assign Tenant to Property</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-3">
                                    <select class="form-select" name="tenant_id" required>
                                        <option value="">Select Tenant</option>
                                        <?php 
                                        $tenants_list = mysqli_query($conn, "SELECT * FROM tenants WHERE admin_id = $admin_id ORDER BY tenant_id DESC");
                                        while ($t = mysqli_fetch_assoc($tenants_list)):
                                        ?>
                                            <option value="<?php echo $t['tenant_id']; ?>"><?php echo $t['full_name']; ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" name="property_id" required>
                                        <option value="">Select Property</option>
                                        <?php while ($p = mysqli_fetch_assoc($properties)): ?>
                                            <option value="<?php echo $p['property_id']; ?>"><?php echo $p['address']; ?> - ₹<?php echo $p['rent']; ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="date" class="form-control" name="start_date" required>
                                </div>
                                <div class="col-md-2">
                                    <input type="date" class="form-control" name="end_date" required>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" step="0.01" class="form-control" name="security_deposit" placeholder="Security Deposit (₹)" required>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" name="assign_lease" class="btn btn-success w-100">Assign</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Tenants Table -->
                <div class="card table-card">
                    <div class="card-header">
                        <h5 class="mb-0">All Tenants</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Full Name</th>
                                        <th>Email</th>
                                        <th>Contact</th>
                                        <th>Aadhar</th>
                                        <th>Emergency Contact</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($tenants)): ?>
                                    <tr>
                                        <td><?php echo $row['tenant_id']; ?></td>
                                        <td><?php echo $row['full_name']; ?></td>
                                        <td><?php echo $row['email']; ?></td>
                                        <td><?php echo $row['contact']; ?></td>
                                        <td><?php echo $row['aadhar_number'] ?? '-'; ?></td>
                                        <td><?php echo $row['emergency_contact'] ?? '-'; ?></td>
                                        <td>
                                            <a href="tenants.php?delete=<?php echo $row['tenant_id']; ?>" class="btn btn-sm btn-danger btn-action" onclick="return confirmDelete()">Delete</a>
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
