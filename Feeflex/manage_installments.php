
<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit();
}

$message = "";

// Approve or reject installment request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $request_id = (int)$_POST["request_id"];
    $status = $_POST["status"];
    $remarks = trim($_POST["admin_remarks"]);

    if (in_array($status, ["Approved", "Rejected"])) {
        $stmt = $conn->prepare(
            "UPDATE installment_requests
             SET status = ?, admin_remarks = ?
             WHERE request_id = ? AND status = 'Pending'"
        );

        $stmt->bind_param("ssi", $status, $remarks, $request_id);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $message = "Request updated successfully!";
        } else {
            $message = "Request could not be updated.";
        }

        $stmt->close();
    }
}

// Get all installment requests
$sql = "SELECT r.*, s.full_name, s.roll_no,
               f.total_fee
        FROM installment_requests r
        JOIN students s ON r.student_id = s.student_id
        JOIN fees f ON r.fee_id = f.fee_id
        ORDER BY r.requested_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Installments - FeeFlex</title>
    <link rel="stylesheet" href="style.css">

    <style>
        .container {
            max-width: 1100px;
            margin: 35px auto;
            padding: 20px;
        }

        .request-box {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 10px;
            box-shadow: 0 2px 8px #ddd;
        }

        .request-box p {
            margin: 10px 0;
        }

        textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            padding: 10px 20px;
            margin: 5px;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
        }

        .approve {
            background: #16a34a;
        }

        .reject {
            background: #dc2626;
        }

        .message {
            color: green;
            margin: 15px 0;
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

    <h1>Manage Installment Requests</h1>

    <p class="message">
        <?php echo htmlspecialchars($message); ?>
    </p>

    <?php if ($result->num_rows > 0): ?>

        <?php while ($request = $result->fetch_assoc()): ?>

            <div class="request-box">

                <h2>
                    <?php echo htmlspecialchars($request["full_name"]); ?>
                </h2>

                <p><strong>Roll Number:</strong>
                    <?php echo htmlspecialchars($request["roll_no"]); ?>
                </p>

                <p><strong>Total Fee:</strong>
                    ₹<?php echo number_format($request["total_fee"], 2); ?>
                </p>

                <p><strong>Requested Installments:</strong>
                    <?php echo $request["requested_installments"]; ?>
                </p>

                <p><strong>Installment Amount:</strong>
                    ₹<?php echo number_format($request["installment_amount"], 2); ?>
                </p>

                <p><strong>Reason:</strong>
                    <?php echo nl2br(htmlspecialchars($request["reason"])); ?>
                </p>

                <p><strong>Status:</strong>
                    <?php echo htmlspecialchars($request["status"]); ?>
                </p>

                <?php if ($request["status"] == "Pending"): ?>

                    <form method="POST">

                        <input type="hidden" name="request_id"
                               value="<?php echo $request["request_id"]; ?>">

                        <textarea name="admin_remarks"
                                  placeholder="Admin remarks (optional)"></textarea>

                        <button class="approve" type="submit"
                                name="status" value="Approved">
                            Approve
                        </button>

                        <button class="reject" type="submit"
                                name="status" value="Rejected">
                            Reject
                        </button>

                    </form>

                <?php else: ?>

                    <p><strong>Admin Remarks:</strong>
                        <?php echo htmlspecialchars($request["admin_remarks"] ?? "None"); ?>
                    </p>

                <?php endif; ?>

            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <p>No installment requests found.</p>

    <?php endif; ?>

    <a href="admin_dashboard.php">Back to Admin Dashboard</a>

</div>

</body>
</html>
