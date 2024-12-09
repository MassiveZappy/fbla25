<?php
require_once __DIR__ . "/AccSystemApiWithCookies.php";

$apiUrl = "http://localhost:5050";
$apiClient = new AccSystemApiWithCookies($apiUrl);

try {
    if (!$apiClient->isSignedIn()) {
        header("Location: login.php");
        exit();
    }

    $userData = $apiClient->getUserData();
    $userName = $userData["user"]["Name"];
    $userEmail = $userData["user"]["Email"];

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = $_POST["name"];
        $description = $_POST["description"];
        $apiClient->createTransactionalList($userEmail, $name, $description);
    }

    $transactionalLists = $userData["user"]["TransactionalList"];
} catch (Exception $e) {
    $error = $e->getMessage();
    echo "<pre>";
    print_r($error);
    echo "</pre>";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Transactional Lists</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
    <?php include "header.php"; ?>
    <h2>Manage Transactional Lists</h2>
    <p><?php echo $userName . " (" . $userEmail . ")"; ?></p>

    <h3>Add New Transactional List</h3>
    <form method="POST">
        <label for="name">List Name:</label>
        <input type="text" id="name" name="name" required>
        <label for="description">Description:</label>
        <input type="text" id="description" name="description" required>
        <button type="submit">Add List</button>
    </form>

    <h3>Your Transactional Lists</h3>
    <?php if (empty($transactionalLists)): ?>
        <p>You have no transactional lists.</p>
    <?php // Replace with actual list name if available
        // Replace with actual list name if available
        // Replace with actual list name if available
        // Replace with actual list name if available
        // Replace with actual list name if available
        // Replace with actual list name if available
        else: ?>
        <ul>
            <?php foreach ($transactionalLists as $tlUUID): ?>
                <li>
                    <a href="transactionalList.php?tlUUID=<?php echo $tlUUID; ?>">
                        <?php echo $tlUUID;
                // Replace with actual list name if available
                ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php include "footer.php"; ?>
</body>
</html>
