<?php
require 'check_login.php';

$user->logOff();
if (! $_GET['redirect']) {
    header("Location: $CALE_VIRTUALA_SERVER");
} else {
    $redirect = "Location: {$CALE_VIRTUALA_SERVER}{$_GET['redirect']}/";
    header($redirect);
}
exit();
?>