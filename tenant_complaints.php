<?php
session_start();
if (!isset($_SESSION['tenant_id'])) {
    header("Location: tenant_login.php");
    exit();
}
include 'db.php';

$tenant_id = (int)$_SESSION['tenant_id'];
$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $lease_id = (int)$_POST['lease_id'];
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $request_date = date('Y-m-d');

    $lease = mysqli_fetch_assoc(mysqli_query($conn, "SELECT admin_id, property_id FROM leases WHERE lease_id = $lease_id AND tenant_id = $tenant_id AND status = 'Active'"));
    if ($lease) {
        $admin_id = (int)$lease['admin_id'];
        $property_id = (int)$lease['property_id'];
        $sql = "INSERT INTO maintenance_requests (admin_id, property_id, tenant_id, description, request_date, status) VALUES ('$admin_id', '$property_id', '$tenant_id', '$description', '$request_date', 'Pending')";
        if (mysqli_query($conn, $sql)) {
            $success = "Your maintenance complaint has been submitted.";
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    } else {
        $error = "You need an active lease to raise a maintenance complaint.";
    }
}

$active_leases = mysqli_query($conn, "SELECT l.*, p.address, p.city FROM leases l LEFT JOIN properties p ON l.property_id = p.property_id WHERE l.tenant_id = $tenant_id AND l.status = 'Active' ORDER BY l.lease_id DESC");
$complaints = mysqli_query($conn, "SELECT mr.*, p.address, p.city FROM maintenance_requests mr LEFT JOIN properties p ON mr.property_id = p.property_id WHERE mr.tenant_id = $tenant_id ORDER BY mr.request_id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Complaints - House Rental Management</title>
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
                    <a class="nav-link" href="tenant_dashboard.php"><i class="bi bi-house"></i> Properties</a>
                    <a class="nav-link active" href="tenant_complaints.php"><i class="bi bi-tools"></i> Complaints</a>
                    <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </nav>
            </div>

            <div class="col-md-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Maintenance Complaints</h2>
                    <span class="text-muted"><?php echo $_SESSION['tenant_name']; ?></span>
                </div>

                <?php if($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="card form-card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Raise Complaint</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-5">
                                    <select class="form-select" name="lease_id" required>
                                        <option value="">Select Active Lease</option>
                                        <?php while ($lease = mysqli_fetch_assoc($active_leases)): ?>
                                            <option value="<?php echo $lease['lease_id']; ?>"><?php echo $lease['address']; ?> - <?php echo $lease['city']; ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-7">
                                    <textarea class="form-control" name="description" rows="3" placeholder="Describe the issue" required></textarea>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">Submit Complaint</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card table-card">
                    <div class="card-header">
                        <h5 class="mb-0">Your Complaint Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Property</th>
                                        <th>Description</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_assoc($complaints)): ?>
                                    <tr>
                                        <td><?php echo $row['address']; ?>, <?php echo $row['city']; ?></td>
                                        <td><?php echo $row['description']; ?></td>
                                        <td><?php echo $row['request_date']; ?></td>
                                        <td><span class="status-<?php echo strtolower($row['status']); ?>"><?php echo $row['status']; ?></span></td>
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
