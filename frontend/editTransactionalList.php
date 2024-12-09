<?php
require_once "AccSystemApiWithCookies.php";

$apiUrl = "http://localhost:5050";
$apiClient = new AccSystemApiWithCookies($apiUrl);

if (!$apiClient->isSignedIn()) {
    header("Location: login.php");
    exit();
}
$userData = $apiClient->getUserData()["user"];
$userName = $userData["Name"];
$userEmail = $userData["Email"];

$tlUUID = $_GET["tlUUID"] ?? "";
$transactionalList = $apiClient->getTransactionalList(
    $userEmail,
    $tlUUID,
    $userData["SessionToken"]
);
print_r($transactionalList);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Transactional List</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <?php include "header.php"; ?>
    <h2>Transactional List</h2>
    <h3><?php echo $transactionalList["name"]; ?></h3>
    <ul>
        <?php foreach ($transactionalList["events"] as $event): ?>
            <li><?php echo $event["name"] .
                " - " .
                $event["time"] .
                ' - $' .
                $event["amount"]; ?></li>
        <?php endforeach; ?>
    </ul>
    <p>Total Amount: $<?php echo array_sum(
        array_column($transactionalList["events"], "amount")
    ); ?></p>
    <?php include "footer.php"; ?>
</body>
</html>
