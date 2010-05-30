<?php
require 'config/check_login.php';

//membri noi
$sql = "SELECT COUNT(*) FROM `membri` WHERE `data_inregistrarii` = DATE(NOW())";
$membriNoi = mysql_result(mysql_query($sql, db_c()), 0);

//comenzi noi
$sql = "SELECT COUNT(*) FROM `comenzi` WHERE DATE(`data`) = DATE(NOW())";
$comenziNoi['total'] = mysql_result(mysql_query($sql, db_c()), 0);
$sql = "SELECT COUNT(*) FROM `comenzi` WHERE DATE(`data`) = DATE(NOW()) AND `new` = 1";
$comenziNoi['noi'] = mysql_result(mysql_query($sql, db_c()), 0);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/admin_template.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<base href="<?php echo $CALE_VIRTUALA_SERVER;?>" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>MyShop ADMIN</title>
<!-- InstanceEndEditable -->
<script type="text/javascript" src="scripts/popwin.js"></script>
<link href="css/style.css" rel="stylesheet" type="text/css" />
<!-- InstanceBeginEditable name="head" -->
<style type="text/css">
div.fLeft { background-color: #F3F7FC; }
div.fLeft.marginLeft { margin-left:70px; }
div.fLeft label { display: block; width:150px; text-align: center; font-weight: bold; border-color: #CCCCCC; border-style:solid; border-width: 0px 1px 1px 0px; padding: 3px 0px; background-color:#F5F5F5; }
div.fLeft ul { padding-left:20px; }
</style>
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
	  <fieldset style="border:1px solid #777777"<?php echo (stripos($_SERVER['HTTP_USER_AGENT'], "msie") !== false ? ' style="padding:7px;"' : '');?>>
          <legend style="font-size:14px; color:#333333"><img src="images/icons/azi.png" width="50" height="50" class="valignMiddle" /><?php echo date("l, j F, Y");?>&nbsp;</legend>
          <table width="100%" border="0" cellspacing="0" cellpadding="5" class="clear" id="stats">
            <colgroup>
              <col width="100" />
              <col />
              <col />
            </colgroup>
            <tr>
              <td colspan="3"><img src="images/spacer.gif" height="3" width="100%" /> </td>
            </tr>
            <tr>
              <td>&nbsp;&raquo; Membri noi: </td>
              <td><strong><?php echo $membriNoi;?></strong></td>
              <td rowspan="2" valign="middle" align="right"><img width="184" height="37" border="0" alt="MyShop" src="http://<?php echo $config->DOMENIU_SITE; ?>/img/layout/logo.png" /></td>
            </tr>
            <tr>
              <td>&nbsp;&raquo; Comenzi noi: </td>
              <td><strong><?php echo $comenziNoi['total'];?></strong><?php echo ($comenziNoi['noi'] ? ' &nbsp;&nbsp;(<strong>' . $comenziNoi['noi'] . '</strong> comenzi nevizualizate)' : '');?></td>
            </tr>
            <tr>
              <td colspan="3">
                <hr size="1" style="border-width: 1px 0px 0px 0px; border-style: solid; border-color: #CCCCCC;" />
                <br />
                <p align="left">Conform setărilor contului dvs., aveţi următoarele drepturi:</p>
                <?php
                $i = 0;
                foreach ($user->getAllRights() as $section => $rights) {
                ?>
                <div class="fLeft<?php echo($i ? ' marginLeft' : '');?>"><label><?php echo ucfirst($section);?>:</label>
                  <ul style="margin:8px 0px; list-style:none">
                    <li style="padding:3px"><img src="images/icons/<?php echo ($rights['edit'] ? 'ok' : 'renunt')?>.png" width="16" height="16" alt="" /> &nbsp;Editare</li>
                    <li style="padding:3px"><img src="images/icons/<?php echo ($rights['insert'] ? 'ok' : 'renunt')?>.png" width="16" height="16" alt="" /> &nbsp;Adăugare</li>
                    <li style="padding:3px"><img src="images/icons/<?php echo ($rights['delete'] ? 'ok' : 'renunt')?>.png" width="16" height="16" alt="" /> &nbsp;Ştergere</li>
                  </ul>
                </div>
                <?php $i++; } ?>
              </td>
            </tr>
          </table>
      </fieldset>
    <!-- InstanceEndEditable --></div>
  </div>
  <div id="bottom"><img src="images/box_left_bottom.gif" width="8" height="8" class="fLeft" /><img src="images/box_right_bottom.gif" width="8" height="8" class="fRight" /></div>  
</div>
<div id="copyright" class="clear">Copyright, &copy; 2010 Rareş Vlăsceanu. Toate drepturile rezervate. </div>
</body>
<!-- InstanceEnd --></html>
