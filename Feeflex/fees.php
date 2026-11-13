
<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION["student_id"])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION["student_id"];

$sql = "SELECT fee_id, academic_year, total_fee
        FROM fees
        WHERE student_id = ?
        ORDER BY fee_id DESC
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

$fee = $result->fetch_assoc();

$total_fee = 0;
$paid = 0;
$balance = 0;

if ($fee) {
    $fee_id = $fee["fee_id"];
    $total_fee = (float)$fee["total_fee"];

    $payment_sql = "SELECT COALESCE(SUM(amount), 0) AS paid
                    FROM payments WHERE fee_id = ?";

    $payment_stmt = $conn->prepare($payment_sql);
    $payment_stmt->bind_param("i", $fee_id);
    $payment_stmt->execute();

    $payment_result = $payment_stmt->get_result();
    $payment_data = $payment_result->fetch_assoc();

    $paid = (float)$payment_data["paid"];
    $payment_stmt->close();
}

$balance = max(0, $total_fee - $paid);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Fees - FeeFlex</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .fee-container {
            max-width: 700px;
            margin: 40px auto;
            padding: 25px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px #ddd;
        }
        .fee-item {
            padding: 18px;
            margin: 12px 0;
            background: #f1f5f9;
            border-radius: 8px;
            font-size: 18px;
        }
    </style>
</head>
<body>

<header>
    <div class="logo">Fee<span>Flex</span></div>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="fee-container">
    <h1>My Fee Details</h1>

    <?php if ($fee): ?>
        <div class="fee-item">
            Academic Year: <?php echo htmlspecialchars($fee["academic_year"] ?? "Not specified"); ?>
        </div>

        <div class="fee-item">
            Total Fee: ₹<?php echo number_format($total_fee, 2); ?>
        </div>

        <div class="fee-item">
            Amount Paid: ₹<?php echo number_format($paid, 2); ?>
        </div>

        <div class="fee-item">
            Remaining Balance: ₹<?php echo number_format($balance, 2); ?>
        </div>
    <?php else: ?>
        <p>No fee has been assigned to your account yet.</p>
    <?php endif; ?>

    <br>
    <a href="dashboard.php">Back to Dashboard</a>
</div>

</body>
</html>
