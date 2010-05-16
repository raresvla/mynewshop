<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>MyShop Login</title>
<style type="text/css">
html,body {
  margin:0;
  padding:0;
  height:100%;
  width:100%;
}

table.full-height {
  height:100%;
  width:100%;
  font-family:Verdana, Arial, Helvetica, sans-serif;
  font-size:11px
}
.inputcol {
	font-size : 11px;
	font-family : Verdana;
	font-weight: normal;
}
</style>
<?php if(stripos($_SERVER['HTTP_USER_AGENT'], "msie")) { ?>
<style type="text/css">
fieldset {
	position: relative;
	margin-top:1em;
	padding-top:.75em;
}

legend {
	position:absolute;
	top: -.5em;
	left: .5em;
}
</style>
<?php } ?>
</head>
<body>
  <table class="full-height">
    <tbody><tr>
    <td align="center"><fieldset style="background-color:#EFEFEF; border:1px solid #666666; width:350px; padding:<?php echo (stripos($_SERVER['HTTP_USER_AGENT'], "msie") ? 5 : 0);?>"><legend style="color:#000000">MyShop Admin</legend>
      <table width="100%" border="0" cellspacing="0" cellpadding="5">
        <tr>
          <td align="right"><img src="<?php echo $CALE_VIRTUALA_SERVER;?>images/login.png" alt="" width="80" height="80" /></td>
          <td><form id="form_login" method="post" action="" style="margin:0; padding:0; white-space:normal; display:inline;">
            <table width="100%" border="0" cellspacing="0" cellpadding="1">
              <tr>
                <td align="right">Utilizator:&nbsp;</td>
                <td><input name="username" type="text" id="username" size="22" value="" class="inputcol" /></td>
              </tr>
              <tr>
                <td align="right"></td>
                <td></td>
              </tr>
              <tr>
                <td align="right">Parola:&nbsp;</td>
                <td><input name="password" type="password" id="password" size="22" value="" class="inputcol" /></td>
              </tr>
              <tr>
                <td colspan="2" align="center"><br />
                  <input name="login" type="submit" id="login" value="     Login     " style="font-family:Tahoma; font-size:11px; padding:3px;" /><input type="hidden" id="__login" name="__login" value="1" /></td>
                </tr>
            </table>
          </form></td>
        </tr>
        <tr>
          <td colspan="2" style="padding:0"><img src="images/spacer.gif" alt="" width="1" height="1" /></td>
        </tr>
        <tr>
          <td colspan="2" style="color:#FFFFFF; background-color: #666666" align="left"><?php echo ($user->msg ? $user->msg : "<strong>ATENŢIE:</strong> Accesul în această zonă este restricţionat.");?></td>
          </tr>
      </table>
    </fieldset>
      </td>
    </tr></tbody></table>
    <script type="text/javascript">document.getElementById('username').focus();</script>
</body>
</html>