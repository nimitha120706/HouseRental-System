<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
include 'db.php';
$admin_id = (int)$_SESSION['admin_id'];

if (isset($_GET['id'])) {
    $request_id = (int)$_GET['id'];
    mysqli_query($conn, "DELETE FROM maintenance_requests WHERE request_id = $request_id AND admin_id = $admin_id");
    header("Location: maintenance.php");
    exit();
}
?>
