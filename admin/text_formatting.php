<?php
include("config/check_login.php");

foreach ($_GET as $key => $value)
${$key} = (!get_magic_quotes_gpc() ? addslashes($value) : $value);

if($type == "email") {
	$sql = "SELECT `content` FROM `settings` WHERE `variable` = 'admin_contact' LIMIT 1";
	$email_contact = mysql_result(mysql_query($sql, db_c()), 0);
	$_title = "Compose email";
}
if($action == "edit") {
	switch ($type) {
		case 'message': {
			$_title = "Edit message";
			$sql = "SELECT * FROM `messages` WHERE `id` = '$target'";
			$data = mysql_fetch_assoc(mysql_query($sql, db_c()));
		} break;
		case 'newsletter': {
			$_title = "Edit newsletter";
			$sql = "SELECT * FROM `newsletter` WHERE `id` = '$target'";
			$data = mysql_fetch_assoc(mysql_query($sql, db_c()));			
		}
	}
}
elseif ($action == "save") {
	foreach ($_POST as $key => $value)
	${$key} = (!get_magic_quotes_gpc() ? addslashes($value) : $value);

	switch ($type) {
		case 'message': {
			$date = date("Y-m-d");
			if($target == "new")
			$sql = "INSERT INTO `messages` (`target`, `date`, `title`, `content`) VALUES ('$to', '$date', '$title', '$content')";
			else
			$sql = "UPDATE `messages` SET `target` = '$to', `date` = '$date', `title` = '$title', `content` = '$content' WHERE `id` = '$target'";

			mysql_query($sql, db_c()) or die(mysql_error());
		} break;
		case 'newsletter': {
			$date = date("Y-m-d");
			if($target == "new")
			$sql = "INSERT INTO `newsletter` (`date`, `title`, `content`, `sent`) VALUES ('$date', '$title', '$content', '0000-00-00')";
			else
			$sql = "UPDATE `newsletter` SET `date` = '$date', `title` = '$title', `content` = '$content', `sent` = '0000-00-00' WHERE `id` = '$target'";
			
			mysql_query($sql, db_c()) or die(mysql_error());
		} break;
	}
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
.formular {
	margin: 0px;
	padding: 0px;
	text-align: center;
	white-space: normal;
	display: inline;
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
<?php
if ($action == "save")
{
	echo '</head>
<body>
<script language="javascript">
window.close(); window.opener.location.reload(true);
</script>
</body>
</html>';
	die();
}
?>
<script type="text/javascript" src="../consultants_area/config/tinyfck/tiny_mce.js"></script>
<script type="text/javascript">
tinyMCE.init({
	mode : "textareas",
	theme : "advanced",
	plugins : "spellchecker,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,zoom,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,filemanager,imagemanager",
	theme_advanced_buttons1_add_before : "newdocument,separator",
	theme_advanced_buttons1_add : "fontselect,fontsizeselect",
	theme_advanced_buttons2_add : "separator,insertdate,inserttime,preview,separator,forecolor,backcolor",
	theme_advanced_buttons2_add_before: "cut,copy,paste,pastetext,pasteword,separator,search,replace,separator",
	theme_advanced_buttons3_add_before : "tablecontrols,separator",
	theme_advanced_buttons3_add : "emotions,iespell,media,advhr,separator,print,separator,ltr,rtl,separator,fullscreen",
	theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,spellchecker,cite,abbr,acronym,del,ins,|,visualchars,nonbreaking",
	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "left",
	theme_advanced_statusbar_location : "bottom",
	plugin_insertdate_dateFormat : "%m/%d/%Y",
	plugin_insertdate_timeFormat : "%H:%M:%S",
	extended_valid_elements : "hr[class|width|size|noshade]",
	file_browser_callback : "fileBrowserCallBack",
	content_css : "editor_style.css",
	paste_use_dialog : false,
	theme_advanced_resizing : true,
	theme_advanced_resize_horizontal : false,
	theme_advanced_link_targets : "_something=My somthing;_something2=My somthing2;_something3=My somthing3;",
	apply_source_formatting : true
});

function fileBrowserCallBack(field_name, url, type, win) {
	var connector = "../../filemanager/browser.html?Connector=connectors/php/connector.php";
	var enableAutoTypeSelection = true;

	var cType;
	tinyfck_field = field_name;
	tinyfck = win;

	switch (type) {
		case "image":
		cType = "Image";
		break;
		case "flash":
		cType = "Flash";
		break;
		case "file":
		cType = "File";
		break;
	}

	if (enableAutoTypeSelection && cType) {
		connector += "&Type=" + cType;
	}

	window.open(connector, "tinyfck", "modal,width=600,height=400");
}

function addRemove(from) {
	var checkbox = document.getElementById('bcc');
	var to = document.getElementById('to');
	var restore = document.getElementById('restore');

	if(from == "span") {
		if(checkbox.checked) {
			to.value = restore.value;
			checkbox.checked = false;
		}
		else {
			//add address
			restore.value = to.value;
			to.value += " Administrator <<?php echo $email_contact;?>>;";
			checkbox.checked = true;
		}
	}
	else {
		if(checkbox.checked) {
			//add address
			restore.value = to.value;
			to.value += " Administrator <<?php echo $email_contact;?>>;";
		}
		else
		to.value = restore.value;
	}
	document.getElementById('subject').focus();
}
</script>
<!-- /TinyMCE -->
<title><?php echo ($_title ? $_title : "New $type");?></title></head>

<body>
  <table class="full-height">
    <tbody><tr>
      <td align="center" style="padding-top:5px"><form action="<?php echo ($type == "email" ? "../send_email.php" : "text_formatting.php?action=save&type=$type&target=".($target ? $target : "new"));?>" method="post" class="formular" id="form1">
        <fieldset style="background-color:#EFEFEF; border:1px solid #666666; <?php echo (!stripos($_SERVER['HTTP_USER_AGENT'], "msie") ? "width:600px; padding:0px" : "width:650px;");?>">
        <legend style="color:#000000"><img src="images/icons/edit_rtf.png" alt="" class="valignMiddle" /> <strong><?php echo ($_title ? $_title : "New $type");?></strong></legend>
        <br />
        <table width="100%" border="0" cellspacing="0" cellpadding="3">
        <?php if($type == "email") { ?>
          <tr>
            <td align="left" style="padding-left:15px; padding-right:15px"><table width="100%" cellpadding="0" cellspacing="0">
              <tr>
                <td>To: </td>
                <td><input name="to" type="text" class="inputcol" id="to" size="70" value="<?php echo urldecode($name)." <".urldecode($email).">;";?>" /><input type="hidden" value="" id="restore" name="restore" /></td>
                <td align="right"><table width="100%" cellpadding="0" cellspacing="0"><tr><td valign="middle"><input type="checkbox" name="bcc" id="bcc" class="inputcol" onclick="addRemove('check');" /></td><td valign="middle"><span onclick="addRemove('span')" style="cursor:default">Send&nbsp;me&nbsp;a&nbsp;copy</span></td></tr></table></td>
              </tr>
              <tr>
                <td colspan="3" style="padding-top:3px"></td>
              </tr>
              <tr>
                <td style="padding-right:4px">Subject: </td>
                <td colspan="2"><input name="subject" type="text" class="inputcol" id="subject" size="89" /></td>
              </tr>
              <tr>
                <td colspan="3">&nbsp;</td>
              </tr>              
            </table></td>
          </tr>
        <?php } elseif($type == "message") { ?> 
          <tr>
            <td align="center">To: <select name="to" id="to" class="inputcol"><option value="judges"<?php echo ($data['target'] == "judges" ? " selected" : "");?>>Judges</option><option value="consultants"<?php echo ($data['target'] && ($data['target'] != "judges") ? " selected" : "");?>>Consultants</option></select> &nbsp;&nbsp;&nbsp;&nbsp; Title: <input name="title" type="text" class="inputcol" id="title" size="60" value="<?php echo $data['title'];?>" /></td>
          </tr>
        <?php } else { ?>
            <tr>
            <td align="center">Title: <input name="title" type="text" class="inputcol" id="title" size="70" value="<?php echo $data['title'];?>" /></td>
          </tr>      
        <?php } ?>
          <tr>
            <td align="center"><textarea name="content" style="width:100%"><?php echo $data['content'];?></textarea></td>
          </tr>
          <tr>
            <td align="center" style="background-color: #888888"><table width="300" border="0" cellspacing="0" cellpadding="3">
              <colgroup>
                <col width="50%" />
                <col />
              </colgroup>
              <tr>
                <td align="center"><input name="Submit" type="submit" class="inputcol" value="     <?php echo ($type == "email" ? "Send" : "Save");?>     " /></td>
                <td align="center"><input name="butt" type="button" class="inputcol" value="    Cancel    " onclick="window.close();" /></td>
              </tr>
            </table></td>
          </tr>
        </table>
        </fieldset>
      </form>      </td>
    </tr></tbody></table>
    <script type="text/javascript">document.getElementById('<?php echo ($type == "email" ? "subject" : "title");?>').focus();</script>
</body>
</html>