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
<link href="scripts/prototype_window/themes/default.css" rel="stylesheet" type="text/css" />
<link href="scripts/prototype_window/themes/alphacube.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="scripts/prototype.js"></script>
<script type="text/javascript" src="scripts/comenzi.js"></script>
<script type="text/javascript" src="scripts/windowManager.js"></script>
<script type="text/javascript" src="scripts/prototype_window/javascripts/window.js"></script>
<style type="text/css">
table.table_border tbody#tableContent tr.viewed { background-color: #F5F5F5; }
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
      <legend style="font-size:14px; color:#333333"><img src="images/icons/comenzi.png" width="40" height="40" class="valignMiddle" />Comenzi&nbsp;</legend>
	  <div style="margin-top:10px">
	    <a href="javascript:detaliiComanda()" class="blueButton fLeft"><img src="images/icons/arrowDown.png" alt="" width="16" height="16" /> Vezi detalii </a>
	    <?php if($user->hasRight('comenzi', 'delete')) { ?>
		<a href="javascript:stergeComenzi()" class="blueButton fRight"><img src="images/icons/delete.png" alt="" width="16" height="16" /> Şterge</a>
		<?php } if($user->hasRight('comenzi', 'edit')) { ?>
		<a href="javascript:statusComanda()" class="blueButton fRight marginRight"><img src="images/icons/status_comanda.png" alt="" width="16" height="16" /> Status comandă</a>
		<a href="javascript:statusPlata()" class="blueButton fRight marginRight"><img src="images/icons/status_plata.png" alt="" width="16" height="16" /> Status plată</a>
		<?php } ?>
	  </div>
	  <div class="clear"></div>
      <table width="100%" border="0" cellpadding="5" cellspacing="0" class="table_border">
	    <colgroup>
		  <col width="40" />
		  <col width="115" />
		  <col width="90" />
		  <col />
		  <col width="100" />
		  <col width="80" />
		  <col width="80" />
		  <col width="60" />
		  <col width="60" />
		</colgroup>
		<thead id="tableHeader">
		</thead>
		<tbody id="tableContent">
		  <tr><td colspan="9"><div class="loader" align="center" style="padding: 15px;">Preluare informatii...<br /> <img src="images/loading.gif" width="226" height="19" /></td></tr>
		</tbody>
      </table>
      <div id="listingContainer"></div>
	  </fieldset>
	  <div class="hidden" id="_winStatusPlata">
        <img src="images/spacer.gif" height="20" width="100%" alt="" />
        <div class="marginBottom"><span class="fLeft">&nbsp;&nbsp;Comanda ID:&nbsp;</span><div class="fLeft" id="statusPlata_comandaId"></div></div>
        <div class="marginBottom"><span class="fLeft">&nbsp;&nbsp;Cumpărător:&nbsp;</span><div class="fLeft" id="statusPlata_cumparator"></div></div>
        <div class="clear marginBottom">
          <span class="fLeft" style="width:100px">&nbsp;&nbsp;Status:</span>
          <select name="statusPlata" id="statusPlata" class="fLeft inputcol" style="width:200px">
            <option value="inAsteptare">In aşteptare</option>
            <option value="platita">Plătită</option>
            <option value="anulata">Anulată</option>
          </select>
        </div>
        <p class="center clear">
          <img src="images/spacer.gif" height="15" width="100%" alt="" />
          <input type="button" name="statusPlata_continua" id="statusPlata_continua" value="  Salvează  " class="button_win" /> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          <input type="button" name="statusPlata_renunt" id="statusPlata_renunt" value="  Renunţ  " class="button_win" />
        </p>
      </div>
	  <div class="hidden" id="_winStatusComanda">
        <img src="images/spacer.gif" height="20" width="100%" alt="" />
        <div class="marginBottom"><span class="fLeft">&nbsp;&nbsp;Comanda ID:&nbsp;</span><div class="fLeft" id="statusComanda_comandaId"></div></div>
        <div class="marginBottom"><span class="fLeft">&nbsp;&nbsp;Cumpărător:&nbsp;</span><div class="fLeft" id="statusComanda_cumparator"></div></div>
        <div class="clear marginBottom">
          <span class="fLeft" style="width:100px">&nbsp;&nbsp;Status:</span>
          <select name="statusComanda" id="statusComanda" class="fLeft inputcol" style="width:200px">
            <option value="confirmata">Confirmată</option>
            <option value="neconfirmata">Neconfirmată</option>
            <option value="livrata">Livrată</option>
            <option value="returnata">Returnată</option>
            <option value="anulata">Anulată</option>
          </select>
        </div>
        <p class="center clear">
          <img src="images/spacer.gif" height="15" width="100%" alt="" />
          <input type="button" name="statusComanda_continua" id="statusComanda_continua" value="  Salvează  " class="button_win" /> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          <input type="button" name="statusComanda_renunt" id="statusComanda_renunt" value="  Renunţ  " class="button_win" />
        </p>
      </div>
	<!-- InstanceEndEditable --></div>
  </div>
  <div id="bottom"><img src="images/box_left_bottom.gif" width="8" height="8" class="fLeft" /><img src="images/box_right_bottom.gif" width="8" height="8" class="fRight" /></div>  
</div>
<div id="copyright" class="clear">Copyright, &copy; 2010 Rareş Vlăsceanu. Toate drepturile rezervate. </div>
</body>
<!-- InstanceEnd --></html>
