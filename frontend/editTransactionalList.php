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
)["transactionalList"];
$adminNames = [];
$memberNames = [];
$invitedMemberNames = [];
$events = [];
foreach ($transactionalList["Admins"] as $adminUUID) {
    $adminNames[] = $apiClient->getUserNameByUUID($adminUUID)["name"];
}
foreach ($transactionalList["Members"] as $memberUUID) {
    $memberNames[] = $apiClient->getUserNameByUUID($memberUUID)["name"];
}
foreach ($transactionalList["InvitedMembers"] as $invitedMemberUUID) {
    $invitedMemberNames[] = $apiClient->getUserNameByUUID($invitedMemberUUID)[
        "name"
    ];
}
foreach ($transactionalList["Events"] as $eventUUID) {
    $events[] = $apiClient->getEvent(
        $userEmail,
        $tlUUID,
        $eventUUID,
        $userData["SessionToken"]
    )["event"];
    // add uuid to each event
    $events[count($events) - 1]["uuid"] = $eventUUID;
}
?>

<!DOCTYPE html>
<html>
<head>
<!--tab name is lists, linked to style sheet-->
    <title>Transactional List</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<!--link header, print header with name of page, print details of the transaction list (name, description, members, etc)-->
    <?php include "header.php"; ?>
    <h2>Transactional List</h2>
    <h3>List Name: <?php echo $transactionalList["Name"]; ?></h3>
    <p>Description: <?php echo $transactionalList["Description"]; ?></p>
    <p>Admins: <?php echo implode(", ", $adminNames); ?>, Members: <?php echo implode(", ", $memberNames); ?></p>
    <p>Invited Members: <?php echo implode(", ", $invitedMemberNames); ?></p>
    <!-- TODO invite someone by username -->
    <!-- <form method="POST">
        <label for="inviteEmail">Invite Email:</label>
        <input type="email" id="inviteEmail" name="inviteEmail" required>
        <button type="submit">Invite</button>
    </form> -->

    <!--  creates table of "events" attributes upon creation of event-->
    <h4>Events <a href="createEvent.php?tlUUID=<?php echo $tlUUID; ?>">(Add)</a></h4>
    <table id="eventsTable">
        <tr class="eventsRow">
            <th class="eventsCol">Name</th>
            <th class="eventsCol">Description</th>
            <th class="eventsCol">Time</th>
            <th class="eventsCol">Amount</th>
            <th class="eventsCol">For</th>
            <th class="eventsCol">Paid</th>
            <th class="eventsCol">Actions</th>
        </tr>
<!--        loop through each of the events and each of its elements-->
        <?php foreach ($events as $event): ?>
        <tr class="eventsRow">
            <td class= "eventsCol"><?php echo $event["Name"]; ?></td>
                <td class= "eventsCol"><?php echo $event["Description"]; ?></td>
                <td class= "eventsCol"><?php echo $event["Time"]; ?></td>
                <td class= "eventsCol"><?php echo $event["Amount"]; ?></td>
                <td class= "eventsCol"><?php echo $apiClient->getUserNameByUUID(
                    $event["ForUser"]
                )["name"]; ?></td>
                <td class= "eventsCol"><?php echo $event["Paid"] ? "Yes" : "No"; ?></td>
                <td class= "eventsCol">
                    <!-- pay with markEventAsPaid.php -->
                    <?php if (!$event["Paid"]) {
                        echo "<a href=\"markEventAsPaid.php?tlUUID=" .
                            $tlUUID .
                            "&eventUUID=" .
                            $event["uuid"] .
                            "\">Pay</a>";
                    } ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <p>Total Amount: $<?php echo array_sum(
        array_column($transactionalList["Events"], "amount")
    ); ?></p>

<!--    link footer to the page-->
    <?php include "footer.php"; ?>
</body>
</html>
