<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
include 'db.php';
$admin_id = (int)$_SESSION['admin_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $property_id = mysqli_real_escape_string($conn, $_POST['property_id']);
    $tenant_id = mysqli_real_escape_string($conn, $_POST['tenant_id']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $request_date = mysqli_real_escape_string($conn, $_POST['request_date']);
    
    $lease = mysqli_fetch_assoc(mysqli_query($conn, "SELECT lease_id FROM leases WHERE admin_id = $admin_id AND property_id = '$property_id' AND tenant_id = '$tenant_id' AND status = 'Active'"));
    if (!$lease) {
        $error = "Please select a tenant with an active lease for this property.";
    } else {
        $sql = "INSERT INTO maintenance_requests (admin_id, property_id, tenant_id, description, request_date, status) VALUES ('$admin_id', '$property_id', '$tenant_id', '$description', '$request_date', 'Pending')";
    
        if (mysqli_query($conn, $sql)) {
            header("Location: maintenance.php");
            exit();
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}

$properties = mysqli_query($conn, "SELECT * FROM properties WHERE admin_id = $admin_id");
$tenants = mysqli_query($conn, "SELECT * FROM tenants WHERE admin_id = $admin_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Maintenance Request - House Rental Management</title>
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
                    <h2>Add Maintenance Request</h2>
                    <a href="maintenance.php" class="btn btn-secondary">Back to Maintenance</a>
                </div>
                
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="card form-card">
                    <div class="card-header">
                        <h5 class="mb-0">Maintenance Request Details</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="property_id" class="form-label">Property</label>
                                        <select class="form-select" id="property_id" name="property_id" required>
                                            <option value="">Select Property</option>
                                            <?php while ($p = mysqli_fetch_assoc($properties)): ?>
                                                <option value="<?php echo $p['property_id']; ?>"><?php echo $p['address']; ?> - <?php echo $p['city']; ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="tenant_id" class="form-label">Tenant</label>
                                        <select class="form-select" id="tenant_id" name="tenant_id" required>
                                            <option value="">Select Tenant</option>
                                            <?php while ($t = mysqli_fetch_assoc($tenants)): ?>
                                                <option value="<?php echo $t['tenant_id']; ?>"><?php echo $t['full_name']; ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="request_date" class="form-label">Request Date</label>
                                        <input type="date" class="form-control" id="request_date" name="request_date" required>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit Request</button>
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
