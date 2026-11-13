
<?php
session_start();

if (!isset($_SESSION["student_id"])) {
    header("Location: login.php");
    exit();
}

$name = $_SESSION["student_name"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard - FeeFlex</title>
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
            padding: 35px;
            border-radius: 12px;
            margin-bottom: 30px;
        }

        .welcome h1 {
            margin-bottom: 12px;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .feature-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 3px 10px #ddd;
        }

        .feature-card h2 {
            color: #172554;
            font-size: 20px;
            margin-bottom: 12px;
        }

        .feature-card p {
            color: #64748b;
            margin-bottom: 18px;
        }

        .feature-card a {
            display: inline-block;
            background: #2563eb;
            color: white;
            padding: 10px 18px;
            border-radius: 5px;
            text-decoration: none;
        }

        .feature-card a:hover {
            background: #1d4ed8;
        }
    </style>
</head>

<body>

<header>
    <div class="logo">Fee<span>Flex</span></div>
    <nav>
        <a href="index.php">Home</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="dashboard">

    <div class="welcome">
        <h1>Welcome, <?php echo htmlspecialchars($name); ?>!</h1>
        <p>Manage your student fees and financial assistance in one place.</p>
    </div>

    <div class="features">

        <div class="feature-card">
            <h2>My Fee Details</h2>
            <p>Check your total fees, paid amount and remaining balance.</p>
            <a href="fees.php">View Fee Details</a>
        </div>

        <div class="feature-card">
            <h2>Installment Plan</h2>
            <p>Request permission to pay your fees in installments.</p>
            <a href="installment.php">Request Installment</a>
        </div>

        <div class="feature-card">
            <h2>Scholarships</h2>
            <p>Find scholarships matching your income and academic marks.</p>
            <a href="scholarships.php">Find Scholarships</a>
        </div>

        <div class="feature-card">
            <h2>Financial Assistance</h2>
            <p>Request financial support for your education.</p>
            <a href="assistance.php">Request Assistance</a>
        </div>

        <div class="feature-card">
            <h2>Payment History</h2>
            <p>View your previous payments and printable receipts.</p>
            <a href="payment_history.php">View Payment History</a>
        </div>

    </div>

</div>

<footer>
    <p>© 2026 FeeFlex. All Rights Reserved.</p>
</footer>

</body>
</html>
