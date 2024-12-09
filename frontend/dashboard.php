<?php
require_once "AccSystemApiWithCookies.php";

$apiUrl = "http://localhost:5050";
$apiClient = new AccSystemApiWithCookies($apiUrl);

if (!$apiClient->isSignedIn()) {
    header("Location: login.php");
    exit();
}

$userEmail = json_decode($_COOKIE["acc_system_session"], true)["email"];
// print_r($apiClient->getUserData());
$userName = $apiClient->getUserData()["user"]["Name"];

$sortField = $_GET["sortField"] ?? "";
$sortOrder = $_GET["sortOrder"] ?? "";

$transactionalLists = []; // Fetch transactional lists using the API
$events = []; // Fetch events using the API

// Sort events if needed
if ($sortField) {
    usort($events, function ($a, $b) use ($sortField, $sortOrder) {
        if ($sortOrder == "asc") {
            return $a[$sortField] <=> $b[$sortField];
        } else {
            return $b[$sortField] <=> $a[$sortField];
        }
    });
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
    <?php include "header.php"; ?>
    <h2>Dashboard</h2>
    <p><?php echo $userName . " (" . $userEmail . ")"; ?></p>
    <form method="GET">
        <label for="sortField">Sort by:</label>
        <select id="sortField" name="sortField">
            <option value="">None</option>
            <option value="name" <?php if ($sortField == "name") {
                echo "selected";
            } ?>>Event Name</option>
            <option value="time" <?php if ($sortField == "time") {
                echo "selected";
            } ?>>Time</option>
            <option value="amount" <?php if ($sortField == "amount") {
                echo "selected";
            } ?>>Amount</option>
        </select>
        <select id="sortOrder" name="sortOrder">
            <option value="asc" <?php if ($sortOrder == "asc") {
                echo "selected";
            } ?>>Ascending</option>
            <option value="desc" <?php if ($sortOrder == "desc") {
                echo "selected";
            } ?>>Descending</option>
        </select>
        <button type="submit">Sort</button>
    </form>
    <h3>Transactional Lists</h3>
    <?php foreach ($transactionalLists as $list): ?>
        <div class="transactional-list">
            <h4><?php echo $list["name"]; ?></h4>
            <ul>
                <?php foreach ($list["events"] as $event): ?>
                    <li><?php echo $event["name"] .
                        " - " .
                        $event["time"] .
                        ' - $' .
                        $event["amount"]; ?></li>
                <?php endforeach; ?>
            </ul>
            <p>Total Amount: $<?php echo array_sum(
                array_column($list["events"], "amount")
            ); ?></p>
        </div>
    <?php endforeach; ?>
    <?php include "footer.php"; ?>
</body>
</html>
