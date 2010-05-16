<?php
require 'config/check_login.php';

//get config
$sql = "SELECT * FROM `config` WHERE 1";
$config = mysql_query($sql, db_c());

//get zone
$zone = array();
$sql = "SELECT `id`, `zona` FROM `admin_zone` WHERE `zona` NOT IN ('Home', 'Setări')";
$result = mysql_query($sql, db_c());
while ($row = mysql_fetch_assoc($result)) {
    $zone[$row['id']] = $row['zona'];
}

function getRights($userId) {
    $drepturi = array();
    $lista = array('edit', 'insert', 'delete');
    $sql = "SELECT `zone_id`, `edit`, `insert`, `delete` FROM admin_drepturi WHERE `user_id` = '{$userId}'";
    $result = mysql_query($sql, db_c());
    while($row = mysql_fetch_assoc($result)) {
        $buffer = null;
        foreach ($lista as $k => $v) {
            if($row[$v])
            $buffer .= ($buffer ? '&nbsp;&nbsp;' : '') . '<img src="images/icons/_'. $v .'.png" width="12" height="12" />';
        }
        
        $drepturi[$row['zone_id']] = ($buffer ? $buffer : 0);
    }
    
    return $drepturi;
}

//stabilire extra Style / browser
if($browser == "IE")
$extraStyle = 'fieldset {padding:7px;}';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="../Templates/admin_template.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<base href="<?php echo $CALE_VIRTUALA_SERVER;?>" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>MyShop ADMIN</title>
<!-- InstanceEndEditable -->
<script type="text/javascript" src="scripts/popwin.js"></script>
<link href="css/style.css" rel="stylesheet" type="text/css" />
<!-- InstanceBeginEditable name="head" -->
<link href="scripts/prototype_window/themes/default.css" rel="stylesheet" type="text/css" />
<link href="scripts/prototype_window/themes/alphacube.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="scripts/prototype.js"></script>
<script type="text/javascript" src="scripts/setari.js"></script>
<script type="text/javascript" src="scripts/windowManager.js"></script>
<script type="text/javascript" src="scripts/prototype_window/javascripts/window.js"></script>
<style type="text/css">
fieldset table tbody tr td { border-bottom:1px solid #EFEFEF; }
<?php echo $extraStyle;?>
</style>
<script type="text/javascript">
function actionSet(action, id) {
	switch(action) {
		case 'edit': {
			popup('edit_user.php?action=edit&id=' + id, 'edit_user', 612, 490);
			fereastra.focus();
		} break;
		case 'new': {
			popup('edit_user.php?action=add', 'new', 612, 490);
			fereastra.focus();			
		} break;
		case 'delete': {
			window.location.href = 'delete.php?tip=user&id=' + id;
		} break;
	}
}
</script>
<!-- InstanceEndEditable -->
</head>

<body class="main">
<div id="main">
  <div id="top"><img src="images/box_left_top.gif" width="8" height="8" class="fLeft" /><img src="images/box_right_top.gif" width="8" height="8" class="fRight" /></div>
  <div id="header" class="clear">
    <div class="fLeft"></div>
	<div class="fRight"></div>
	<div id="header_content">
	  <div class="fLeft">
	    <div style="margin-left:95px; margin-top:20px"><img src="images/text_up.png" width="285" height="45" /></div>
	  </div>
	  <div class="fRight">
	    <div id="logout">
		  <a href="config/logout.php" class="fRight"><img src="images/close_session.png" width="40" height="40" border="0" /></a>
		  <div>
		    Autentificat: <?php echo $user->getUsername();?><br />
			<a href="javascript://" class="link1" onclick="popup('<?php echo $CALE_VIRTUALA_SERVER;?>config/change_password.php', 'change_pass', 498, 246); fereastra.focus();">Schimbă parola </a>
	 	  </div>
		</div>
      </div>
	</div>
  </div>
  <div id="menu" class="clear">
    <div class="fRight"></div>
    <?php echo $user->printMenu(basename(__FILE__));?>
  </div>
  <div id="content" class="clear">
	<div><!-- InstanceBeginEditable name="center" -->
 <fieldset style="border:1px solid #777777">
          <legend style="font-size:14px; color:#333333"><img src="images/icons/administratori.png" width="45" height="45" style="vertical-align: middle;" />Management utilizatori şi drepturi de acces  &nbsp;</legend>
          <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
              <td><div class="blue_box" style="width:100px; padding:3px; margin-top:5px"><img src="images/icons/add_mic.png" width="16" height="16" border="0" style="vertical-align: middle;" /> <a href="javascript://" onclick="actionSet('new');">Adaugă utilizator </a></div></td>
              <td align="right" valign="bottom"><div style="margin-bottom:4px; margin-right:5px"><img src="images/icons/_view.png" width="12" height="12" style="vertical-align:middle" /> = Vizualizare | <img src="images/icons/_insert.png" width="12" height="12" style="vertical-align:middle" /> = Inserare | <img src="images/icons/_edit.png" width="12" height="12" style="vertical-align:middle" /> = Editare | <img src="images/icons/_delete.png" width="12" height="12" style="vertical-align:middle" /> = Ştergere</div></td>
            </tr>
          </table>
          <table width="100%" border="0" cellpadding="3" cellspacing="0" class="table_border">
            <tr>
              <td width="120" rowspan="2" align="center" class="table_header">Utilizator</td>
              <td colspan="<?php echo sizeof($zone);?>" align="center" class="table_header" style="border-bottom:1px solid #999999">Zone</td>
              <td width="45" rowspan="2" align="center" class="table_header">Power user </td>
              <td width="90" rowspan="2" align="center" class="table_header">Acţiuni</td>
            </tr>
            <tr>
              <?php echo '<td align="center" class="table_header">' . implode('</td><td align="center" class="table_header">', $zone) . '</td>'; ?>
            </tr>
            <?php
            $sql = "SELECT `id`, `username`, CONCAT(`nume`, ' ', `prenume`) AS `nume`, `tip` FROM `admin` WHERE 1";
            $users = mysql_query($sql, db_c());
            
            $i = 0;
            while($row = mysql_fetch_assoc($users)) {
                $i++;
                $drepturi = getRights($row['id']);
                $buffer = '<tr '.($i%2 ? null : ' bgcolor="#F5F5F5"').'onmouseover="this.style.backgroundColor=\'#CCD9F2\';" onmouseout="this.style.backgroundColor=\'\'">' . "\n";
                $buffer .= '<td align="left"' . ($i%2 ? ' class="even_row"' : '') . '><strong>' . $row['username'] . '</strong><br /><span style="color:#555555; font-family:Verdana, Arial, Helvetica, sans-serif; font-size:10px">' . $row['nume'] . '</span></td>' . "\n";
                foreach ($zone as $key => $zona) {
                    if(isset($drepturi[$key])) {
                        if(!$drepturi[$key]) {
                            $buffer .= '<td align="center"' . ($i%2 ? ' class="even_row"' : '') . '><img src="images/icons/_view.png" width="12" height="12" /></td>' . "\n";
                        }
                        else {
                            $buffer .= '<td align="center"' . ($i%2 ? ' class="even_row"' : '') . '>' . $drepturi[$key] . "</td>\n";
                        }
                    }
                    else {
                        $buffer .= '<td align="center"' . ($i%2 ? ' class="even_row"' : '') . '><img src="images/icons/block.png" width="16" height="16" /></td>' . "\n";
                    }
                }
                $buffer .= '<td align="center"' . ($i%2 ? ' class="even_row"' : '') . '><img src="images/icons/' . ($row['tip'] == "powerUser" ? 'ok.png' : 'lipsa.png') . '" width="16" height="16" /></td>' . "\n";
                $buffer .= '<td align="center"' . ($i%2 ? ' class="even_row"' : '') . '><a href="javascript://" onclick="actionSet(\'edit\', ' . $row['id'] . ')">Editează</a>&nbsp;|&nbsp;<a href="javascript://" onclick="actionSet(\'delete\', ' . $row['id'] . ')">Şterge</a></td>' . "\n";
                
                echo $buffer;
            }
            ?>
          </table>
          </fieldset>
          <br />
          <br />	
          <fieldset style="border:1px solid #777777">
          <legend style="font-size:14px; color:#333333"><img src="images/icons/config.png" width="50" height="50" style="vertical-align: middle" />Setări personalizabile &nbsp;</legend>
          <table width="100%" border="0" cellspacing="0" cellpadding="5" class="table_border">
            <colgroup>
              <col width="225" />
              <col />
              <col width="150" />
              <col width="80" />
            </colgroup>
            <thead>
              <tr>
                <td align="left" class="table_header">Variabilă</td>
                <td align="left" class="table_header">Descriere</td>
                <td align="left" class="table_header">Valoare</td>
                <td align="center" class="table_header">Acţiuni</td>
              </tr>
            </thead>
            <tbody>
              <?php while($row = mysql_fetch_assoc($config)) { ?>
              <tr onmouseover="this.style.backgroundColor='#CCD9F2'" onmouseout="this.style.backgroundColor=''" id="row_<?php echo $row['id'];?>">
                <td><?php echo $row['parametru'];?></td>
                <td><?php echo $row['descriere'];?></td>
                <td><?php echo $row['valoare'];?></td>
                <td align="center"><a href="javascript:edit(<?php echo $row['id'];?>)" class="link3"><img src="images/icons/edit.png" width="16" height="16" class="valignMiddle" /> Edit</a></td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
          </fieldset>
          <div class="hidden" id="_winEditConfig">
            <img src="images/spacer.gif" height="20" width="100%" alt="" />
            <div class="marginBottom clear"><span class="fLeft">&nbsp;&nbsp;Variabila:&nbsp;</span><strong class="fLeft" id="variabila"></strong></div>
            <div class="marginBottom clear"><span class="fLeft">&nbsp;&nbsp;Descriere:&nbsp;</span><textarea id="descriere" name="descriere" rows="3" class="fLeft" style="width:282px"></textarea></div>
            <div class="marginBottom clear" style="margin-top:45px;"><span class="fLeft">&nbsp;&nbsp;Valoare:&nbsp;</span><input type="text" id="valoare" name="valoare" size="53" /></div>
            <p class="center clear">
              <img src="images/spacer.gif" height="15" width="100%" alt="" />
              <input type="button" name="editConfig_continua" id="editConfig_continua" value="  Salvează  " class="button_win" /> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
              <input type="button" name="editConfig_renunt" id="editConfig_renunt" value="  Renunţ  " class="button_win" />
            </p>
          </div>          
          <!-- InstanceEndEditable --></div>
  </div>
  <div id="bottom"><img src="images/box_left_bottom.gif" width="8" height="8" class="fLeft" /><img src="images/box_right_bottom.gif" width="8" height="8" class="fRight" /></div>  
</div>
<div id="copyright" class="clear">Copyright, &copy; 2010 Rareş Vlăsceanu. Toate drepturile rezervate. </div>
</body>
<!-- InstanceEnd --></html>
