
<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit();
}

$total_students = $conn->query("SELECT COUNT(*) AS total FROM students")->fetch_assoc()["total"];

$total_payments = $conn->query("SELECT COALESCE(SUM(amount), 0) AS total FROM payments")->fetch_assoc()["total"];

$pending_installments = $conn->query("SELECT COUNT(*) AS total FROM installment_requests WHERE status='Pending'")->fetch_assoc()["total"];

$pending_assistance = $conn->query("SELECT COUNT(*) AS total FROM assistance_requests WHERE status='Pending'")->fetch_assoc()["total"];

$total_scholarships = $conn->query("SELECT COUNT(*) AS total FROM scholarships")->fetch_assoc()["total"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - FeeFlex</title>
    <link rel="stylesheet" href="style.css">

    <style>
        .dashboard {
            max-width: 1100px;
            margin: 40px auto;
            padding: 20px;
        }

        .welcome {
            background: #172554;
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 18px;
            margin-bottom: 30px;
        }

        .stat-card, .manage-card {
            background: white;
            padding: 22px;
            border-radius: 10px;
            box-shadow: 0 3px 10px #ddd;
        }

        .stat-card h2 {
            color: #2563eb;
            font-size: 28px;
            margin-bottom: 8px;
        }

        .stat-card p {
            color: #64748b;
        }

        .management {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
        }

        .manage-card h3 {
            color: #172554;
            margin-bottom: 12px;
        }

        .manage-card p {
            color: #64748b;
            margin-bottom: 16px;
        }

        .manage-card a {
            display: inline-block;
            background: #2563eb;
            color: white;
            padding: 10px 16px;
            border-radius: 5px;
            text-decoration: none;
        }

        .manage-card a:hover {
            background: #1d4ed8;
        }
    </style>
</head>

<body>

<header>
    <div class="logo">Fee<span>Flex</span></div>
    <nav>
        <a href="index.php">Home</a>
        <a href="admin_logout.php">Logout</a>
    </nav>
</header>

<div class="dashboard">

    <div class="welcome">
        <h1>Welcome Administrator!</h1>
        <p>Manage students, fees, payments and financial assistance.</p>
    </div>

    <h2>Dashboard Overview</h2>
    <br>

    <div class="stats">

        <div class="stat-card">
            <h2><?php echo $total_students; ?></h2>
            <p>Total Registered Students</p>
        </div>

        <div class="stat-card">
            <h2>₹<?php echo number_format((float)$total_payments, 2); ?></h2>
            <p>Total Payment Received</p>
        </div>

        <div class="stat-card">
            <h2><?php echo $pending_installments; ?></h2>
            <p>Pending Installment Requests</p>
        </div>

        <div class="stat-card">
            <h2><?php echo $pending_assistance; ?></h2>
            <p>Pending Assistance Requests</p>
        </div>

        <div class="stat-card">
            <h2><?php echo $total_scholarships; ?></h2>
            <p>Total Scholarships</p>
        </div>

    </div>

    <h2>Management Section</h2>
    <br>

    <div class="management">

        <div class="manage-card">
            <h3>Manage Students</h3>
            <p>View registered student information.</p>
            <a href="manage_students.php">View Students</a>
        </div>

        <div class="manage-card">
            <h3>Manage Fees & Payments</h3>
            <p>Assign fees and record student payments.</p>
            <a href="manage_fees.php">Manage Fees</a>
        </div>

        <div class="manage-card">
            <h3>Installment Requests</h3>
            <p>Approve or reject installment requests.</p>
            <a href="manage_installments.php">Manage Requests</a>
        </div>

        <div class="manage-card">
            <h3>Financial Assistance</h3>
            <p>Review and manage assistance requests.</p>
            <a href="manage_assistance.php">Manage Assistance</a>
        </div>

        <div class="manage-card">
            <h3>Manage Scholarships</h3>
            <p>Add and view scholarship opportunities.</p>
            <a href="manage_scholarships.php">Manage Scholarships</a>
        </div>

    </div>

</div>

<footer>
    <p>© 2026 FeeFlex. All Rights Reserved.</p>
</footer>

</body>
</html>
