
<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION["student_id"])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION["student_id"];

$stmt = $conn->prepare(
    "SELECT family_income, marks_percentage
     FROM students WHERE student_id = ?"
);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

$income = $student["family_income"];
$marks = $student["marks_percentage"];

$stmt = $conn->prepare(
    "SELECT * FROM scholarships
     WHERE (income_limit IS NULL OR income_limit >= ?)
     AND (minimum_marks IS NULL OR minimum_marks <= ?)"
);
$stmt->bind_param("dd", $income, $marks);
$stmt->execute();
$scholarships = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scholarship Finder - FeeFlex</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .scholarship-container {
            max-width: 850px;
            margin: 40px auto;
            padding: 25px;
            background: white;
            border-radius: 10px;
        }
        .scholarship-card {
            background: #f1f5f9;
            padding: 20px;
            margin: 15px 0;
            border-radius: 8px;
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

<div class="scholarship-container">
    <h1>Scholarship Finder</h1>
    <p>Scholarships matching your profile:</p>

    <?php if ($scholarships->num_rows > 0): ?>

        <?php while ($row = $scholarships->fetch_assoc()): ?>
            <div class="scholarship-card">
                <h2><?php echo htmlspecialchars($row["scholarship_name"]); ?></h2>

                <p>
                    <strong>Income Limit:</strong>
                    <?php
                    echo $row["income_limit"] === null
                        ? "No limit specified"
                        : "₹" . number_format($row["income_limit"], 2);
                    ?>
                </p>

                <p>
                    <strong>Minimum Marks:</strong>
                    <?php
                    echo $row["minimum_marks"] === null
                        ? "Not specified"
                        : $row["minimum_marks"] . "%";
                    ?>
                </p>

                <p><?php echo htmlspecialchars($row["description"] ?? ""); ?></p>

                <?php if (!empty($row["application_link"])): ?>
                    <a href="<?php echo htmlspecialchars($row["application_link"]); ?>"
                       target="_blank" rel="noopener noreferrer">
                        Visit Scholarship Website
                    </a>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>

    <?php else: ?>
        <p>No matching scholarships found at the moment.</p>
    <?php endif; ?>

    <p><small>These are potential matches based on stored criteria, not confirmation of eligibility.</small></p>

    <a href="dashboard.php">Back to Dashboard</a>
</div>

</body>
</html>

<?php
$stmt->close();
?>
