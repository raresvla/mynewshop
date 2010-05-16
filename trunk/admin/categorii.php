<?php
require 'config/check_login.php';
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
<script type="text/javascript" src="scripts/categorii.js"></script>
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
          <fieldset style="border:1px solid #777777;<?php echo ($browser == "IE" ? 'padding:7px;' : '');?>">
            <legend style="font-size:14px; color:#333333"><img src="images/icons/categorii.png" width="40" height="40" />Categorii de produse&nbsp;</legend>
		    <div style="float: left; width:210px; border-right:1px solid #999999">
			  <img src="images/spacer.gif" width="1" height="5" />
			  <p class="fLeft" style="padding:0px; margin:0px; padding-left:3px"><img src="images/open_all.gif" width="15" height="15" /> <a href="javascript: d.openAll();">Deschide toate </a></p>
			  <p class="fRight" style="padding:0px; margin:0px; padding-right:8px"><img src="images/close_all.gif" width="15" height="15" /> <a href="javascript: d.closeAll();">Închide toate</a></p>
			  <p class="clear" style="width:97%; border-bottom:1px solid #CCCCCC; margin:0px 0px <?php echo ($browser == "IE" ? 5 : 10);?>px 0px; padding-top:5px"></p>
			  <div id="tree"></div>
			</div>
			<div style="float: right; width: 610px"><iframe id="viewCurrentCateg" name="viewCurrentCateg" src="viewCategorie.php?base=1" frameborder="0" style="width: 100%; height: 100%" scrolling="no"></iframe></div>
          </fieldset>
       <!-- InstanceEndEditable --></div>
  </div>
  <div id="bottom"><img src="images/box_left_bottom.gif" width="8" height="8" class="fLeft" /><img src="images/box_right_bottom.gif" width="8" height="8" class="fRight" /></div>  
</div>
<div id="copyright" class="clear">Copyright, &copy; 2010 Rareş Vlăsceanu. Toate drepturile rezervate. </div>
</body>
<!-- InstanceEnd --></html>
