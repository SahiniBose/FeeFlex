
<?php
require_once "config/database.php";

$message = "";

$result = $conn->query("SELECT COUNT(*) AS total FROM admins");
$row = $result->fetch_assoc();

if ($row["total"] == 0) {
    $username = "admin";
    $password = password_hash("Admin@123", PASSWORD_DEFAULT);

    $stmt = $conn->prepare(
        "INSERT INTO admins (username, password_hash) VALUES (?, ?)"
    );
    $stmt->bind_param("ss", $username, $password);

    if ($stmt->execute()) {
        $message = "Admin account created successfully!";
    } else {
        $message = "Unable to create admin account.";
    }

    $stmt->close();
} else {
    $message = "An admin account already exists.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>FeeFlex Admin Setup</title>
</head>
<body>
    <h1>FeeFlex Admin Setup</h1>
    <p><?php echo htmlspecialchars($message); ?></p>
    <p>Username: admin</p>
    <p>Password: Admin@123</p>
    <p>After setup, delete setup_admin.php from your project folder.</p>
</body>
</html>
