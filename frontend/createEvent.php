<?php
require_once "AccSystemApiWithCookies.php";

$apiUrl = "http://localhost:5050";
$apiClient = new AccSystemApiWithCookies($apiUrl);
$userData = $apiClient->getUserData()["user"];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userEmail = $userData["Email"];
    $tlUUID = $_POST["tlUUID"];
    $name = $_POST["name"];
    $description = $_POST["description"];
    $amount = $_POST["amount"];
    $forUserEmail = $_POST["forUserEmail"];
    $incomeOrSpending = $_POST["incomeOrSpending"];

    if ($incomeOrSpending === "income") {
        $amount = abs($amount);
    } else {
        $amount = -abs($amount);
    }

    $response = $apiClient->addEvent(
        $userEmail,
        $tlUUID,
        $name,
        $description,
        true,
        $amount,
        $userEmail,
        $forUserEmail
    )[0];
    if ($response["status"] === "success") {
        header("Location: editTransactionalList.php?tlUUID=$tlUUID");
        exit();
    } else {
        $error = $response["error"];
    }
} else {
    $transactionalLists = [];
    foreach ($userData["TransactionalList"] as $tlUUID) {
        $transactionalLists[] = $apiClient->getTransactionalList(
            $userData["Email"],
            $tlUUID,
            $userData["SessionToken"]
        )["transactionalList"];
        // add uuid to each transactional list
        $transactionalLists[count($transactionalLists) - 1]["UUID"] = $tlUUID;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Event</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <?php include "header.php"; ?>
    <h2>Create Event</h2>
    <form method="POST">
        <label for="name">Event Name:</label>
            <input type="text" id="name" name="name" required>
        <label for="description">Description:</label>
            <input type="text" id="description" name="description" required>
        <label for="amount">Amount:</label>
            <input type="number" id="amount" name="amount" required>
        <div class="radio-group">
            <label for="income">Income</label>
            <input type="radio" id="income" name="incomeOrSpending" value="income" required>

            <label for="spending">Spending</label>
            <input type="radio" id="spending" name="incomeOrSpending" value="spending" required checked>
        </div>
        <label for="forUserEmail">For User (Email):</label>
        <input type="email" id="forUserEmail" name="forUserEmail" required value=<?php echo $userData[
            "Email"
        ]; ?> >
        <!-- Dropdown for tl -->
        <label for="tlUUID">Transactional List:</label>
        <select id="tlUUID" name="tlUUID">
            <?php foreach ($transactionalLists as $tl): ?>
                <option value="<?php echo $tl["UUID"]; ?>">
                    <?php echo $tl["Name"]; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Create Event</button>
    </form>
    <?php include "footer.php"; ?>
</body>
</html>
