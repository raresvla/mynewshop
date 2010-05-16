<?php
require 'config/check_login.php';

switch ($_GET['sectiune']) {
    case 'categorii':
        {
            require 'config/ajaxAction-categorii.class.php';
            $ajaxAction = new AjaxActionCategorii($_GET, $user);
        }
        break;
    case 'produse':
        {
            require 'config/ajaxAction-produse.class.php';
            require 'config/resize.php';
            $ajaxAction = new AjaxActionProduse($_GET, $user);
        }
        break;
    case 'clienti':
        {
            require 'config/ajaxAction-clienti.class.php';
            $ajaxAction = new AjaxActionClienti($_GET, $user);
        }
        break;
    case 'comenzi':
        {
            require 'config/ajaxAction-comenzi.class.php';
            $ajaxAction = new AjaxActionComenzi($_GET, $user);
        }
        break;
    case 'setari':
        {
            require 'config/ajaxAction-setari.class.php';
            $ajaxAction = new AjaxActionComenzi($_GET, $user);
        }
        break;        
}

exit();
?>