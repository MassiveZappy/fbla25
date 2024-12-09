<?php
# Logs out by resetting cookies or after resets cookies after two hours
setcookie("acc_system_session", "", time() - 3600, "/");
header("Location: index.php");
?>