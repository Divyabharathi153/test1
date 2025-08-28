<?php
include("database.php");
session_start();

$message = "";
$color = "";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Sanitize input
        $fullName = filter_input(INPUT_POST, "full_name", FILTER_SANITIZE_STRING);
        $lastName = filter_input(INPUT_POST, "last_name", FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
        $mobile = filter_input(INPUT_POST, "mobile", FILTER_SANITIZE_NUMBER_INT);
        $age = filter_input(INPUT_POST, "age", FILTER_VALIDATE_INT);
        $gender = $_POST["gender"];
        $course = $_POST["course"];

        // Check if email already exists
        $checkQuery = "SELECT id FROM register WHERE email = ?";
        $stmt = mysqli_prepare($conn, $checkQuery);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $message = "Email already registered. Try logging in.";
            $color = "red";
        } else {
            mysqli_stmt_close($stmt); // Close previous statement

            // Insert new user
            $insertQuery = "INSERT INTO register (full_name, last_name, email, mobile, age, gender, course)
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
            $insertStmt = mysqli_prepare($conn, $insertQuery);
            mysqli_stmt_bind_param($insertStmt, "sssisss", $fullName, $lastName, $email, $mobile, $age, $gender, $course);
            mysqli_stmt_execute($insertStmt);

            $_SESSION["user_id"] = mysqli_insert_id($conn);
            $_SESSION["user_name"] = $fullName;

            $message = "Registered successfully!";
            $color = "green";

            mysqli_stmt_close($insertStmt);
        }

        mysqli_close($conn);
    }
} catch (mysqli_sql_exception $e) {
    $message = "Database error: " . $e->getMessage();
    $color = "red";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register</title>
  <link rel="stylesheet" href="style.css?v=2">
</head>
<body>
    <form method="POST">
    <?php if (!empty($message)) : ?>
        <p style="color: <?= $color ?>; text-align: center; margin: 0;">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>
  <div class="container">
    <form id="registerForm" class="form" method="POST">
      <h2 style="text-align:center;">Register</h2><br>
      <input type="text" name="full_name" placeholder="Full Name" required><br>
      <input type="text" name="last_name" placeholder="Last Name" required><br>
      <input type="email" name="email" placeholder="Email" required><br>
      <input type="tel" name="mobile" placeholder="Mobile Number" pattern="[0-9]{10}" required><br>
      <input type="number" name="age" placeholder="Age" required><br>

      <select name="gender" id="gender" required>
        <option value="" disabled selected>Select Gender</option>
        <option value="Male">Male</option>
        <option value="Female">Female</option>
        <option value="Other">Other</option>
      </select><br>

      <select name="course" id="course" required>
        <option value="" disabled selected>Select Course</option>
        <option value="CSE">CSE</option>
        <option value="AIDS">AIDS</option>
        <option value="ECE">ECE</option>
        <option value="IT">IT</option>
        <option value="EEE">EEE</option>
      </select><br>

      <button type="submit">Register</button>
    </form>
  </div>

</body>
</html>
