<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "house_rental_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function ensure_admin_id_column($conn, $table) {
    $table_safe = mysqli_real_escape_string($conn, $table);
    $column = mysqli_query($conn, "SHOW COLUMNS FROM `$table_safe` LIKE 'admin_id'");
    if ($column && mysqli_num_rows($column) == 0) {
        mysqli_query($conn, "ALTER TABLE `$table_safe` ADD admin_id INT NOT NULL DEFAULT 1");
    }
}

$admin_scoped_tables = [
    'properties',
    'tenants',
    'leases',
    'maintenance_requests',
    'payments',
    'rent_collection',
    'tenant_history',
    'monthly_reports'
];

foreach ($admin_scoped_tables as $table) {
    ensure_admin_id_column($conn, $table);
}
?>
