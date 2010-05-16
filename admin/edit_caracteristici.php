<?php
include("config/check_login.php");

$sql = "SELECT `denumire` FROM `categorii` WHERE `id` = '{$_GET['id']}'";
$denumire = mysql_result(mysql_query($sql, db_c()), 0);
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
<style type="text/css">
#sectiuniCaracteristici {
	border-top: 0px;
	height: 170px;
	overflow-x: hidden;
	overflow-y: auto;
	background-color: #FFFFFF;
}
#caracteristici {
	border-top: 0px;
	height: 210px;
	overflow-x: hidden;
	overflow-y: auto;
	background-color: #FFFFFF;
}
</style>
<link href="scripts/prototype_window/themes/default.css" rel="stylesheet" type="text/css" />
<link href="scripts/prototype_window/themes/alphacube.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="scripts/prototype.js"></script>
<script type="text/javascript" src="scripts/edit_caracteristici.js"></script>
<script type="text/javascript" src="scripts/windowManager.js"></script>
<script type="text/javascript" src="scripts/prototype_window/javascripts/window.js"></script>
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
<title>Caracteristici</title>
</head>

<body>
  <table class="full-height">
    <tbody><tr>
      <td align="center" style="padding-top:5px"><form action="edit_user.php?action=save&source=<?php echo ($_GET['action'] == "edit" ? "db&id={$_GET['id']}" : "new");?>" method="post" class="formular" id="edit">
        <fieldset id="box_container">
          <legend class="legend_box_container"><img src="images/icons/caracteristici_big.png" alt="" width="25" height="25" style="vertical-align: middle;" /> <strong><?php echo $denumire;?></strong></legend>
          <div id="container">
            <input type="hidden" name="_categorie" id="_categorie" value="<?php echo $_GET['id'];?>" />
            <fieldset class="box_content">
            <legend class="legend_box_content">Secţiuni </legend>
            <a href="javascript:addSectiune()" class="blueButton fLeft marginRight"><img src="images/icons/add_mic.png" alt="" width="16" height="16" /> Adaugă</a>
            <a href="javascript:move('up')" class="blueButton fLeft marginRight"><img src="images/icons/move_up.png" alt="" width="16" height="16" /> Up</a>
            <a href="javascript:move('down')" class="blueButton fLeft"><img src="images/icons/move_down.png" alt="" width="16" height="16" /> Down</a>
            <a href="javascript:stergeSectiune()" class="blueButton fRight"><img src="images/icons/delete.png" alt="" width="16" height="16" /> Şterge</a>
            <a href="javascript:editSectiune()" class="blueButton fRight marginRight"><img src="images/icons/edit.png" alt="" width="16" height="16" /> Editează</a>
            <table width="100%" border="0" cellpadding="3" cellspacing="0" class="table_border clear">
              <colgroup>
                <col width="50" />
                <col />
              </colgroup>
              <tr>
                <td align="center" class="table_header">#</td>
                <td align="center" class="table_header">Denumire</td>
              </tr>
            </table>
            <div id="sectiuniCaracteristici" class="table_border"><div class="loader">Preluare informatii...<br /> <img src="images/loading.gif" width="226" height="19" /></div></div>
          </fieldset>
          <br />
          <fieldset class="box_content">
            <legend class="legend_box_content">Caracteristici</legend>
            <a href="javascript:addCaracteristica()" class="blueButton fLeft"><img src="images/icons/add_mic.png" alt="" width="16" height="16" /> Adaugă</a>
            <a href="javascript:stergeCaracteristici()" class="blueButton fRight"><img src="images/icons/delete.png" alt="" width="16" height="16" /> Şterge</a>
            <a href="javascript:editCaracteristica()" class="blueButton fRight marginRight"><img src="images/icons/edit.png" alt="" width="16" height="16" /> Editează</a>
            <table width="100%" border="0" cellpadding="3" cellspacing="0" class="table_border clear">
              <colgroup>
                <col width="50" />
                <col />
                <col width="45"/>
              </colgroup>
              <tr>
                <td align="center" class="table_header">#</td>
                <td align="center" class="table_header">Denumire</td>
                <td align="center" class="table_header">Preview</td>
              </tr>
            </table>
		    <div id="caracteristici" class="table_border"><div class="loader"><img src="images/icons/info2.png" width="16" height="16" class="valignMiddle" style="padding:5px 0px" /> Selectati o secţiune din lista de mai sus.</div></div>		  
          </fieldset>
        </div>
        <table width="100%" border="0" cellspacing="0" cellpadding="3" style="margin-top:5px">
          <tr>
            <td align="center" style="background-color:#888888"><input name="inchide" type="button" class="button_win" value="Închide" onclick="window.close();" /></td>
          </tr>
        </table>
        </fieldset>
      </form></td>
  </tr>
 </tbody>
</table>
<div class="hidden" id="_winSectiuni">
  <img src="images/spacer.gif" height="20" width="100%" alt="" />
  <div class="marginBottom"><input type="text" name="sectiune" id="sectiune" class="fRight inputcol" size="45" /><span class="fRight">Denumire:&nbsp;</span></div>
  <p class="center">
    <input type="button" name="sectiuni_continua" id="sectiuni_continua" value="  Salveaza  " class="button_win" /> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <input type="button" name="sectiuni_renunt" id="sectiuni_renunt" value="  Renunt  " class="button_win" />
  </p>
</div>
<div class="hidden" id="_winCaracteristici">
  <img src="images/spacer.gif" height="20" width="100%" alt="" />
  <div class="marginBottom"><input type="text" name="caracteristica" id="caracteristica" class="fRight inputcol" size="45" /><span class="fRight">Caracteristica:&nbsp;</span></div>
  <div class="clear marginBottom"><input type="checkbox" name="caracteristica_preview" id="caracteristica_preview" class="fRight inputcol" style="margin-right:300px"/><span class="fRight">Preview:&nbsp;</span></div>
  <p class="center">
    <input type="button" name="caracteristici_continua" id="caracteristici_continua" value="  Salveaza  " class="button_win" /> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <input type="button" name="caracteristici_renunt" id="caracteristici_renunt" value="  Renunt  " class="button_win" />
  </p>
</div>
</body>
</html>