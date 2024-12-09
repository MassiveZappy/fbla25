<?php
// given tlUUID, eventUUID
// mark event as paid

require_once "AccSystemApiWithCookies.php";
$apiUrl = "localhost:5050";
$apiClient = new AccSystemApiWithCookies($apiUrl);

$userData = $apiClient->getUserData()["user"];
$userEmail = $userData["Email"];
$tlUUID = $_GET["tlUUID"];
$eventUUID = $_GET["eventUUID"];

$apiClient->markEventAsPaid($userEmail, $tlUUID, $eventUUID);

header("Location: editTransactionalList.php?tlUUID=$tlUUID");
