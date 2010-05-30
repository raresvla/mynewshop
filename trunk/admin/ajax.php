<?php
require 'config/check_login.php';

switch ($_GET['sectiune']) {
    case 'produse':
        require 'config/resize.php';
    case 'categorii':
    case 'clienti':
    case 'comenzi':
    case 'setari': {
        require "ajax/{$_GET['sectiune']}.php";
        $dispatcher = "AjaxAction" . ucfirst($_GET['sectiune']);
        new $dispatcher($_GET, $user);
    }
}

exit();
?>