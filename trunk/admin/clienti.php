<?php
require 'config/check_login.php';
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
<script type="text/javascript" src="scripts/prototype.js"></script>
<script type="text/javascript" src="scripts/clienti.js"></script>
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
      <legend style="font-size:14px; color:#333333"><img src="images/icons/clienti.png" width="40" height="40" class="valignMiddle" />Clienţi&nbsp;</legend>
	  <div style="margin-top:10px">
          <!--
	    <a href="javascript:veziComenzi()" class="blueButton fLeft"><img src="images/icons/comenzi.png" alt="" width="16" height="16" /> Vizualizează comenzi</a>
          -->
	    <?php if($user->hasRight('clienti', 'delete')) { ?>
		<a href="javascript:stergeClienti()" class="blueButton fRight"><img src="images/icons/delete.png" alt="" width="16" height="16" /> Şterge</a>
		<?php } if($user->hasRight('clienti', 'edit')) { ?>
		<a href="javascript:suspendaClienti()" class="blueButton fRight marginRight"><img src="images/icons/suspenda.png" alt="" width="16" height="16" /> Suspendă</a>
		<a href="javascript:activeazaClienti()" class="blueButton fRight marginRight"><img src="images/icons/activeaza.png" alt="" width="16" height="16" /> Activează</a>
		<?php } ?>
	  </div>
	  <div class="clear"></div>
      <table width="100%" border="0" cellpadding="5" cellspacing="0" class="table_border">
	    <colgroup>
		  <col width="40" />
		  <col />
		  <col width="200" />
		  <col width="115" />
		  <col width="90" />
		  <col width="60" />
		</colgroup>
		<thead id="tableHeader">
		</thead>
		<tbody id="tableContent">
		  <tr><td colspan="6"><div class="loader" align="center" style="padding: 15px;">Preluare informatii...<br /> <img src="images/loading.gif" width="226" height="19" /></td></tr>
		</tbody>
      </table>
      <div id="listingContainer"></div>
	  </fieldset>
	<!-- InstanceEndEditable --></div>
  </div>
  <div id="bottom"><img src="images/box_left_bottom.gif" width="8" height="8" class="fLeft" /><img src="images/box_right_bottom.gif" width="8" height="8" class="fRight" /></div>  
</div>
<div id="copyright" class="clear">Copyright, &copy; 2010 Rareş Vlăsceanu. Toate drepturile rezervate. </div>
</body>
<!-- InstanceEnd --></html>
