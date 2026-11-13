
<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit();
}

$sql = "SELECT student_id, full_name, roll_no, email,
        phone, department, semester, family_income,
        marks_percentage FROM students ORDER BY student_id DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Students - FeeFlex</title>
    <link rel="stylesheet" href="style.css">

    <style>
        .student-container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #172554;
            color: white;
        }

        tr:nth-child(even) {
            background: #f1f5f9;
        }
    </style>
</head>
<body>

<header>
    <div class="logo">Fee<span>Flex</span></div>
    <nav>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="admin_logout.php">Logout</a>
    </nav>
</header>

<div class="student-container">
    <h1>Registered Students</h1>

    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Roll Number</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Department</th>
            <th>Semester</th>
            <th>Family Income</th>
            <th>Marks</th>
        </tr>

        <?php while ($student = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $student["student_id"]; ?></td>
            <td><?php echo htmlspecialchars($student["full_name"]); ?></td>
            <td><?php echo htmlspecialchars($student["roll_no"]); ?></td>
            <td><?php echo htmlspecialchars($student["email"]); ?></td>
            <td><?php echo htmlspecialchars($student["phone"] ?? ""); ?></td>
            <td><?php echo htmlspecialchars($student["department"] ?? ""); ?></td>
            <td><?php echo $student["semester"]; ?></td>
            <td>₹<?php echo number_format($student["family_income"], 2); ?></td>
            <td><?php echo $student["marks_percentage"]; ?>%</td>
        </tr>
        <?php endwhile; ?>
    </table>

    <br>
    <a href="admin_dashboard.php">Back to Admin Dashboard</a>
</div>

</body>
</html>
