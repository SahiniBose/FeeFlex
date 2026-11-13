
<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION["student_id"])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION["student_id"];

$sql = "SELECT p.payment_id, p.amount, p.payment_date,
               p.receipt_no, p.remarks, f.academic_year
        FROM payments p
        JOIN fees f ON p.fee_id = f.fee_id
        WHERE f.student_id = ?
        ORDER BY p.payment_date DESC, p.payment_id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment History - FeeFlex</title>
    <link rel="stylesheet" href="style.css">

    <style>
        .container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 25px;
            background: white;
            border-radius: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 13px;
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

        .table-box {
            overflow-x: auto;
        }

        .receipt-btn {
            display: inline-block;
            background: #2563eb;
            color: white;
            padding: 8px 14px;
            text-decoration: none;
            border-radius: 5px;
        }

        .receipt-btn:hover {
            background: #1d4ed8;
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

<div class="container">

    <h1>My Payment History</h1>

    <p>Here you can view all your previous fee payments.</p>

    <div class="table-box">
        <table>

            <tr>
                <th>Payment ID</th>
                <th>Academic Year</th>
                <th>Amount Paid</th>
                <th>Payment Date</th>
                <th>Receipt Number</th>
                <th>Remarks</th>
                <th>Receipt</th>
            </tr>

            <?php if ($result->num_rows > 0): ?>

                <?php while ($payment = $result->fetch_assoc()): ?>

                    <tr>
                        <td>
                            <?php echo $payment["payment_id"]; ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($payment["academic_year"]); ?>
                        </td>

                        <td>
                            ₹<?php echo number_format($payment["amount"], 2); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($payment["payment_date"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($payment["receipt_no"] ?? "Not generated"); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($payment["remarks"] ?? ""); ?>
                        </td>

                        <td>
                            <a class="receipt-btn"
                               href="receipt.php?id=<?php echo $payment['payment_id']; ?>">
                                View Receipt
                            </a>
                        </td>
                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>
                    <td colspan="7">No payment history found.</td>
                </tr>

            <?php endif; ?>

        </table>
    </div>

    <br>

    <a href="dashboard.php">Back to Student Dashboard</a>

</div>

</body>
</html>

<?php
$stmt->close();
?>
