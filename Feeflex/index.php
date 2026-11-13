
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FeeFlex - Student Fee Management System</title>
    <link rel="stylesheet" href="style.css">

    <style>
        .hero {
            min-height: 500px;
        }

        .buttons a {
            display: inline-block;
            padding: 13px 28px;
            margin: 8px;
            border-radius: 6px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            font-size: 16px;
        }

        .buttons a:hover {
            background: #1d4ed8;
        }

        .features {
            max-width: 1100px;
            margin: 20px auto 50px;
            padding: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .feature {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 3px 10px #ddd;
        }

        .feature h3 {
            color: #172554;
            margin-bottom: 12px;
        }

        .feature p {
            color: #64748b;
            line-height: 1.5;
        }

        .section-title {
            text-align: center;
            color: #172554;
            margin-top: 35px;
        }
    </style>
</head>

<body>

<header>
    <div class="logo">Fee<span>Flex</span></div>

    <nav>
        <a href="index.php">Home</a>
        <a href="#features">Features</a>
        <a href="#contact">Contact</a>
        <a href="admin_login.php">Admin Login</a>
    </nav>
</header>

<section class="hero">
    <h1>Welcome to FeeFlex</h1>

    <p>
        Flexible Student Fee Planning and Financial Assistance System.
        Manage your fees, request installments and explore scholarships.
    </p>

    <div class="buttons">
        <a href="login.php">Student Login</a>
        <a href="register.php">Student Registration</a>
        <a href="admin_login.php">Admin Login</a>
    </div>
</section>

<h2 class="section-title" id="features">Our Features</h2>

<section class="features">

    <div class="feature">
        <h3>Student Registration</h3>
        <p>Create an account and securely access your student dashboard.</p>
    </div>

    <div class="feature">
        <h3>Fee Management</h3>
        <p>View your total fees, payments and remaining balance.</p>
    </div>

    <div class="feature">
        <h3>Installment Facility</h3>
        <p>Request permission to pay your fees in installments.</p>
    </div>

    <div class="feature">
        <h3>Scholarship Finder</h3>
        <p>Explore scholarship opportunities based on eligibility.</p>
    </div>

    <div class="feature">
        <h3>Financial Assistance</h3>
        <p>Request financial support for your education.</p>
    </div>

    <div class="feature">
        <h3>Payment Receipts</h3>
        <p>View your payment history and print receipts.</p>
    </div>

</section>

<footer id="contact">
    <p>Contact: support@feeflex.com</p>
    <p>© 2026 FeeFlex. All Rights Reserved.</p>
</footer>

</body>
</html>
