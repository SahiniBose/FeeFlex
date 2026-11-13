
<?php
session_start();
require_once "config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare(
        "SELECT admin_id, username, password_hash
         FROM admins WHERE username = ?"
    );

    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $admin = $result->fetch_assoc();

        if (password_verify($password, $admin["password_hash"])) {
            session_regenerate_id(true);
            $_SESSION["admin_id"] = $admin["admin_id"];
            $_SESSION["admin_username"] = $admin["username"];

            header("Location: admin_dashboard.php");
            exit();
        } else {
            $message = "Incorrect password.";
        }
    } else {
        $message = "Admin account not found.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - FeeFlex</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h1>Admin Login</h1>

<p><?php echo htmlspecialchars($message); ?></p>

<form method="POST">
    <input type="text" name="username"
           placeholder="Enter Username" required>

    <input type="password" name="password"
           placeholder="Enter Password" required>

    <button type="submit">Admin Login</button>
</form>

</body>
</html>
