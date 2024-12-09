<?php
require_once __DIR__ . "/AccSystemApiWithCookies.php";

$apiUrl = "http://localhost:5050";
$apiClient = new AccSystemApiWithCookies($apiUrl);

if (!$apiClient->isSignedIn()) {
    header("Location: login.php");
    exit();
}
$userData = $apiClient->getUserData();
$userName = $userData["user"]["Name"];
$userEmail = $userData["user"]["Email"];
$userToken = json_decode($_COOKIE["acc_system_session"], true)["sessionToken"];
try {
    if (!$apiClient->isSignedIn()) {
        header("Location: login.php");
        exit();
    }

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
<!--    text displayed on tab, link to style sheet-->
    <title>Manage Transactional Lists</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<!--link to header-->
    <?php include "header.php"; ?>
<!--print name of page to screen of website-->
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
    <?php else: ?>
<!--            $apiClient->getTransactionalList(
                                $userEmail,
                                $tlUUID,
                                $userToken
                            )-->
            <!--            each event gets a row of a table. cols are: Name, Description, Admins, Members, Invited Members -->
        <table id="tlTable">
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Admins</th>
                <th>Members</th>
                <th>Invited Members</th>
                <th>Edit</th>
            </tr>
            <?php foreach ($transactionalLists as $transactionalListUUID): ?>
                <tr class="tlTableRow">
                    <?php
                    $transactionalList = $apiClient->getTransactionalList(
                        $userEmail,
                        $transactionalListUUID,
                        json_decode($_COOKIE["acc_system_session"], true)["sessionToken"]
                    )["transactionalList"];
                    $adminNames = [];
                    $memberNames = [];
                    $invitedMemberNames = [];
                    foreach ($transactionalList["Admins"] as $adminUUID) {
                        $adminNames[] = $apiClient->getUserNameByUUID(
                            $adminUUID
                        )["name"];
                    }
                    foreach ($transactionalList["Members"] as $memberUUID) {
                        $memberNames[] = $apiClient->getUserNameByUUID(
                            $memberUUID
                        )["name"];
                    }
                    foreach (
                        $transactionalList["InvitedMembers"]
                        as $invitedMemberUUID
                    ) {
                        $invitedMemberNames[] = $apiClient->getUserNameByUUID(
                            $invitedMemberUUID
                        )["name"];
                    }
                    ?>
                    <td class="tlTableCol"><?php echo $transactionalList[
                        "Name"
                    ]; ?></td>
                    <td class="tlTableCol"><?php echo $transactionalList[
                        "Description"
                    ]; ?></td>
                    <td class="tlTableCol"><?php echo implode(
                        ", ",
                        $adminNames
                    ); ?></td>
                    <td class="tlTableCol"><?php echo implode(
                        ", ",
                        $memberNames
                    ); ?></td>
                    <td class="tlTableCol"><?php echo implode(
                        ", ",
                        $invitedMemberNames
                    ); ?></td>
                    <td class="tlTableCol">
                        <?php if (in_array($userName, $adminNames)) {
                            echo '<a href="editTransactionalList.php?tlUUID=' .
                                $transactionalListUUID .
                                '">Edit</a>';
                        } else {
                            echo "N/A";
                        } ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <?php include "footer.php"; ?>
</body>
</html>
