
<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit();
}

$message = "";

// Assign or update fee
if (isset($_POST["assign_fee"])) {
    $student_id = (int)$_POST["student_id"];
    $academic_year = trim($_POST["academic_year"]);
    $total_fee = (float)$_POST["total_fee"];

    if ($student_id > 0 && $academic_year != "" && $total_fee > 0) {
        $stmt = $conn->prepare(
            "INSERT INTO fees (student_id, academic_year, total_fee)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE total_fee = VALUES(total_fee)"
        );

        $stmt->bind_param("isd", $student_id, $academic_year, $total_fee);

        if ($stmt->execute()) {
            $message = "Fee assigned or updated successfully!";
        } else {
            $message = "Failed to assign fee.";
        }

        $stmt->close();
    } else {
        $message = "Please enter valid fee details.";
    }
}

// Record payment
if (isset($_POST["record_payment"])) {
    $fee_id = (int)$_POST["fee_id"];
    $amount = (float)$_POST["amount"];
    $payment_date = $_POST["payment_date"];
    $remarks = trim($_POST["remarks"]);

    $stmt = $conn->prepare(
        "SELECT total_fee FROM fees WHERE fee_id = ?"
    );
    $stmt->bind_param("i", $fee_id);
    $stmt->execute();
    $fee_result = $stmt->get_result();
    $fee = $fee_result->fetch_assoc();
    $stmt->close();

    if ($fee && $amount > 0 && $payment_date != "") {
        $stmt = $conn->prepare(
            "SELECT COALESCE(SUM(amount), 0) AS paid
             FROM payments WHERE fee_id = ?"
        );
        $stmt->bind_param("i", $fee_id);
        $stmt->execute();
        $paid_result = $stmt->get_result();
        $paid = (float)$paid_result->fetch_assoc()["paid"];
        $stmt->close();

        $balance = (float)$fee["total_fee"] - $paid;

        if ($amount <= $balance) {
            $stmt = $conn->prepare(
                "INSERT INTO payments
                (fee_id, amount, payment_date, recorded_by, remarks)
                VALUES (?, ?, ?, ?, ?)"
            );

            $admin_id = $_SESSION["admin_id"];
            $stmt->bind_param(
                "idsis",
                $fee_id,
                $amount,
                $payment_date,
                $admin_id,
                $remarks
            );

            if ($stmt->execute()) {
                $message = "Payment recorded successfully!";
            } else {
                $message = "Failed to record payment.";
            }

            $stmt->close();
        } else {
            $message = "Payment cannot be greater than the remaining balance of ₹" . number_format($balance, 2);
        }
    } else {
        $message = "Please enter valid payment details.";
    }
}

$students = $conn->query(
    "SELECT student_id, full_name, roll_no FROM students ORDER BY full_name"
);

$fees = $conn->query(
    "SELECT f.fee_id, f.academic_year, f.total_fee,
            s.full_name, s.roll_no,
            COALESCE(SUM(p.amount), 0) AS paid
     FROM fees f
     JOIN students s ON f.student_id = s.student_id
     LEFT JOIN payments p ON f.fee_id = p.fee_id
     GROUP BY f.fee_id
     ORDER BY f.fee_id DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Fees - FeeFlex</title>
    <link rel="stylesheet" href="style.css">

    <style>
        .container {
            max-width: 1100px;
            margin: 35px auto;
            padding: 25px;
        }

        .fee-box {
            background: white;
            padding: 25px;
            margin-bottom: 25px;
            border-radius: 10px;
        }

        input, select {
            display: block;
            width: 100%;
            padding: 11px;
            margin: 12px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            padding: 12px 22px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
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

        .table-box {
            overflow-x: auto;
        }

        .message {
            color: #166534;
            margin-bottom: 15px;
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

<div class="container">

    <h1>Manage Student Fees</h1>
    <p class="message">
        <?php echo htmlspecialchars($message); ?>
    </p>

    <div class="fee-box">
        <h2>Assign Student Fee</h2>

        <form method="POST">
            <label>Select Student</label>

            <select name="student_id" required>
                <option value="">Choose Student</option>

                <?php while ($student = $students->fetch_assoc()): ?>
                    <option value="<?php echo $student["student_id"]; ?>">
                        <?php echo htmlspecialchars($student["full_name"]); ?>
                        (<?php echo htmlspecialchars($student["roll_no"]); ?>)
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Academic Year</label>
            <input type="text" name="academic_year"
                   placeholder="Example: 2026-2027" required>

            <label>Total Fee (₹)</label>
            <input type="number" name="total_fee"
                   min="1" step="0.01" required>

            <button type="submit" name="assign_fee">
                Assign Fee
            </button>
        </form>
    </div>

    <div class="fee-box">
        <h2>Record Student Payment</h2>

        <form method="POST">
            <label>Select Fee Record</label>

            <select name="fee_id" required>
                <option value="">Choose Fee Record</option>

                <?php
                $fee_options = $conn->query(
                    "SELECT f.fee_id, s.full_name, f.academic_year
                     FROM fees f
                     JOIN students s ON f.student_id = s.student_id
                     ORDER BY f.fee_id DESC"
                );

                while ($option = $fee_options->fetch_assoc()):
                ?>
                    <option value="<?php echo $option["fee_id"]; ?>">
                        <?php echo htmlspecialchars($option["full_name"]); ?>
                        - <?php echo htmlspecialchars($option["academic_year"]); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Payment Amount (₹)</label>
            <input type="number" name="amount"
                   min="0.01" step="0.01" required>

            <label>Payment Date</label>
            <input type="date" name="payment_date"
                   value="<?php echo date('Y-m-d'); ?>" required>

            <label>Remarks</label>
            <input type="text" name="remarks"
                   placeholder="Optional remarks">

            <button type="submit" name="record_payment">
                Record Payment
            </button>
        </form>
    </div>

    <div class="fee-box">
        <h2>All Fee Records</h2>

        <div class="table-box">
            <table>
                <tr>
                    <th>Student</th>
                    <th>Roll Number</th>
                    <th>Academic Year</th>
                    <th>Total Fee</th>
                    <th>Paid</th>
                    <th>Remaining Balance</th>
                </tr>

                <?php while ($fee = $fees->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($fee["full_name"]); ?></td>
                    <td><?php echo htmlspecialchars($fee["roll_no"]); ?></td>
                    <td><?php echo htmlspecialchars($fee["academic_year"]); ?></td>
                    <td>₹<?php echo number_format($fee["total_fee"], 2); ?></td>
                    <td>₹<?php echo number_format($fee["paid"], 2); ?></td>
                    <td>₹<?php echo number_format($fee["total_fee"] - $fee["paid"], 2); ?></td>
                </tr>
                <?php endwhile; ?>

            </table>
        </div>
    </div>

    <a href="admin_dashboard.php">Back to Admin Dashboard</a>

</div>

</body>
</html>
