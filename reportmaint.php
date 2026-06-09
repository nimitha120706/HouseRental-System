<?php
session_start();

if (isset($_SESSION['tenant_id'])) {
    header("Location: tenant_complaints.php");
    exit();
}

header("Location: tenant_login.php");
exit();
?>
