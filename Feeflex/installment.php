
<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION["student_id"])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION["student_id"];
$message = "";
$error = "";

// Get student's latest fee
$stmt = $conn->prepare(
    "SELECT * FROM fees WHERE student_id = ? ORDER BY fee_id DESC LIMIT 1"
);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$fee = $stmt->get_result()->fetch_assoc();
$stmt->close();

$balance = 0;
$latest_request = null;

if ($fee) {
    // Calculate total paid amount
    $stmt = $conn->prepare(
        "SELECT COALESCE(SUM(amount), 0) AS paid
         FROM payments WHERE fee_id = ?"
    );
    $stmt->bind_param("i", $fee["fee_id"]);
    $stmt->execute();
    $paid = $stmt->get_result()->fetch_assoc()["paid"];
    $stmt->close();

    $balance = max(0, $fee["total_fee"] - $paid);

    // Get latest installment request
    $stmt = $conn->prepare(
        "SELECT * FROM installment_requests
         WHERE student_id = ? AND fee_id = ?
         ORDER BY request_id DESC LIMIT 1"
    );
    $stmt->bind_param("ii", $student_id, $fee["fee_id"]);
    $stmt->execute();
    $latest_request = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Submit a new request
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $number = (int)($_POST["installments"] ?? 0);
        $reason = trim($_POST["reason"] ?? "");

        if ($latest_request &&
            in_array($latest_request["status"], ["Pending", "Approved"])) {

            $error = "You already have a " .
                     strtolower($latest_request["status"]) .
                     " installment request.";

        } elseif ($balance <= 0) {
            $error = "You have no remaining fee balance.";

        } elseif ($number < 2 || $number > 12) {
            $error = "Choose between 2 and 12 installments.";

        } elseif ($reason == "") {
            $error = "Please enter a reason for your request.";

        } else {

            $installment_amount = $balance / $number;

            $stmt = $conn->prepare(
                "INSERT INTO installment_requests
                 (student_id, fee_id, requested_installments,
                  installment_amount, reason)
                 VALUES (?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "iiids",
                $student_id,
                $fee["fee_id"],
                $number,
                $installment_amount,
                $reason
            );

            if ($stmt->execute()) {
                $message = "Installment request submitted successfully!";

                // Refresh latest request
                $stmt2 = $conn->prepare(
                    "SELECT * FROM installment_requests
                     WHERE student_id = ? AND fee_id = ?
                     ORDER BY request_id DESC LIMIT 1"
                );
                $stmt2->bind_param("ii", $student_id, $fee["fee_id"]);
                $stmt2->execute();
                $latest_request = $stmt2->get_result()->fetch_assoc();
                $stmt2->close();

            } else {
                $error = "Unable to submit request. Please try again.";
            }

            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Installment Planner - FeeFlex</title>
    <link rel="stylesheet" href="style.css">

    <style>
        .container {
            max-width: 750px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 3px 12px #ddd;
        }

        h1, h2 {
            color: #172554;
            margin-bottom: 20px;
        }

        .balance {
            background: #eff6ff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .balance h2 {
            color: #2563eb;
            margin: 8px 0 0;
        }

        label {
            display: block;
            margin-top: 18px;
            font-weight: bold;
        }

        input, select, textarea {
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            margin-top: 20px;
            padding: 12px 22px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .status {
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            background: #f1f5f9;
        }

        .approved {
            background: #dcfce7;
            color: #166534;
        }

        .pending {
            background: #fef3c7;
            color: #92400e;
        }

        .rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .success {
            color: green;
            margin-bottom: 15px;
        }

        .error {
            color: red;
            margin-bottom: 15px;
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

    <h1>Flexible Installment Planner</h1>

    <?php if ($message): ?>
        <p class="success"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <?php if ($error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <?php if (!$fee): ?>

        <p>No fee has been assigned to your account yet.</p>

    <?php else: ?>

        <div class="balance">
            <p>Remaining Fee</p>
            <h2>₹<?php echo number_format($balance, 2); ?></h2>
        </div>

        <?php if ($latest_request &&
                  $latest_request["status"] == "Approved"): ?>

            <div class="status approved">
                <h2>Request Status: Approved</h2>
                <p>Your installment request has been approved by the admin.</p>
                <br>
                <p><strong>Number of Installments:</strong>
                    <?php echo $latest_request["requested_installments"]; ?>
                </p>
                <p><strong>Amount Per Installment:</strong>
                    ₹<?php echo number_format($latest_request["installment_amount"], 2); ?>
                </p>
                <p><strong>Admin Remarks:</strong>
                    <?php echo htmlspecialchars($latest_request["admin_remarks"] ?? "No remarks"); ?>
                </p>
            </div>

        <?php elseif ($latest_request &&
                     $latest_request["status"] == "Pending"): ?>

            <div class="status pending">
                <h2>Request Status: Pending</h2>
                <p>Your request is waiting for admin approval.</p>
                <p><strong>Requested Installments:</strong>
                    <?php echo $latest_request["requested_installments"]; ?>
                </p>
                <p><strong>Amount Per Installment:</strong>
                    ₹<?php echo number_format($latest_request["installment_amount"], 2); ?>
                </p>
            </div>

        <?php else: ?>

            <?php if ($latest_request &&
                      $latest_request["status"] == "Rejected"): ?>
                <div class="status rejected">
                    <h2>Request Status: Rejected</h2>
                    <p>Your previous request was rejected.</p>
                    <p><strong>Admin Remarks:</strong>
                        <?php echo htmlspecialchars($latest_request["admin_remarks"] ?? "No remarks"); ?>
                    </p>
                    <p>You can submit a new request below.</p>
                </div>
            <?php endif; ?>

            <?php if ($balance > 0): ?>

                <h2>Request Installment Plan</h2>

                <form method="POST">

                    <label>Number of Installments</label>

                    <select name="installments" required>
                        <option value="">Select Installments</option>
                        <?php for ($i = 2; $i <= 12; $i++): ?>
                            <option value="<?php echo $i; ?>">
                                <?php echo $i; ?> Installments
                            </option>
                        <?php endfor; ?>
                    </select>

                    <label>Reason for Request</label>

                    <textarea name="reason" rows="4"
                        placeholder="Enter your reason..." required></textarea>

                    <button type="submit">Submit Request</button>

                </form>

            <?php else: ?>

                <p>Your fee has been fully paid.</p>

            <?php endif; ?>

        <?php endif; ?>

    <?php endif; ?>

    <br>
    <a href="dashboard.php">Back to Dashboard</a>

</div>

</body>
</html>
