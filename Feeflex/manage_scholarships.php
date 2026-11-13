
<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit();
}

$message = "";

// Add scholarship
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["scholarship_name"]);
    $income = $_POST["income_limit"] !== "" ? (float)$_POST["income_limit"] : null;
    $marks = $_POST["minimum_marks"] !== "" ? (float)$_POST["minimum_marks"] : null;
    $description = trim($_POST["description"]);
    $link = trim($_POST["application_link"]);

    if ($name != "" && ($income === null || $income >= 0)
        && ($marks === null || ($marks >= 0 && $marks <= 100))) {

        $stmt = $conn->prepare(
            "INSERT INTO scholarships
            (scholarship_name, income_limit, minimum_marks,
             description, application_link)
            VALUES (?, ?, ?, ?, ?)"
        );

        $stmt->bind_param("sddss", $name, $income, $marks, $description, $link);

        if ($stmt->execute()) {
            $message = "Scholarship added successfully!";
        } else {
            $message = "Could not add scholarship.";
        }

        $stmt->close();
    } else {
        $message = "Please enter valid scholarship details.";
    }
}

// Get scholarships
$result = $conn->query("SELECT * FROM scholarships ORDER BY scholarship_id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Scholarships - FeeFlex</title>
    <link rel="stylesheet" href="style.css">

    <style>
        .container {
            max-width: 1000px;
            margin: 35px auto;
            padding: 20px;
        }

        .box {
            background: white;
            padding: 25px;
            margin-bottom: 25px;
            border-radius: 10px;
        }

        input, textarea {
            display: block;
            width: 100%;
            padding: 11px;
            margin: 12px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background: #2563eb;
            color: white;
            border: none;
            padding: 12px 22px;
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

        .table-box {
            overflow-x: auto;
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

    <h1>Manage Scholarships</h1>

    <p class="message">
        <?php echo htmlspecialchars($message); ?>
    </p>

    <div class="box">
        <h2>Add New Scholarship</h2>

        <form method="POST">

            <label>Scholarship Name</label>
            <input type="text" name="scholarship_name"
                   placeholder="Enter scholarship name" required>

            <label>Annual Family Income Limit (₹)</label>
            <input type="number" name="income_limit"
                   min="0" step="0.01"
                   placeholder="Leave blank if no limit">

            <label>Minimum Marks Percentage</label>
            <input type="number" name="minimum_marks"
                   min="0" max="100" step="0.01"
                   placeholder="Leave blank if no minimum">

            <label>Description</label>
            <textarea name="description"
                      placeholder="Enter scholarship details"></textarea>

            <label>Application Link (Optional)</label>
            <input type="url" name="application_link"
                   placeholder="https://example.com">

            <button type="submit">Add Scholarship</button>

        </form>
    </div>

    <div class="box">
        <h2>Existing Scholarships</h2>

        <div class="table-box">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Income Limit</th>
                    <th>Minimum Marks</th>
                    <th>Description</th>
                </tr>

                <?php while ($scholarship = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $scholarship["scholarship_id"]; ?></td>
                    <td><?php echo htmlspecialchars($scholarship["scholarship_name"]); ?></td>
                    <td>
                        <?php
                        echo $scholarship["income_limit"] === null
                            ? "No limit"
                            : "₹" . number_format($scholarship["income_limit"], 2);
                        ?>
                    </td>
                    <td>
                        <?php
                        echo $scholarship["minimum_marks"] === null
                            ? "No minimum"
                            : $scholarship["minimum_marks"] . "%";
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($scholarship["description"] ?? ""); ?></td>
                </tr>
                <?php endwhile; ?>

            </table>
        </div>
    </div>

    <a href="admin_dashboard.php">Back to Admin Dashboard</a>

</div>

</body>
</html>
