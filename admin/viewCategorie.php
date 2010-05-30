<?php
require 'config/check_login.php';

function printSubcategories($id, $level = 0) {
    if(!$id) {
        return;
    }
    else {
        $sql = "SELECT * FROM `categorii` WHERE `parent_id`" . ($id == -1 ? ' IS NULL' : " = '{$id}'");
        $result = mysql_query($sql, db_c());

        $buffer = null;
        if(mysql_num_rows($result)) {
            $level++;
            $buffer .= "<ul>\n";
            while($row = @mysql_fetch_assoc($result)) {
                $buffer .= str_repeat("\t", $level - 1) . "  <li>{$row['denumire']}";
                $buffer .= printSubcategories($row['id'], $level);
                $buffer .= "  </li>\n";
            }
            $buffer .= str_repeat("\t", $level - 1) . "</ul>\n";
        }
        return $buffer;
    }
}

if(!empty($_GET['base'])) {
    $title = "MyShop - by Rareş Vlăsceanu";
    
    $sql = "SELECT COUNT(*) FROM `categorii` WHERE 1";
    $totalCategorii = mysql_result(mysql_query($sql, db_c()), 0);
    
    $sql = "SELECT COUNT(*) FROM `produse` WHERE 1";
    $totalProduse = mysql_result(mysql_query($sql, db_c()), 0);
}
else {
    $sql = "SELECT * FROM `categorii` WHERE `id` = '{$_GET['categorieId']}'";
    $data = mysql_fetch_assoc(mysql_query($sql, db_c()));
    $title = $data['denumire'];
    
    if($data['icon']) {
        $details = getimagesize("../public/imagini/categorii/{$data['icon']}");
    }
}

/* Determinare categorii 'frunza' */
$sql = "SELECT GROUP_CONCAT(`id` SEPARATOR ',') FROM `categorii` WHERE `id` NOT IN (SELECT DISTINCT(`parent_id`) FROM `categorii` WHERE `parent_id` IS NOT NULL)";
$categoriiFrunza = explode(',', mysql_result(mysql_query($sql, db_c()), 0));
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $title;?></title>
<link href="css/style.css" rel="stylesheet" type="text/css" />
<style type="text/css">
body { margin: 0px 5px; }
#myFrame {
	font-family:Tahoma;
	border: 1px solid #777777;
}
#myFrame #container {
	width: 100%;
	color:#CCCCCC;
	border: 1px solid #CCCCCC;
	margin-top: 0px;
}
#myFrame div > a {
	display:block;
	padding:3px;
	text-align:center;
}
#myFrame #myButtons { border-bottom:1px solid #FFFFFF;}
#myFrame input[type=text] { width: 98%; } 
#myFrame a:hover { text-decoration:none;}
#myFrame a.disabled:hover { color: #999999; }
#myFrame legend { color:#666666; font-size:12px;}
</style>
<script type="text/javascript" src="scripts/popwin.js"></script>
<script type="text/javascript" src="scripts/prototype.js"></script>
<script type="text/javascript" src="scripts/userInterface.js"></script>
<script type="text/javascript" src="scripts/viewCategorie.js"></script>
<script type="text/javascript">
Event.observe(window, 'load', function() {
	var myUI = new UI('myButtons');
	<?php
	if($user->hasRight('categorii', 'edit') && empty($_GET['base'])) {
	    echo "myUI.addButton({id: 'edit', name: 'Editează', icon: 'images/icons/edit.png', width: 80, link: editeaza}, 'left');\n";
	}
	if($user->hasRight('categorii', 'insert')) {
	    echo "myUI.addButton({id: 'add', name: 'Adaugă', icon: 'images/icons/add_mic.png', width: 80, link: adauga}, 'left');\n";
	}
	if(!empty($data) && $user->hasRight('categorii', 'edit') && in_array($data['id'], $categoriiFrunza)) {
	    echo "myUI.addButton({id: 'caracteristici', name: 'Caracteristici', icon: 'images/icons/specificatii.png', width: 100, link: caracteristici}, 'left');\n";
	}
	if($user->hasRight('categorii', 'delete') && empty($_GET['base'])) {
	    echo "myUI.addButton({id: 'delete', name: 'Şterge', icon: 'images/icons/delete.png', width: 80, link: sterge}, 'right');\n";
	}
	?>
	
	parent.$('viewCurrentCateg').style.height = (document.body.scrollHeight + 20) + 'px';
});
</script>
</head>

<body>
<fieldset id="myFrame" style="<?php echo ($browser == "IE" ? "padding: 7px" : "");?>">
  <legend>Detalii categorie</legend>
  <br />
  <form id="myForm" name="myForm" method="post" action="ajax.php?sectiune=categorii&actiune=upload&id=<?php if(!empty($data)) echo $data['id'];?>" target="_upload" enctype="multipart/form-data" onsubmit="return ($F('_icon') ? true : false)">
   <input type="hidden" id="_id" name="_id" value="<?php if(!empty($data)) echo $data['id'];?>" />
   <input type="hidden" id="_parentId" name="_parentId" value="<?php if(!empty($data)) echo $data['parent_id'];?>" />
   <input type="hidden" id="_context" name="_context" value="" />
   <div id="myButtons"></div>
   <div id="container" class="clear">
    <?php if(!empty($_GET['base'])) { ?>
    <table width="100%" border="0" cellspacing="0" cellpadding="5" class="clear" id="insert" style="display:none">
      <tr>
        <td colspan="2"></td>
      </tr>
      <tr>
        <td width="100">Denumire: </td>
        <td><input type="text" name="denumire" id="denumire" class="inputcol" /></td>
      </tr>
      <tr>
        <td colspan="2"></td>
      </tr>
    </table>
    <table width="100%" border="0" cellspacing="0" cellpadding="5" class="clear" id="stats">
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
            <li style="padding:3px"><img src="images/icons/<?php echo ($user->hasRight('categorii', 'edit') ? 'ok' : 'renunt')?>.png" width="16" height="16" alt="" /> &nbsp;Editare</li>
            <li style="padding:3px"><img src="images/icons/<?php echo ($user->hasRight('categorii', 'insert') ? 'ok' : 'renunt')?>.png" width="16" height="16" alt="" /> &nbsp;Adăugare</li>
            <li style="padding:3px"><img src="images/icons/<?php echo ($user->hasRight('categorii', 'delete') ? 'ok' : 'renunt')?>.png" width="16" height="16" alt="" /> &nbsp;Ştergere</li>
          </ul>
        </td>
      </tr>
     </table>
     <?php } else { ?>
     <table width="100%" border="0" cellspacing="0" cellpadding="5" class="clear">
      <tr>
        <td width="100">Denumire: </td>
        <td id="categorie"><strong style="line-height:19px;"><?php echo $data['denumire'];?></strong></td>
      </tr>
    </table>
    <div id="imageUploading" style="<?php echo (!$data['parent_id'] ? 'display:none' : '');?>">
      <table width="100%" border="0" cellspacing="0" cellpadding="5" style="border: 0px">
        <tr>
          <td colspan="2"><hr size="1" style="border-width: 1px 0px 0px 0px; border-style: solid; border-color: #CCCCCC;" /></td>
        </tr>
  	    <tr>
    	  <td width="100" valign="top">Imagine:</td>
    	  <td valign="middle"><img id="icon" src="http://<?php echo $config->DOMENIU_SITE; ?>/imagini/categorii/<?php echo $data['icon'];?>" width="<?php echo $details[0];?>" height="<?php echo $details[1];?>" class="fLeft" style="border: 1px solid #E5E5E5; padding:10px" /> <div class="fRight" id="changeImage" style="padding:left:50px"></div></td>
  		</tr>
  		<tr>
  		  <td colspan="2"><img src="images/spacer.gif" height="3" width="100%" /> </td>
  		</tr>
      </table>
    </div>
    <?php } ?>
   </div>
  </form>
  <iframe name="_upload" id="_upload" height="0" width="0" frameborder="0" style="display:none"></iframe>
  <input type="hidden" id="_backUp" name="_backUp" value="" />
</fieldset>
</body>
</html>
