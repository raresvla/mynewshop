<?php

class AjaxActionCategorii
{
    private  $_user;
    function __construct ($details, User $user)
    {
        $method = $details['sectiune'] . "_" . $details['actiune'];
        if (method_exists($this, $method)) {
            $this->_user = $user;
            $this->$method($details);
        } else {
            throw new Exception('Metoda asociata nu este definita (' + $method + ')!');
        }
    }
    
    public function categorii_getCategories() {
        $sql = "SELECT `id`, `parent_id`, `denumire` FROM `categorii` WHERE 1";
        $categorii = mysql_query($sql, db_c());
        
        echo '<script type="text/javascript">' . "\n";
        echo "\td = new dTree('d'); d.config.target = 'viewCurrentCateg';\n";
        echo "\td.add(0, -1, 'MyShop - by Rareş Vlăsceanu', 'viewCategorie.php?base=1');\n";
        
        $i = 0;
        while ($row = mysql_fetch_assoc($categorii)) {
            echo "\td.add({$row['id']}, " . intval($row['parent_id']) . ", '" . addslashes($row['denumire']) . "','viewCategorie.php?categorieId={$row['id']}');\n";
            $i ++;
        }
        
        echo "\t$('tree').innerHTML = d.toString();\n";
        echo "</script>";
    }
    
    public function categorii_salveaza() {
        if (isset($_SESSION['uploadIconTmp'])) {
            if ($_SESSION['uploadIconTmp']['oldIcon'])
                unlink("../imagini/categorii/{$_SESSION['uploadIconTmp']['oldIcon']}");
            @rename("../imagini/categorii/{$_SESSION['uploadIconTmp']['tmpIcon']}", "../imagini/categorii/{$_SESSION['uploadIconTmp']['newIcon']}");
        }
        
        if ($_GET['context'] == 'edit') {
            $sql = "UPDATE `categorii` SET `denumire` = '" . mysql_escape_string($_POST['denumire']) . "'" . (isset($_SESSION['uploadIconTmp']) ? ", `icon` = '{$_SESSION['uploadIconTmp']['newIcon']}'" : "") . " WHERE `id` = '{$_GET['id']}' LIMIT 1";
            echo (mysql_query($sql, db_c()) ? 1 : mysql_error());
        } else {
            $sql = "INSERT INTO `categorii` (`parent_id`, `denumire`, `icon`) VALUES (" . ($_GET['id'] ? "'{$_GET['id']}'" : 'NULL') . ", '" . mysql_escape_string($_POST['denumire']) . "', '{$_SESSION['uploadIconTmp']['newIcon']}')";
            echo (mysql_query($sql, db_c()) ? 1 : mysql_error());
        }
        
        unset($_SESSION['uploadIconTmp']);
    }
    
    public function categorii_upload() {
    if ($icon = $_FILES['_icon']) {
        $error = false;
        if (isset($_SESSION['uploadIconTmp'])) {
            /* alt upload deja inregistrat, il abandonam */
            $tmpFile = "../imagini/categorii/{$_SESSION['uploadIconTmp']['tmpIcon']}";
            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }
            unset($_SESSION['uploadIconTmp']);
        }
        
        if (! in_array($icon['type'], array('image/png' , 'image/gif'))) {
                $error = true;
                $message = 'Datorita constrangerilor de design, nu pot fi incarcate decat|imagini .png sau .gif, cu background transparent!';
            } else {
                $details = getimagesize($icon['tmp_name']);
                if ($details[0] != 100 || $details[1] != 100) {
                    $error = true;
                    $message = 'Imaginea trebuie sa aiba dimensiunile de 100x100 pixeli,|redimensionarea ei nefiind posibila (s-ar pierde transparenta).';
                } else {
                    /* Temporarly Change Icon */
                    if ($_POST['_context'] == 'edit') {
                        $sql = "SELECT `icon` FROM `categorii` WHERE `id` = '{$_GET['id']}'";
                        $oldIcon = mysql_result(mysql_query($sql, db_c()), 0);
                    } else {
                        $oldIcon = null;
                    }
                    
                    $file = "../imagini/categorii/tmp-{$icon['name']}";
                    $_SESSION['uploadIconTmp'] = array('oldIcon' => $oldIcon , 'tmpIcon' => "tmp-{$icon['name']}" , 'newIcon' => $icon['name']);
                    
                    move_uploaded_file($icon['tmp_name'], $file);
                    chmod($file, 0777);
                    
                    echo "<body><html><head><script>parent.refreshIcon('{$_SESSION['uploadIconTmp']['tmpIcon']}');</script></head></html></body>";
                }
            }
        }
        if ($error) {
            echo "<body><html><head><script>alert('" . str_replace("|", '\n', addslashes($message)) . "')</script></head></html></body>";
        }
    }
    
    public function categorii_clearUpload() {
        if (!empty($_SESSION['uploadIconTmp'])) {
            $tmpFile = "../public/imagini/categorii/{$_SESSION['uploadIconTmp']['tmpIcon']}";
            if (file_exists($tmpFile)) {
                unlink($tmpFile);
                echo $_SESSION['uploadIconTmp']['oldIcon'];
            }
            unset($_SESSION['uploadIconTmp']);
        } else {
            echo "false";
        }
    }
    
    public function categorii_sterge() {
        $sql = "SELECT `icon`, `parent_id` FROM `categorii` WHERE `id` = '{$_GET['id']}'";
        $data = mysql_fetch_assoc(mysql_query($sql, db_c()));
        
        $sql = "DELETE FROM `categorii` WHERE `id` " . ($_GET['id'] ? "= '{$_GET['id']}'" : "IS NULL");
        if (! mysql_query($sql, db_c())) {
            echo 'false';
        } else {
            if ($data['icon'] && file_exists("../public/imagini/categorii/{$data['icon']}")) {
                @unlink("../public/imagini/categorii/{$data['icon']}");
            }
            echo ($data['parent_id'] ? $data['parent_id'] : - 1);
        }
    }
    
    public function categorii_getSectiuniCaracteristici($selectedRow = null, $activeRow = null) {
        $sql = "SELECT * FROM `sectiuni_caracteristici` WHERE `categorie_id` = '{$_GET['categorieId']}' ORDER BY `ordine` ASC";
        $result = mysql_query($sql, db_c());
        
        if(!mysql_num_rows($result)) {
            echo '<div class="loader"><img src="images/icons/info.png" width="25" height="25" class="valignMiddle" /> Nicio sectiune definita.</div>';
        }
        else {
            $buffer = null;
            $i = 0;
            while($row = mysql_fetch_assoc($result)) {
                $buffer .= "<tr id=\"row_sectiuni_{$row['id']}\" onmouseover=\"showHide('sectiuni', {$row['id']}, 'on')\" onmouseout=\"showHide('sectiuni', {$row['id']}, 'off')\" style=\"height:20px" . ($selectedRow == $row['id'] ? ';background-color:#E7EDF9' : '') ."\"" . ($activeRow == $row['id'] ? ' class="selectedRow"' : '') . ">\n";
                $buffer .= '  <td align="center"><span id="span_sectiuni_' . $row['id'] . '" style="display:' . ($selectedRow != $row['id'] ? 'block' : 'none') .'">' . ($i+1) . '.</span><input type="checkbox" name="sectiuni[]" id="check_sectiuni_' . $row['id'] . '" value="' . $row['id'] . '" style="display:' . ($selectedRow == $row['id'] ? 'block' : 'none') .'" onclick="selectDeselect(this, \'sectiuni\', ' . $row['id'] . ');"' . ($selectedRow == $row['id'] ? ' checked="checked"' : '') . ' /></td>' . "\n";
                $buffer .= '  <td align="left">' . $row['sectiune'] . '</td>' . "\n";
                $buffer .= "</tr>\n";
                
                $i++;
            }
            
            $cols = "<colgroup>\n  <col width=\"50\" />\n  <col />\n</colgroup>";
            
            echo '<table width="100%" border="0" cellpadding="0" cellspacing="0">' . $cols . $buffer . '</table>';
        }
    }
    
    public function categorii_getCaracteristici() {
        $sql = "SELECT * FROM `caracteristici` WHERE `categorie_id` = '{$_GET['categorieId']}' AND `sectiune_id` = '{$_GET['sectiuneId']}'";
        $result = mysql_query($sql, db_c());
        
        if(!mysql_num_rows($result)) {
            echo '<div class="loader"><img src="images/icons/info.png" width="25" height="25" class="valignMiddle" /> Nicio caracteristica definita.</div>';
        }
        else {
            $buffer = null;
            $i = 0;
            while($row = mysql_fetch_assoc($result)) {
                $buffer .= "<tr id=\"row_caracteristici_{$row['id']}\" onmouseover=\"showHide('caracteristici', {$row['id']}, 'on')\" onmouseout=\"showHide('caracteristici', {$row['id']}, 'off')\" style=\"height:20px\">\n";
                $buffer .= '  <td align="center"><span id="span_caracteristici_' . $row['id'] . '">' . ($i+1) . '.</span><input type="checkbox" name="caracteristici[]" id="check_caracteristici_' . $row['id'] . '" value="' . $row['id'] . '" onclick="selectDeselect(this, \'caracteristici\', ' . $row['id'] . ');" style="display:none" /></td>' . "\n";
                $buffer .= '  <td align="left">' . $row['caracteristica'] . '</td>' . "\n";
                $buffer .= '  <td align="center">' . ($row['preview'] ? '<img src="images/icons/ok.png" width="16" height="16" alt="" />' : '&nbsp;') . '</td>' . "\n";
                $buffer .= "</tr>\n";
                
                $i++;
            }
            $cols = "<colgroup>\n  <col width=\"50\" />\n  <col />\n  <col width=\"45\" /></colgroup>";
            
            echo '<table width="100%" border="0" cellpadding="0" cellspacing="0">' . $cols . $buffer . '</table>';
        }
    }
    
    public function categorii_mutaSectiune() {
        $sql = "SELECT `ordine` FROM `sectiuni_caracteristici` WHERE `id` = '{$_GET['sectiuneId']}' LIMIT 1";
        $ordine = mysql_result(mysql_query($sql, db_c()), 0, 'ordine');
        
        if($_GET['direction'] == "up") {
            $sql = "SELECT `id` FROM `sectiuni_caracteristici` WHERE `ordine` < '$ordine' AND `categorie_id` = '{$_GET['categorieId']}' ORDER BY `ordine` DESC LIMIT 1";
            $id1 = @mysql_result(mysql_query($sql, db_c()), 0, 'id');
            if($id1) {
                $sql = "UPDATE `sectiuni_caracteristici` SET `ordine` = '$ordine' WHERE `id` = '$id1'";
                mysql_query($sql, db_c());
                $sql = "UPDATE `sectiuni_caracteristici` SET `ordine` = '".($ordine - 1)."' WHERE `id` = '{$_GET['sectiuneId']}'";
                mysql_query($sql, db_c());
            }
        }
        else {
            $sql = "SELECT `id` FROM `sectiuni_caracteristici` WHERE `ordine` > '$ordine' AND `categorie_id` = '{$_GET['categorieId']}' ORDER BY `ordine` ASC LIMIT 1";
            $id1 = @mysql_result(mysql_query($sql, db_c()), 0, 'id');
            if($id1) {
                $sql = "UPDATE `sectiuni_caracteristici` SET `ordine` = '$ordine' WHERE `id` = '$id1'";
                mysql_query($sql, db_c());
                $sql = "UPDATE `sectiuni_caracteristici` SET `ordine` = '".($ordine + 1)."' WHERE `id` = '{$_GET['sectiuneId']}'";
                mysql_query($sql, db_c());
            }
        }
        
        $this->categorii_getSectiuniCaracteristici($_GET['sectiuneId'], $_GET['activeRow']);
    }
    
    public function categorii_stergeSectiune() {
        $sectiuni = json_decode(stripslashes($_GET['sectiuni']));
        $sql = "DELETE FROM `sectiuni_caracteristici` WHERE `id` IN (" . implode(", ", $sectiuni) . ")";
        mysql_query($sql, db_c());
        
        $this->categorii_getSectiuniCaracteristici(null, $_GET['activeRow']);
    }
    
    public function categorii_stergeCaracteristica() {
        $caracteristici = json_decode(stripslashes($_GET['caracteristici']));
        $sql = "DELETE FROM `caracteristici` WHERE `id` IN (" . implode(", ", $caracteristici) . ")";
        mysql_query($sql, db_c());
        
        $this->categorii_getCaracteristici();
    }
    
    public function categorii_adaugaSectiune() {
        $date = $_GET['data'];
        $sql = "SELECT MAX(`ordine`) FROM `sectiuni_caracteristici` WHERE `categorie_id` = '{$_GET['categorieId']}'";
        $ordine = mysql_result(mysql_query($sql, db_c()), 0) + 1;
        $sql = "INSERT INTO `sectiuni_caracteristici` (`categorie_id`, `sectiune`, `ordine`) VALUES ('{$_GET['categorieId']}', '{$date}', '$ordine')";
        mysql_query($sql, db_c());
        
        $this->categorii_getSectiuniCaracteristici(null, $_GET['activeRow']);
    }
    
    public function categorii_editSectiune() {
        $date = $_GET['data'];
        $sql = "UPDATE `sectiuni_caracteristici` SET `sectiune` = '{$date}', WHERE `categorie_id` = '{$_GET['categorieId']}' AND `id` = '{$_GET['sectiuneId']}'";
        mysql_query($sql, db_c());
    }
    
    public function categorii_adaugaCaracteristica() {
        $date = json_decode(stripslashes($_GET['data']));
        $sql = "INSERT INTO `caracteristici` (`categorie_id`, `sectiune_id`, `caracteristica`, `preview`) VALUES ('{$_GET['categorieId']}', '{$_GET['sectiuneId']}', '{$date->caracteristica}', '" . intval($date->preview) . "')";
        mysql_query($sql, db_c());
        
        $this->categorii_getCaracteristici();
    }
    
    public function categorii_editCaracteristica() {
        $date = json_decode(stripslashes($_GET['data']));
        $sql = "UPDATE `caracteristici` SET `caracteristica` = '{$date->caracteristica}', `preview` = '" . intval($date->preview) . "' WHERE `id` = '{$_GET['id']}'";
        mysql_query($sql, db_c());
    }
}
?>