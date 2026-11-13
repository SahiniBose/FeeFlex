
<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION["student_id"])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION["student_id"];
$message = "";

$stmt = $conn->prepare(
    "SELECT fee_id FROM fees WHERE student_id = ?
     ORDER BY fee_id DESC LIMIT 1"
);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$fee = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reason = trim($_POST["reason"]);
    $amount = (float)$_POST["amount"];
    $fee_id = $fee ? $fee["fee_id"] : null;

    if ($reason !== "" && $amount > 0) {
        $stmt = $conn->prepare(
            "INSERT INTO assistance_requests
            (student_id, fee_id, reason, requested_amount)
            VALUES (?, ?, ?, ?)"
        );

        $stmt->bind_param("iisd", $student_id, $fee_id, $reason, $amount);

        if ($stmt->execute()) {
            $message = "Financial assistance request submitted successfully!";
        } else {
            $message = "Unable to submit your request. Please try again.";
        }

        $stmt->close();
    } else {
        $message = "Please enter a reason and a valid amount.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Assistance - FeeFlex</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .assistance-container {
            max-width: 650px;
            margin: 40px auto;
            padding: 25px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px #ddd;
        }
        .assistance-container input,
        .assistance-container textarea {
            width: 100%;
            padding: 12px;
            margin: 12px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
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

<div class="assistance-container">
    <h1>Financial Assistance Request</h1>

    <p><?php echo htmlspecialchars($message); ?></p>

    <form method="POST">
        <label>Amount Requested (₹)</label>
        <input type="number" name="amount" min="1" step="0.01"
               placeholder="Enter amount" required>

        <label>Reason for Assistance</label>
        <textarea name="reason" rows="5"
                  placeholder="Explain why you need financial assistance"
                  required></textarea>

        <button type="submit">Submit Request</button>
    </form>

    <p>Your request will be reviewed by the college administrator.</p>

    <a href="dashboard.php">Back to Dashboard</a>
</div>

</body>
</html>
