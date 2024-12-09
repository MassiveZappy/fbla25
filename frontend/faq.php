<html>
<head>
    <!--Adds the title "FAQ" to the tab and links stylistic changes from the css file-->
    <title>FAQ</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
    <body id="FAQtext">
        <?php include "header.php"; ?>
        <h2>FAQ</h2>
            <h3>How do I create an account and login? </h3>
                <p>
                    To create an account, go into the signup page and input your username, email, and password. Then go to the login page and sign in through that account.
                </p>
            <h3>How do I add transactions (called events)?</h3>
                <p>
                    Add transactions by going to Dashboard, clicking "create event," and then inputting the event's details.
                </p>
            <h3>Can I group expenses and incomes by category?</h3>
                <p>
                    Yes of course! Navigate to "dashboard" and input details such as name, date, or amount to tailor to your specific request!
                </p>
            <h3>Where can I find my transactional list balance?</h3>
                <p>
                    You can find your account balance under the dashboard section and pressing "edit" for anyone of your created transactional lists!
                </p>
            <h3>How do I use the search function to filter my transactions?</h3>
                <p> Users can filter by 3 criteria: date, amount, and category.
                    <ul>
                        <li>Type in start and end dates to display transactions in that range.</li>
                        <li>Filtering by amount can display transactions by size. Doing this using positive and negative values will filter by spending (negative) and income (positive).</li>
                        <li> Adding a category filter will find all transactions under that name. </li>
                    </ul>
                </p>

        <h3>Credits</h3>
            <p>App formation was assisted by Natalie Steager.</p>

        <?php include "footer.php"; ?>
    </body>
</html>