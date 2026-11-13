
<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION["student_id"])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION["student_id"];
$payment_id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

if ($payment_id <= 0) {
    die("Invalid receipt request.");
}

// Find payment belonging to the logged-in student
$stmt = $conn->prepare(
    "SELECT p.*, f.academic_year, f.total_fee,
            s.full_name, s.roll_no, s.email, s.department
     FROM payments p
     JOIN fees f ON p.fee_id = f.fee_id
     JOIN students s ON f.student_id = s.student_id
     WHERE p.payment_id = ? AND s.student_id = ?"
);

$stmt->bind_param("ii", $payment_id, $student_id);
$stmt->execute();

$result = $stmt->get_result();
$receipt = $result->fetch_assoc();
$stmt->close();

if (!$receipt) {
    die("Payment receipt not found.");
}

// Generate a receipt number if one does not exist
if (empty($receipt["receipt_no"])) {
    $receipt_no = "FF-" . date("Ymd") . "-" . $payment_id;

    $update = $conn->prepare(
        "UPDATE payments SET receipt_no = ?
         WHERE payment_id = ? AND receipt_no IS NULL"
    );

    $update->bind_param("si", $receipt_no, $payment_id);
    $update->execute();
    $update->close();

    $receipt["receipt_no"] = $receipt_no;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>FeeFlex Payment Receipt</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            padding: 30px;
        }

        .receipt {
            max-width: 650px;
            margin: auto;
            background: white;
            padding: 35px;
            border: 2px solid #172554;
            border-radius: 10px;
        }

        .heading {
            text-align: center;
            color: #172554;
            border-bottom: 2px solid #172554;
            padding-bottom: 15px;
        }

        .heading h1 {
            margin-bottom: 5px;
        }

        .details {
            margin-top: 25px;
            line-height: 2;
        }

        .amount {
            background: #eff6ff;
            padding: 15px;
            margin-top: 20px;
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            color: #172554;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            color: #64748b;
        }

        .buttons {
            text-align: center;
            margin-top: 25px;
        }

        button, a {
            padding: 12px 20px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            margin: 5px;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .receipt {
                border: 2px solid black;
            }

            .buttons {
                display: none;
            }
        }
    </style>
</head>

<body>

<div class="receipt">

    <div class="heading">
        <h1>FeeFlex</h1>
        <h3>Student Fee Payment Receipt</h3>
        <p>Flexible Student Fee Planning System</p>
    </div>

    <div class="details">

        <p><strong>Receipt Number:</strong>
            <?php echo htmlspecialchars($receipt["receipt_no"]); ?>
        </p>

        <p><strong>Payment ID:</strong>
            <?php echo $receipt["payment_id"]; ?>
        </p>

        <p><strong>Student Name:</strong>
            <?php echo htmlspecialchars($receipt["full_name"]); ?>
        </p>

        <p><strong>Roll Number:</strong>
            <?php echo htmlspecialchars($receipt["roll_no"]); ?>
        </p>

        <p><strong>Email:</strong>
            <?php echo htmlspecialchars($receipt["email"]); ?>
        </p>

        <p><strong>Department:</strong>
            <?php echo htmlspecialchars($receipt["department"] ?? ""); ?>
        </p>

        <p><strong>Academic Year:</strong>
            <?php echo htmlspecialchars($receipt["academic_year"]); ?>
        </p>

        <p><strong>Payment Date:</strong>
            <?php echo htmlspecialchars($receipt["payment_date"]); ?>
        </p>

        <p><strong>Payment Remarks:</strong>
            <?php echo htmlspecialchars($receipt["remarks"] ?? ""); ?>
        </p>

    </div>

    <div class="amount">
        Amount Paid: ₹<?php echo number_format($receipt["amount"], 2); ?>
    </div>

    <div class="footer">
        <p>Thank you for your payment!</p>
        <p>This is a computer-generated receipt.</p>
    </div>

</div>

<div class="buttons">
    <button onclick="window.print()">Print Receipt</button>
    <a href="payment_history.php">Back to Payment History</a>
</div>

</body>
</html>
