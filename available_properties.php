<?php
include 'db.php';

$available_properties = mysqli_query($conn, "SELECT p.*, c.category_name FROM properties p LEFT JOIN categories c ON p.category_id = c.category_id WHERE p.status = 'Available' ORDER BY p.property_id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Properties - House Rental Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar p-0">
                <div class="text-center py-4">
                    <h4 class="text-white">House Rental</h4>
                    <p class="text-white-50">Available Homes</p>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link active" href="available_properties.php"><i class="bi bi-house"></i> Available Properties</a>
                    <a class="nav-link" href="tenant_login.php"><i class="bi bi-person"></i> Tenant Login</a>
                    <a class="nav-link" href="login.php"><i class="bi bi-box-arrow-in-right"></i> Admin Login</a>
                </nav>
            </div>

            <div class="col-md-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Available Properties</h2>
                    <a href="login.php" class="btn btn-secondary">Back to Login</a>
                </div>

                <div class="card table-card">
                    <div class="card-header">
                        <h5 class="mb-0">Properties Open for Rent</h5>
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
                                        <th>Description</th>
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
                                        <td><?php echo $row['description'] ? $row['description'] : '-'; ?></td>
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
