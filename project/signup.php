<?php
include("database.php");
session_start();

$message = "";
$color = "";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $Username = filter_input(INPUT_POST, "username", FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
        $password = $_POST["password"];
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Check if email already exists
        $checkQuery = "SELECT id FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $checkQuery);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $message = "Email already exists. Please use a different email.";
            $color = "red";
            mysqli_stmt_close($stmt); // ✅ Close only once here
        } else {
            mysqli_stmt_close($stmt); // ✅ Close previous SELECT stmt before new INSERT

            // Insert new user
            $insertQuery = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
            $insertStmt = mysqli_prepare($conn, $insertQuery);
            mysqli_stmt_bind_param($insertStmt, "sss", $Username, $email, $hash);
            mysqli_stmt_execute($insertStmt);

            $_SESSION["user_id"] = mysqli_insert_id($conn);
            $_SESSION["user_name"] = $Username;

            $message = "Registered successfully!";
            $color = "green";

            mysqli_stmt_close($insertStmt); // ✅ Close INSERT stmt
            header("location:register.php");
        }

        mysqli_close($conn); // ✅ Close DB connection
    }
} catch (mysqli_sql_exception $e) {
    $message = "Database error: " . $e->getMessage();
    $color = "red";
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Signup Page</title>
    <link rel="stylesheet" href="style.css?v=2">
  
</head>
<body>


<form method="POST">
    <?php if (!empty($message)) : ?>
        <p style="color: <?= $color ?>; text-align: center; margin: 0;">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>
    <div class ="main1"></div>
     <h2>SIGNUP</h2>
    <input type="text" name="username" placeholder="Username" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Signup</button>
</form>
</body>
</html>
