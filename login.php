<?php
session_start();
include 'db.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['register_admin'])) {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $password = mysqli_real_escape_string($conn, $_POST['password']);

        $exists = mysqli_query($conn, "SELECT admin_id FROM admins WHERE username = '$username' OR email = '$email'");
        if (mysqli_num_rows($exists) > 0) {
            $error = "Username or email already exists";
        } else {
            $sql = "INSERT INTO admins (name, username, password, email) VALUES ('$name', '$username', '$password', '$email')";
            if (mysqli_query($conn, $sql)) {
                $success = "Account created successfully. You can log in now.";
            } else {
                $error = "Error: " . mysqli_error($conn);
            }
        }
    } else {
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $password = mysqli_real_escape_string($conn, $_POST['password']);

        $sql = "SELECT * FROM admins WHERE username = '$username' AND password = '$password'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);
            $_SESSION['admin_id'] = $row['admin_id'];
            $_SESSION['admin_name'] = $row['name'];
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid username or password";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - House Rental Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card login-card">
                    <div class="card-header">
                        <h4 class="text-center">Admin Login</h4>
                    </div>
                    <div class="card-body">
                        <?php if($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <?php if($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                        <hr>
                        <div class="text-center">
                            <a class="d-inline-block mb-2" data-bs-toggle="collapse" href="#createAccountBlock" role="button" aria-expanded="<?php echo isset($_POST['register_admin']) ? 'true' : 'false'; ?>" aria-controls="createAccountBlock">Create Account</a>
                        </div>
                        <div class="collapse <?php echo isset($_POST['register_admin']) ? 'show' : ''; ?>" id="createAccountBlock">
                        <form method="POST" action="" class="mt-2">
                            <input type="hidden" name="register_admin" value="1">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="register_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="register_email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="register_username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="register_username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="register_password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="register_password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-outline-primary w-100">Create Account</button>
                        </form>
                        </div>
                        <div class="text-center mt-3">
                            <a href="tenant_login.php">Tenant Login</a>
                            <span class="text-muted mx-2">|</span>
                            <a href="available_properties.php">Browse Available Properties</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
