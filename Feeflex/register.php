
<?php
require_once "config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["full_name"]);
    $roll = trim($_POST["roll_no"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $department = trim($_POST["department"]);
    $semester = (int)$_POST["semester"];
    $income = (float)$_POST["family_income"];
    $marks = (float)$_POST["marks_percentage"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    $sql = "INSERT INTO students
    (full_name, roll_no, email, phone, department, semester,
    family_income, marks_percentage, password_hash)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssssidds",
        $name, $roll, $email, $phone, $department,
        $semester, $income, $marks, $password
    );

    if ($stmt->execute()) {
        $message = "Registration successful! You can now log in.";
    } else {
        $message = "Registration failed. Roll number or email may already exist.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Registration - FeeFlex</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h1>Student Registration</h1>

<p><?php echo htmlspecialchars($message); ?></p>

<form method="POST">
    <input type="text" name="full_name" placeholder="Full Name" required>
    <input type="text" name="roll_no" placeholder="Roll Number" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="text" name="phone" placeholder="Phone Number">
    <input type="text" name="department" placeholder="Department" required>
    <input type="number" name="semester" placeholder="Semester" min="1" required>
    <input type="number" name="family_income" placeholder="Annual Family Income" min="0" step="0.01" required>
    <input type="number" name="marks_percentage" placeholder="Marks Percentage" min="0" max="100" step="0.01" required>
    <input type="password" name="password" placeholder="Create Password" required>
    <button type="submit">Register</button>
</form>

</body>
</html>
