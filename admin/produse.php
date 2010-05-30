<?php
require 'config/check_login.php';

$sql = "SELECT COUNT(*) FROM `categorii` WHERE 1";
$totalCategorii = mysql_result(mysql_query($sql, db_c()), 0);

$sql = "SELECT COUNT(*) FROM `produse` WHERE 1";
$totalProduse = mysql_result(mysql_query($sql, db_c()), 0);
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
<link href="scripts/dtree.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="scripts/prototype.js"></script>
<script type="text/javascript" src="scripts/dtree.js"></script>
<script type="text/javascript" src="scripts/produse.js"></script>
<style type="text/css">
#rightContent table#products tbody tr td { border-bottom:1px solid #EFEFEF; }
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
          <fieldset style="height:100%; border:1px solid #777777;<?php echo ($browser == "IE" ? 'padding:7px;' : '');?>">
            <legend style="font-size:14px; color:#333333"><img src="images/icons/produse.png" width="40" height="40" /> Produse&nbsp;</legend>
		    <div id="leftContent" class="fLeft" style="width:220px; height:100%; border-right:1px solid #999999">
			  <table cellpadding="0" cellspacing="0" width="100%" style="height:100%">
			    <colgroup>
				  <col />
				  <col width="13px" />
				</colgroup>
			    <tr>
			      <td id="categories_tree" style="padding-right:7px; padding-top:5px">
				    <div class="fLeft"><img src="images/open_all.gif" width="15" height="15" /> <a href="javascript: d.openAll();">Deschide toate </a></div>
				    <div class="fRight"><img src="images/close_all.gif" width="15" height="15" /> <a href="javascript: d.closeAll();">Închide toate</a></div>
				    <div style="clear:left; border-bottom:1px solid #CCCCCC; padding-top:5px;"></div>
				    <div id="tree" style="padding-top:10px"></div>
				  </td>
				  <td class="wrapper_opened" onclick="showHideTree(this)">&nbsp;</td>
			    </tr>
			  </table>
			</div>
			<div id="rightContent" style="margin-left:240px; height:100%">
              <table width="100%" border="0" cellspacing="0" cellpadding="5">
                <tr>
                  <td colspan="3"><img src="images/spacer.gif" height="3" width="100%" /> </td>
                </tr>
                <tr>
                  <td width="100">Total categorii: </td>
                  <td><strong><?php echo $totalCategorii;?></strong></td>
                  <td rowspan="2" valign="middle" align="right"><img width="184" height="37" border="0" alt="MyShop" src="http://<?php echo $config->DOMENIU_SITE; ?>/img/layout/logo.png" /></td>
                </tr>
                <tr>
                  <td width="100">Total produse: </td>
                  <td><strong><?php echo $totalProduse;?></strong></td>
                </tr>
                <tr>
                  <td colspan="3">
                    <hr size="1" style="border-width: 1px 0px 0px 0px; border-style: solid; border-color: #CCCCCC;" />
                    Conform setărilor contului dvs., aveţi următoarele drepturi:
                    <ul style="margin:8px 0px; list-style:none">
                      <li style="padding:3px"><img src="images/icons/<?php echo ($user->hasRight('produse', 'edit') ? 'ok' : 'renunt')?>.png" width="16" height="16" alt="" /> &nbsp;Editare</li>
                      <li style="padding:3px"><img src="images/icons/<?php echo ($user->hasRight('produse', 'insert') ? 'ok' : 'renunt')?>.png" width="16" height="16" alt="" /> &nbsp;Adăugare</li>
                      <li style="padding:3px"><img src="images/icons/<?php echo ($user->hasRight('produse', 'delete') ? 'ok' : 'renunt')?>.png" width="16" height="16" alt="" /> &nbsp;Ştergere</li>
                    </ul>
                  </td>
                </tr>
               </table>
			</div>
          </fieldset>
          <!-- InstanceEndEditable --></div>
  </div>
  <div id="bottom"><img src="images/box_left_bottom.gif" width="8" height="8" class="fLeft" /><img src="images/box_right_bottom.gif" width="8" height="8" class="fRight" /></div>  
</div>
<div id="copyright" class="clear">Copyright, &copy; 2010 Rareş Vlăsceanu. Toate drepturile rezervate. </div>
</body>
<!-- InstanceEnd --></html>
