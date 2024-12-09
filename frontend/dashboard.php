<?php
require_once "AccSystemApiWithCookies.php";

$apiUrl = "http://localhost:5050";
$apiClient = new AccSystemApiWithCookies($apiUrl);

if (!$apiClient->isSignedIn()) {
    header("Location: login.php");
    exit();
}

$userEmail = json_decode($_COOKIE["acc_system_session"], true)["email"];
$userName = $apiClient->getUserData()["user"]["Name"];

$sortField = $_GET["sortField"] ?? "";
$sortOrder = $_GET["sortOrder"] ?? "";

$transactionalLists = $apiClient->getUserData()["user"]["TransactionalList"];

$events = [];
foreach ($transactionalLists as $tlUUID) {
    $transactionalList = $apiClient->getTransactionalList(
        $userEmail,
        $tlUUID,
        $apiClient->getUserData()["user"]["SessionToken"]
    )["transactionalList"];
    foreach ($transactionalList["Events"] as $eventUUID) {
        $event = $apiClient->getEvent(
            $userEmail,
            $tlUUID,
            $eventUUID,
            $apiClient->getUserData()["user"]["SessionToken"]
        )["event"];
        $event["UUID"] = $eventUUID;
        $event["TransactionalListName"] = $transactionalList["Name"];
        $events[] = $event;
    }
}

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
// isSetAndNotEmpty
// Filter events
$filteredEvents = [];
$totalAmount = 0;
foreach ($events as $event) {
    $includeEvent = true;

    if (
        isset($_GET["lowerAmount"]) &&
        $event["Amount"] < $_GET["lowerAmount"]
    ) {
        if (!empty($_GET["lowerAmount"])) {
            $includeEvent = false;
        }
    }
    if (
        isset($_GET["upperAmount"]) &&
        $event["Amount"] > $_GET["upperAmount"]
    ) {
        if (!empty($_GET["upperAmount"])) {
            $includeEvent = false;
        }
    }
    if (
        isset($_GET["fromDate"]) &&
        strtotime($event["Time"]) < strtotime($_GET["fromDate"])
    ) {
        if (!empty($_GET["fromDate"])) {
            $includeEvent = false;
        }
    }
    if (
        isset($_GET["toDate"]) &&
        strtotime($event["Time"]) > strtotime($_GET["toDate"])
    ) {
        if (!empty($_GET["toDate"])) {
            $includeEvent = false;
        }
    }
    if (
        isset($_GET["eventName"]) &&
        strpos($event["Name"], $_GET["eventName"]) === false
    ) {
        if (!empty($_GET["eventName"])) {
            $includeEvent = false;
        }
    }
    if (isset($_GET["transactionList"])) {
        if (
            !empty($_GET["transactionList"]) &&
            $event["TransactionalListName"] !=
                $apiClient->getTransactionalList(
                    $userEmail,
                    $_GET["transactionList"],
                    $apiClient->getUserData()["user"]["SessionToken"]
                )["transactionalList"]["Name"]
        ) {
            $includeEvent = false;
        }
    }

    if ($includeEvent) {
        $filteredEvents[] = $event;
    }

    foreach ($filteredEvents as $event) {
        $totalAmount += $event["Amount"];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <?php include "header.php"; ?>
    <h2>Dashboard</h2>
    <p><?php echo $userName . " (" . $userEmail . ")"; ?></p>
    <form method="GET">
        <label for="fromDate">From Date:</label>
        <input type="date" id="fromDate" name="fromDate" value="<?php echo isset(
            $_GET["fromDate"]
        )
            ? $_GET["fromDate"]
            : ""; ?>">
        <label for="toDate">To Date:</label>
        <input type="date" id="toDate" name="toDate" value="<?php echo isset(
            $_GET["toDate"]
        )
            ? $_GET["toDate"]
            : ""; ?>">
        <label for="lowerAmount">Lower Amount:</label>
        <input type="number" id="lowerAmount" name="lowerAmount" value="<?php echo isset(
            $_GET["lowerAmount"]
        )
            ? $_GET["lowerAmount"]
            : ""; ?>">
        <label for="upperAmount">Upper Amount:</label>
        <input type="number" id="upperAmount" name="upperAmount" value="<?php echo isset(
            $_GET["upperAmount"]
        )
            ? $_GET["upperAmount"]
            : ""; ?>">
        <label for="eventName">Event Name:</label>
        <input type="text" id="eventName" name="eventName" value="<?php echo isset(
            $_GET["eventName"]
        )
            ? $_GET["eventName"]
            : ""; ?>">
        <label for="transactionList">Transaction List:</label>
        <select id="transactionList" name="transactionList">
            <option value="">All</option>
            <?php foreach ($transactionalLists as $transactionalListUUID):
                $transactionalList = $apiClient->getTransactionalList(
                    $userEmail,
                    $transactionalListUUID,
                    $apiClient->getUserData()["user"]["SessionToken"]
                )["transactionalList"]; ?>
                <option value="<?php echo $transactionalListUUID; ?>" <?php echo isset(
    $_GET["transactionList"]
) && $_GET["transactionList"] == $transactionalListUUID
    ? "selected"
    : ""; ?>><?php echo $transactionalList["Name"]; ?></option>
            <?php
            endforeach; ?>
            </select>
        <button type="submit">Filter</button>
    </form>
    <h3>Events <a href="createEvent.php">(Create Event)</a> Total:
        <?php
        if ($totalAmount < 0) {
            echo "-$";
        } else {
            echo "$";
        }
        echo abs($totalAmount);
        ?></h3>
    <table id="eventsTable">
        <tr class="eventsRow">
            <th class="eventsCol">Name</th>
            <th class="eventsCol">Description</th>
            <th class="eventsCol">Amount</th>
            <th class="eventsCol">Time</th>
            <th class="eventsCol">For User</th>
            <th class="eventsCol">Transactional List</th>
            <th class="eventsCol">Edit</th>
        </tr>
        <?php foreach ($filteredEvents as $event):
            $userName = $apiClient->getUserNameByUUID($event["ForUser"])[
                "name"
            ]; ?>
            <tr class="eventsRow">
                <td class="eventsCol"><?php echo $event["Name"]; ?></td>
                <td class="eventsCol"><?php echo $event["Description"]; ?></td>
                <td class="eventsCol"><?php echo $event["Amount"]; ?></td>
                <td class="eventsCol"><?php echo $event["Time"]; ?></td>
                <td class="eventsCol"><?php echo $userName; ?></td>
                <td class="eventsCol"><?php echo $event[
                    "TransactionalListName"
                ]; ?></td>
                <td class="eventsCol">
                    <a href="editEvent.php?tlUUID=<?php echo $tlUUID; ?>&eventUUID=<?php echo $event[
    "UUID"
]; ?>">Edit</a>
                </td>
            </tr>
        <?php
        endforeach; ?>
    </table>
    <h3>Transactional Lists <a href="manageTransactionalLists.php">(Manage Transactional Lists)</a></h3>
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
                    $apiClient->getUserData()["user"]["SessionToken"]
                )["transactionalList"];
                $adminNames = [];
                $memberNames = [];
                $invitedMemberNames = [];
                foreach ($transactionalList["Admins"] as $adminUUID) {
                    $adminNames[] = $apiClient->getUserNameByUUID($adminUUID)[
                        "name"
                    ];
                }
                foreach ($transactionalList["Members"] as $memberUUID) {
                    $memberNames[] = $apiClient->getUserNameByUUID($memberUUID)[
                        "name"
                    ];
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
    <?php include "footer.php"; ?>
</body>
</html>
