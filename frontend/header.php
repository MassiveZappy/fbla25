<!DOCTYPE html>
<html>

<head>
    <?php
    # determine if the user is signed in
    require_once "AccSystemApiWithCookies.php";

    $apiUrl = "http://localhost:5050";
    $apiClient = new AccSystemApiWithCookies($apiUrl);
    $isSignedIn = false;
    try {
        $isSignedIn = $apiClient->isSignedIn();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
    ?>
<!--    lang tag-->
    <title>Transactional List</title>

    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="icon" href="/images/favicon.ico" type="image/x-icon">
</head>
<body>
    <header>
        <nav>
            <ul>
                <li style="font-size:20px;color:white;font-weight: bold;">
                    <img src="images/favicon.ico" alt="logo" style="width: 1em;height: 1em;">
                        PHS Financial
                </li>
                <li><a href="index.php">Home</a></li>
                <?php if ($isSignedIn) {
                    echo "<li><a href=\"dashboard.php\">Dashboard</a></li>";
                    echo "<li>Username: " .
                        $apiClient->getUserData()["user"]["Name"] .
                        "</li>";
                    echo "<li><a href=\"logout.php\">Logout</a></li>";
                } else {
                    echo "<li><a href=\"signup.php\">Sign Up</a></li>";
                    echo "<li><a href=\"login.php\">Login</a></li>";
                } ?>
<!--                <li><a href="signup.php">Sign Up</a></li>-->
<!--                <li><a href="login.php">Login</a></li>-->
<!--                <li><a href="dashboard.php">Dashboard</a></li>-->
            </ul>
        </nav>
    </header>
</body>
</html>
