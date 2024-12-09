<!DOCTYPE html>
<!--add lang tag-->
<html lang = "en">
<head>
    <?php
    # determine if  the user is signed in
    require_once "AccSystemApiWithCookies.php";

    $apiUrl = "http://localhost:5050";
    $apiClient = new AccSystemApiWithCookies($apiUrl);
    $isSignedIn = false;
    // checks if user is signed in, get message if no
    try {
        $isSignedIn = $apiClient->isSignedIn();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
    ?>
<!--    text displayed in the tab-->
    <title>Transactional List</title>
<!--link to style sheet, add favicon-->
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="icon" href="/images/favicon.ico" type="image/x-icon">
</head>
<body>
<!--creates a list under the nav tag as a header to create a nav bar for the website-->
    <header>
        <nav>
            <!--                list of pages in nav bar-->
            <ul>
<!--                text size, color, weight of website name/logo with favicon on left side of nav bar-->
                <li style="font-size:20px;color:white;font-weight: bold;">
                    <img src="images/favicon.ico" alt="logo" style="width: 1em;height: 1em;">
                        PHS Financial
                </li>
<!--                add other pages (Home page, dashboard, logout, signup, login...) depending on signedIn status-->
                <li><a href="index.php">Home</a></li>
<!--                checks if user is signed in to determine which pages to make available and display in nav bar-->
                <?php if ($isSignedIn) {
                    echo "<li><a href=\"dashboard.php\">Dashboard</a></li>";
                    echo "<li>Username: " .
                        $apiClient->getUserData()["user"]["Name"] .
                        "</li>";
                    echo "<li><a href=\"faq.php\">FAQ</a></li>";
                    echo "<li><a href=\"logout.php\">Logout</a></li>";
                } else {
                    echo "<li><a href=\"signup.php\">Sign Up</a></li>";
                    echo "<li><a href=\"login.php\">Login</a></li>";
                    echo "<li><a href=\"faq.php\">FAQ</a></li>";
                } ?>
            </ul>
        </nav>
    </header>
</body>
</html>
