<?php
include("config/check_login.php");

switch ($_GET['action']) {
    case 'edit': {
        $sql = "SELECT * FROM `admin` WHERE `id` = '{$_GET['id']}'";
        $data = mysql_fetch_assoc(mysql_query($sql, db_c()));
    } break;
    
    case 'save': {
        //verifica valori trimise
        $toVerify = array("nume", "prenume", "username", "parola");
        if(!notBlank($toVerify, "_POST", $message)) {
            $error = true; 
        }
        else {
            if($_GET['source'] == "db") {
                $sql = "UPDATE `admin` SET `nume` = '" . mysql_escape_string($_POST['nume']) . "', `prenume` = '" . mysql_escape_string($_POST['prenume']) . "', `tip` = '{$_POST['tip']}' WHERE `id` = '{$_GET['id']}'";
                mysql_query($sql, db_c());
    
                $id = $_GET['id'];
                $sql = "DELETE FROM `admin_drepturi` WHERE `user_id` = '{$id}'";
                mysql_query($sql, db_c());
            } else {
                //verifica duplicat
                $sql = "SELECT COUNT(*) FROM `admin` WHERE `username` = '" . mysql_escape_string($_POST['username']) . "'";
                if(mysql_result(mysql_query($sql, db_c()), 0)) {
                    $error = true;
                    
                } else {
                    $sql = "INSERT INTO `admin` (`username`, `password`, `nume`, `prenume`, `tip`) VALUES ('" . mysql_escape_string($_POST['username']) . "', '" . md5($_POST['password']) . "', '" . mysql_escape_string($_POST['nume']) . "', '" . mysql_escape_string($_POST['prenume']) . "', '{$_POST['tip']}')";
                    mysql_query($sql, db_c());
                    
                    $id = mysql_insert_id();
                }
            }
            
            //stabilire drepturi
            $pattern = '/acces_([0-9]+)$/';
            foreach ($_POST as $key => $value) {
                if(preg_match($pattern, $key, $matches)) {
                    $zone_id = $matches[1];
                    $sql = "INSERT INTO `admin_drepturi` (`user_id`, `zone_id`, `edit`, `insert`, `delete`) VALUES ('{$id}', '{$zone_id}', '".intval($_POST["editare_{$zone_id}"])."', '".intval($_POST["inserare_{$zone_id}"])."', '".intval($_POST["stergere_{$zone_id}"])."')";
                    mysql_query($sql, db_c());
                }
            }
            
            $done = true;
        }
    } break;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="css/win.css" />
<!--[if IE]>
  <link rel="stylesheet" type="text/css" href="css/win-ie.css" />
<![endif]-->
<script type="text/javascript" src="scripts/prototype.js"></script>
<script type="text/javascript">
function accessManagement(id) {
	if($('acces_' + id).checked) {
		$('vizualizare_' + id).checked = 'checked';
		$('vizualizare_' + id).disabled = false;
		$('editare_' + id).disabled = false;
		$('inserare_' + id).disabled = false;
		$('stergere_' + id).disabled = false;
	}
	else {
		$('vizualizare_' + id).checked = false;
		$('vizualizare_' + id).disabled = true;
		$('editare_' + id).disabled = true;
		$('inserare_' + id).disabled = true;
		$('stergere_' + id).disabled = true;		
	}
}
</script>
<?php
if ($done)
{
	echo '</head>
<body>
<script language="javascript">
window.close(); window.opener.location.href = window.opener.location;
</script>
</body>
</html>';
	die();
}
?>
<title>Management utilizatori</title></head>

<body>
  <table class="full-height">
    <tbody><tr>
      <td align="center" style="padding-top:5px"><form action="edit_user.php?action=save&source=<?php echo ($_GET['action'] == "edit" ? "db&id={$_GET['id']}" : "new");?>" method="post" class="formular" id="edit">
        <fieldset id="box_container">
        <legend class="legend_box_container"><img src="images/icons/edit_big.png" alt="" width="25" height="25" style="vertical-align: middle;" /><strong>Management utilizatori </strong></legend>
        <div id="container">
          <fieldset class="box_content">
          <legend class="legend_box_content">Detalii personale </legend>
          <table width="100%" border="0" cellspacing="0" cellpadding="5">
            <colgroup>
              <col width="120" />
              <col />
            </colgroup>
            <tr>
              <td align="left">Nume:</td>
              <td align="left"><input name="nume" type="text" class="inputcol" id="nume" size="65" value="<?php echo $data['nume'];?>" /></td>
            </tr>
            <tr>
              <td align="left">Prenume:</td>
              <td align="left"><input name="prenume" type="text" class="inputcol" id="prenume" size="65" value="<?php echo $data['prenume'];?>" /></td>
            </tr>
            <tr>
              <td align="left">Tip cont: </td>
              <td align="left"><select name="tip" class="inputcol" id="tip">
                  <option value="regularUser"<?php echo ($data['tip'] == "regularUser" ? ' selected="selected"' : '')?>>Regular user</option>
                  <option value="powerUser"<?php echo ($data['tip'] == "powerUser" ? ' selected="selected"' : '')?>>Power user [Acces in zona de Configurare]</option>
              </select></td>
            </tr>
            <tr>
              <td align="left">Nume de utilizator:</td>
              <td align="left"><?php echo ($_GET['action'] == "edit" ? "<strong>{$data['username']}</strong>" : "");?>
                  <input type="<?php echo ($_GET['action'] == "edit" ? "hidden" : "text")?>" id="username" name="username" value="<?php echo $username;?>" class="inputcol" size="43" /></td>
            </tr>
            <tr>
              <td align="left">Parola:</td>
              <td align="left"><?php echo ($_GET['action'] == "edit" ? '<em>Criptat&#259; | </em>Resetează:' : "");?>
                <input name="password" type="text" class="inputcol" id="password" size="20" /></td>
            </tr>
          </table>
          </fieldset>
          <br />
          <fieldset class="box_content">
          <legend class="legend_box_content">Drepturi de acces </legend>
          <table width="100%" border="0" cellpadding="3" cellspacing="0" class="table_border">
            <colgroup>
              <col />
              <col width="65" />
              <col width="65" />
              <col width="65" />
              <col width="65" />
              <col width="65" />
            </colgroup>
            <tr>
              <td rowspan="2" align="center" class="table_header">Zona</td>
              <td rowspan="2" align="center" class="table_header">Acces</td>
              <td colspan="4" align="center" class="table_header" style="border-bottom:1px solid #999999">Drepturi</td>
            </tr>
            <tr>
              <td align="center" class="table_header">Vizualizare</td>
              <td align="center" class="table_header">Editare</td>
              <td align="center" class="table_header">Inserare</td>
              <td align="center" class="table_header">&#350;tergere</td>
            </tr>
            <?php
            if($_GET['action'] == "edit") {
                $sql = "SELECT z.id AS `id_zona`, z.zona, d.id, d.edit, d.insert, d.delete FROM `admin_zone` AS `z` LEFT JOIN `admin_drepturi` AS `d` ON z.id = d.zone_id AND user_id = {$_GET['id']} WHERE z.powerUserZone IS NULL AND z.zona != 'Home'";
            } else {
                $sql = "SELECT `id` AS `id_zona`, `zona` FROM `admin_zone` WHERE `powerUserZone` IS NULL AND `zona` != 'Home'"; 
            }
            
            $result = mysql_query($sql, db_c());
            
            $i = 0;
            while ($row = mysql_fetch_assoc($result)) {
                $acces = $row['id'];
            ?>
            <tr>
              <td align="left"><?php echo $row['zona'];?></td>
              <td align="center"><input type="checkbox" name="acces_<?php echo $row['id_zona'];?>" id="acces_<?php echo $row['id_zona'];?>" value="1"<?php echo ($acces ? ' checked="checked"' : "");?> onchange="accessManagement(<?php echo $row['id_zona'];?>)" /></td>
              <td align="center"><input type="checkbox" name="vizualizare_<?php echo $row['id_zona'];?>" id="vizualizare_<?php echo $row['id_zona'];?>" value="1"<?php echo ($acces ? ' checked="checked"' : 'disabled="disabled"');?> onchange="this.checked = !(this.checked)" /></td>
              <td align="center"><input type="checkbox" name="editare_<?php echo $row['id_zona'];?>" id="editare_<?php echo $row['id_zona'];?>" value="1"<?php echo ($row['edit'] ? ' checked="checked"' : (!$acces ? 'disabled="disabled"' : ""));?> /></td>
              <td align="center"><input type="checkbox" name="inserare_<?php echo $row['id_zona'];?>" id="inserare_<?php echo $row['id_zona'];?>" value="1"<?php echo ($row['insert'] ? ' checked="checked"' : (!$acces ? 'disabled="disabled"' : ""));?> /></td>
              <td align="center"><input type="checkbox" name="stergere_<?php echo $row['id_zona'];?>" id="stergere_<?php echo $row['id_zona'];?>" value="1"<?php echo ($row['delete'] ? ' checked="checked"' : (!$acces ? 'disabled="disabled"' : ""));?> /></td>
            </tr>
            <?php
                $i++;
            }
            ?>
          </table>
          </fieldset>
        </div>
        <table width="100%" border="0" cellspacing="0" cellpadding="3" style="margin-top:5px">
          <tr>
            <td align="center" style="background-color: #888888"><table width="400" border="0" cellspacing="0" cellpadding="3">
              <colgroup>
                <col width="50%" />
                <col />
              </colgroup>
              <tr>
                <td align="center"><input name="Submit" type="submit" class="button_win" value="Salveaz&#259;" /></td>
                <td align="center"><input name="butt" type="button" class="button_win" value="Renun&#355;" onclick="window.close();" /></td>
              </tr>
            </table></td>
          </tr>
        </table>
        </fieldset>
      </form>
    </td>
  </tr>
 </tbody>
</table>
<?php if($error) { $keys = array_keys($err); ?><script type="text/javascript">alert('<?php echo (str_replace("'", "\'", $message));?>'); document.forms['new'].<?php echo $err[$keys[0]];?>.focus();</script>
<?php } ?>
</body>
</html>