<?php
include("check_login.php");

if(isset($_POST['current_password']) && isset($_POST['new_password'])) {
	foreach ($_POST as $key => $value)
	${$key} = md5((!get_magic_quotes_gpc() ? addslashes($value) : $value));
	
	if($current_password != $_SESSION['parola']) {
	    $msg = "Parola introdusă nu a putut fi validată!";
	} else {
		$sql = "UPDATE `admin` SET `parola` = '$new_password' WHERE `username` = '{$_SESSION['username']}' AND `parola` = '{$_SESSION['parola']}' LIMIT 1";
		mysql_query($sql, db_c()) or die(mysql_error());
		$msg = "Noua parolă a fost înregistrată cu success.";
	}

	unset($_SESSION['username']);
	unset($_SESSION['password']);
	$_SESSION = array();
	session_destroy();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
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
.button_win {
	font-family:Tahoma;
	font-size:11px;
	padding:3px;
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
<script type="text/javascript">
function allTrim(sString) {
	while (sString.substring(0,1) == ' ') {
		sString = sString.substring(1, sString.length);
	}
	while (sString.substring(sString.length-1, sString.length) == ' ') {
		sString = sString.substring(0,sString.length-1);
	}
	return sString;
}

function formValidate(theForm)
{
	theForm.current_password.value=allTrim(theForm.current_password.value);
	theForm.new_password.value=allTrim(theForm.new_password.value);
	theForm.repeat_password.value=allTrim(theForm.repeat_password.value);

	if (theForm.current_password.value == "") {
		alert("Introduceţi parola curentă !");
		theForm.current_password.focus();
		return (false);
	}

	if (theForm.new_password.value == "") {
		alert("Introduceţi noua parolă !");
		theForm.new_password.focus();
		return (false);
	}

	if (theForm.repeat_password.value == "") {
		alert("Reintroduceţi noua parolă !");
		theForm.repeat_password.focus();
		return (false);
	}

	if (theForm.new_password.value != theForm.repeat_password.value) {
		alert("Cele două parole nu corespund !");
		theForm.new_password.value = "";
		theForm.repeat_password.value = "";
		theForm.new_password.focus();
		updatestrength( theForm.new_password.value );
		return (false);
	}

	if(window.confirm('După validarea datelor introduse, sesiunea dumneavoastră va fi încheiată.\nVă recomandăm ca să finalizaţi toate lucrările pe care le efectuaţi şi abia apoi să schimbaţi parola.\n\nContinuaţi ?'))
	return (true);
	else
	return (false);
}
</script>
<script type="text/javascript">
    var minpwlength = 4;
    var fairpwlength = 7;
    
    var STRENGTH_SHORT = 0;  // less than minpwlength 
    var STRENGTH_WEAK = 1;  // less than fairpwlength
    var STRENGTH_FAIR = 2;  // fairpwlength or over, no numbers
    var STRENGTH_STRONG = 3; // fairpwlength or over with at least one number
    
    img0 = new Image(); 
    img1 = new Image();
    img2 = new Image();
    img3 = new Image();
    
    img0.src = '../images/too_short.png';
    img1.src = '../images/fair.png';
    img2.src = '../images/medium.png';
    img3.src = '../images/strong.png';
    
    var strengthlevel = 0;
    
    var strengthimages = Array( img0.src, img1.src, img2.src, img3.src );
    
    function updatestrength( pw ) {
		var submit_button = document.getElementById('submit_pass');
        if( istoosmall( pw ) ) {
            strengthlevel = STRENGTH_SHORT;
			submit_button.disabled = true;
        }
        else if( !isfair( pw ) ) { 
            strengthlevel = STRENGTH_WEAK;
			submit_button.disabled = true;
        }    
        else if( hasnum( pw ) ) {
            strengthlevel = STRENGTH_STRONG;
			submit_button.disabled = false;
        }
        else {
            strengthlevel = STRENGTH_FAIR;
			submit_button.disabled = false;
        }
    
        document.getElementById( 'strength' ).src = strengthimages[ strengthlevel ];    
    }
    
    function isfair( pw ) {
        if( pw.length < fairpwlength ) {
            return false;
        }
        else { 
            return true;
        }
    }
    
    function istoosmall( pw ) {
        if( pw.length < minpwlength ) {
            return true;
        }
        else {
            return false
        }
    }
    
    function hasnum( pw ) {
        var hasnum = false
        for( var counter = 0; counter < pw.length; counter ++ ) {
            if( !isNaN( pw.charAt( counter ) ) ) {
                hasnum = true;
            }
        }
    
        return hasnum;
    }
</script>
<title>Change password</title></head>
<?php
if (isset($_POST['current_password']))
{
	echo '<body>
<script language="javascript">'.($msg ? "alert('$msg');\n" : "").'
window.close(); window.opener.location.reload(true);
</script>
</body>
</html>';
	die();
}
?>
<body>
  <table class="full-height">
    <tbody><tr>
    <td align="center" style="padding-top:5px"><fieldset style="background-color:#EFEFEF; border:1px solid #666666; width:485px;<?php echo (!stripos($_SERVER['HTTP_USER_AGENT'], "msie") ? " padding:0px" : "");?>">
    <legend style="color:#000000">Schimbă parola</legend>
	 <form id="form_change_pass" method="post" action="" style="margin:0; padding:0; white-space:normal; display:inline;" onsubmit="return formValidate(this);">
      <table width="100%" border="0" cellspacing="0" cellpadding="5">
        <colgroup>
          <col width="80" />
          <col />
        </colgroup>
        <tr>
          <td rowspan="3"><img src="../images/login.png" alt="" width="80" height="80" /></td>
          <td align="center" style="padding:0px"><img src="../images/too_short.png" id="strength" alt="" width="250" height="48" /></td>
        </tr>
        <tr>
          <td align="center" style="padding:0px"><hr style="border-width: 1px 0px 0px 0px; border-color: #CCCCCC; border-style: solid" /></td>
        </tr>
        <tr>
          <td>
            <table width="100%" border="0" cellspacing="0" cellpadding="1">
              <colgroup>
                <col width="130" />
                <col />
              </colgroup>
              <tr>
                <td align="right">Parola curentă:</td>
                <td><input name="current_password" type="password" class="inputcol" id="current_password" value="" size="35" /></td>
              </tr>
              <tr>
                <td align="right"></td>
                <td></td>
              </tr>
              <tr>
                <td align="right">Noua parolă:</td>
                <td><input name="new_password" type="password" class="inputcol" id="new_password" value="" size="35" onkeyup="updatestrength( this.value );" /></td>
              </tr>
              <tr>
                <td align="right"></td>
                <td></td>
              </tr>			  
              <tr>
                <td align="right">Repetaţi parola:</td>
                <td><input name="repeat_password" type="password" class="inputcol" id="repeat_password" value="" size="35" /></td>
              </tr>
            </table>
         </td>
        </tr>
        <tr>
          <td colspan="2" style="padding:0"><img src="config/images/spacer.gif" alt="" width="1" height="1" /></td>
        </tr>
        <tr>
          <td colspan="2" align="center" style="padding-bottom:15px">
            <input name="submit_pass" type="submit" id="submit_pass" value="   Schimbă parola   " class="button_win" disabled="disabled" /></td>
        </tr>
        <tr>
          <td colspan="2" style="background-color:#666666; color:#FFFFFF" align="left"><strong>Atenţie:</strong>  După validarea noii parole, sesiunea dumneavoastră va fi închisă automat, din motive de securitate. Parola trebuie să fie cel puţin de tărie medie.</td>
          </tr>
      </table>
	 </form>
    </fieldset>
      </td>
    </tr></tbody></table>
    <script type="text/javascript">document.getElementById('current_password').focus();</script>
</body>
</html>