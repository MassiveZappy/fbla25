<?php
require_once "AccSystemApiWithCookies.php";

$apiUrl = "http://localhost:5050";
$apiClient = new AccSystemApiWithCookies($apiUrl);

//get email and password from user: if correct login and send to dashboard, else send user fail message
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    try {
        $apiClient->signIn($email, $password);

        echo "Successfully signed in.";
        header("Location: dashboard.php");
        exit();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<!-- text in tab when on this page, link to style sheet-->
    <title>Login</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<!--link header, print name of page "Login"-->
    <?php include "header.php"; ?>
    <h2>Login</h2>
    <?php if (isset($error)) {
        echo "<p class='error'>$error</p>";
    } ?>
    <form method="POST">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <button type="submit">Login</button>
    </form>
<!--link footer-->
    <?php include "footer.php"; ?>
</body>
</html>
