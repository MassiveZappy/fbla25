<?php
setcookie("acc_system_session", "", time() - 3600, "/");
header("Location: index.php");
?>