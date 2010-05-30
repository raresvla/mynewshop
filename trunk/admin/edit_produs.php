<?php
include("config/check_login.php");

function deleteImage($src) {
    @unlink('../public/imagini/produse/' . $src);
    @unlink('../public/thumbs/50/' . $src);
    @unlink('../public/thumbs/100/' . $src);
    @unlink('../public/thumbs/200/' . $src);
}
function renameImage($old, $new) {
    @rename('../public/imagini/produse/' . $old, '../public/imagini/produse/' . $new);
    @rename('../public/thumbs/50/' . $old, '../public/thumbs/50/' . $new);
    @rename('../public/thumbs/100/' . $old, '../public/thumbs/100/' . $new);
    @rename('../public/thumbs/200/' . $old, '../public/thumbs/200/' . $new);
}

switch ($_GET['action']) {
    case 'edit': {
        $sql = "SELECT * FROM `produse` WHERE `id` = '{$_GET['id']}'";
        $data = mysql_fetch_assoc(mysql_query($sql, db_c()));        
    } break;
    
    case 'save': {
        //verifica valori trimise
        $toVerify = array("denumire", "cod_produs", "pret", "stoc_disponibil");
        if(notBlank($toVerify, "_POST", $message)) {
            $error = true;
            $data = $_POST;
        }
        else {
            foreach ($_POST as $key => $value) {
                if(strpos($key, 'descriere') === false) {
                    ${$key} = addslashes($value);
                }
                else {
                    ${$key} = addslashes(str_replace(array("\r\n", "\r", "\n"), "|", $value));
                }
            }
                        
            if ($_GET['source'] == 'db') {
                $sql = "UPDATE `produse` SET `marca` = '{$marca}', `cod_produs` = '{$cod_produs}', `denumire` = '{$denumire}', `descriere` = '{$descriere}', `pret` = '{$pret}', `greutate` = '{$greutate}', `stoc_disponibil` = {$stoc_disponibil}, `ultima_modificare` = NOW(), `afisat` = " . intval($afisat) . ", `noutati` = " . intval($noutati) . ", `recomandari` = " . intval($recomandari) . " WHERE `id` = {$_GET['id']}";
                mysql_query($sql, db_c()) or die(mysql_error());
                $id = $_GET['id'];
            }
            else {
                $sql = "INSERT INTO `produse` (`categorie_id`, `marca`, `cod_produs`, `denumire`, `descriere`, `pret`, `greutate`, `stoc_disponibil`, `data_adaugare`, `afisat`, `noutati`, `recomandari`) VALUES ('{$_categorieId}', '{$marca}', '$cod_produs', '{$denumire}', '{$descriere}', '$pret', '$greutate', '$stoc_disponibil', DATE(NOW()), " . intval($afisat) . ", " . intval($noutati) . ", " . intval($recomandari) . ")";
                mysql_query($sql, db_c()) or die(mysql_error());
                $id = mysql_insert_id();
            }

            /* update characteristics */
            $new = array();
            $modified = array();
            $doNotDelete = array();
            foreach ($_SESSION['_caracteristici'] as $key => $c) {
                if($c['source'] == 'new') {
                    $new[] = $c;
                    unset($_SESSION['_caracteristici'][$key]);
                }
                elseif($c['source'] == 'db' && $c['modified']) {
                    $modified[] = $c;
                    $doNotDelete[] = $c['caracteristica']['id'];
                    unset($_SESSION['_caracteristici'][$key]);
                }
            }
            //remove deleted characteristics
            if(sizeof($_SESSION['_caracteristici'])) {
                foreach ($_SESSION['_caracteristici'] as $c) {
                    $doNotDelete[] = $c['caracteristica']['id'];
                }
            }
            $sql = "DELETE FROM `produse_caracteristici` WHERE `id_produs` = {$id}" . (sizeof($doNotDelete) ? " AND `id_caracteristica` NOT IN (" . implode(', ', $doNotDelete) . ")" : '');
            mysql_query($sql, db_c()) or die(mysql_error());    
            //save changed characteristics
            if(sizeof($modified)) {
                foreach ($modified as $c) {
                    $sql = "UPDATE `produse_caracteristici` SET `valoare` = '" . addslashes($c['valoare']) . "' WHERE `id_caracteristica` = {$c['caracteristica']['id']} AND `id_produs` = {$id}";
                    mysql_query($sql, db_c()) or die(mysql_error());
                }
            }
            //add new characteristics
            $sql = "";
            if(sizeof($new)) {
                foreach ($new as $c) {
                    $sql .= ($sql ? ', ' : '') . "({$id}, {$c['caracteristica']['id']}, '" . addslashes($c['valoare']) . "')";
                }
                $sql = "INSERT INTO `produse_caracteristici` (`id_produs`, `id_caracteristica`, `valoare`) VALUES {$sql}";
                mysql_query($sql, db_c()) or die(mysql_error());
            }
            /* end */
            
            /* update pictures */
            $new = array();
            $modified = array();
            $doNotDelete = array();
            foreach ($_SESSION['_imagini'] as $key => $img) {
                if($img['source'] == 'new') {
                    $new[] = $img;
                    unset($_SESSION['_imagini'][$key]);
                }
                elseif($img['source'] == 'db' && $img['modified']) {
                    $modified[] = $img;
                    $doNotDelete[] = $img['id'];
                    unset($_SESSION['_imagini'][$key]);
                }
            }
            //remove deleted pictures
            if(sizeof($_SESSION['_imagini'])) {
                foreach ($_SESSION['_imagini'] as $img) {
                    $doNotDelete[] = $img['id'];
                }
            }
            $sql = "SELECT `foto` FROM `galerie_foto` WHERE `produs_id` = {$id}" . (sizeof($doNotDelete) ? "  AND `id` NOT IN (" . implode(', ', $doNotDelete) . ")" : '');
            $result = mysql_query($sql, db_c());
            while($row = mysql_fetch_assoc($result)) {
                deleteImage($row['foto']);
            }
            $sql = "DELETE FROM `galerie_foto` WHERE `produs_id` = {$id}" . (sizeof($doNotDelete) ? "  AND `id` NOT IN (" . implode(', ', $doNotDelete) . ")" : '');
            mysql_query($sql, db_c()) or die(mysql_error());

            //save changed pictures
            if(sizeof($modified)) {
                foreach ($modified as $img) {
                    if($img['old']) {
                        $newImg = substr($img['foto'], strpos($img['foto'], '-') + 1);
                        deleteImage($img['old']);
                        renameImage($img['foto'], $newImg);
                        
                        $sql = "UPDATE `galerie_foto` SET `foto` = '" . addslashes($newImg) . "', `main` = " . intval($img['main']) . " WHERE `id` = {$img['id']} AND `produs_id` = {$id}";
                        mysql_query($sql, db_c()) or die(mysql_error());
                    }
                    else {
                        $sql = "UPDATE `galerie_foto` SET `main` = " . intval($img['main']) . " WHERE `id` = {$img['id']} AND `produs_id` = {$id}";
                        mysql_query($sql, db_c()) or die(mysql_error());
                    }
                }
            }
            //add new pictures
            $sql = "";
            if(sizeof($new)) {
                foreach ($new as $img) {
                    $newImg = substr($img['foto'], strpos($img['foto'], '-') + 1);
                    renameImage($img['foto'], $newImg);
                    $sql .= ($sql ? ', ' : '') . "({$id}, '{$newImg}', '" . intval($img['main']) . "')";
                }
                $sql = "INSERT INTO `galerie_foto` (`produs_id`, `foto`, `main`) VALUES {$sql}";
                mysql_query($sql, db_c()) or die(mysql_error());
            }
            
            $done = true;
        }
    } break;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="css/win.css" />
<!--[if IE]>
  <link rel="stylesheet" type="text/css" href="css/win-ie.css" />
<![endif]-->
<link href="scripts/prototype_window/themes/default.css" rel="stylesheet" type="text/css" />
<link href="scripts/prototype_window/themes/alphacube.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="scripts/prototype.js"></script>
<script type="text/javascript" src="scripts/tinyfck/tiny_mce.js"></script>
<script type="text/javascript" src="scripts/edit_produs.js"></script>
<script type="text/javascript" src="scripts/windowManager.js"></script>
<script type="text/javascript" src="scripts/prototype_window/javascripts/window.js"></script>
<script type="text/javascript">
tinyMCE.init({
	mode : "exact",
	elements : "descriere",
	theme : "simple"
});
</script>
<style type="text/css">
#caracteristici {
	font-size:10px;
	border-top:0px;
	height: 160px;
	overflow-x: hidden;
	overflow-y: auto;
	background-color: #FFFFFF;
}
#caracteristici table tr td, #imagini table tr td {
	border-bottom:1px solid #EFEFEF;
}
#imagini {
	border-top:0px;
	height: 190px;
	overflow-x: hidden;
	overflow-y: auto;
	background-color: #FFFFFF;
}
</style>
<?php
if (!empty($done))
{
	echo '</head>
<body>
<script language="javascript">
window.close(); window.opener.goTo();
</script>
</body>
</html>';
	die();
}
?>
<title>MyShop Admin - Produse</title></head>

<body>
  <iframe name="_upload" id="_upload" height="0" width="0" frameborder="0" style=""></iframe>
  <table class="full-height">
    <tbody><tr>
      <td align="center" style="padding-top:5px"><form action="edit_produs.php?action=save&source=<?php echo ($_GET['action'] == "edit" ? "db&id={$_GET['id']}" : "new");?>" method="post" class="formular" id="edit">
        <fieldset id="box_container">
        <legend class="legend_box_container"><img src="images/icons/edit_big.png" alt="" width="25" height="25" style="vertical-align: middle;" /><strong><?php echo ($_GET['action'] == 'add' ? 'Adaugă produs' : 'Editează produs');?> </strong></legend>
        <div id="container">
          <div class="fLeft" style="width:482px">
            <fieldset class="box_content">
            <legend class="legend_box_content">Detalii produs </legend>
            <table width="100%" border="0" cellspacing="0" cellpadding="5">
              <colgroup>
                <col width="110" />
                <col />
                <col width="50" />
                <col width="120" />
              </colgroup>
              <tr>
                <td align="left">Denumire:</td>
                <td colspan="3" align="left">
                    <input name="denumire" type="text" class="inputcol" id="denumire" size="54" value="<?php if(!empty($data)) echo $data['denumire'];?>" />
                </td>
              </tr>
              <tr>
                <td align="left">Marca:</td>
                <td colspan="3" align="left"><input name="marca" type="text" class="inputcol" id="marca" size="54" value="<?php if(!empty($data)) echo $data['marca'];?>" /></td>
              </tr>
              <tr>
                <td align="left">Cod Produs: </td>
                <td align="left"><input name="cod_produs" type="text" class="inputcol" id="cod_produs" size="20" value="<?php if(!empty($data)) echo $data['cod_produs'];?>" /></td>
                <td align="left">Greutate:</td>
                <td align="left"><input name="greutate" type="text" class="inputcol right" id="greutate" size="10" value="<?php if(!empty($data)) echo $data['greutate'];?>" /> Kg</td>
              </tr>
              <tr>
                <td colspan="4" align="center" style="padding:0px;"><img src="images/spacer.gif" alt="" width="1" height="18" /><input type="hidden" id="_categorieId" name="_categorieId" value="<?php echo (isset($data['categorie_id']) ? $data['categorie_id'] : $_GET['categorieId']);?>" /><input type="hidden" id="_produsId" name="_produsId" value="<?php if(!empty($_GET['id'])) echo $_GET['id'];?>" /></td>
              </tr>
              <tr>
                <td colspan="4" align="center" style="padding:0px">Descriere: </td>
                </tr>
              <tr>
                <td colspan="4" align="center">
                    <textarea name="descriere" id="descriere" cols="52" rows="11"><?php if(!empty($data)) echo $data['descriere'];?></textarea></td>
                </tr>
            </table>
            </fieldset>
              <br />
            <fieldset class="box_content">
            <legend class="legend_box_content">Preţ, stoc </legend>
            <table width="100%" border="0" cellspacing="0" cellpadding="5">
              <colgroup>
                <col width="60" />
                <col width="100"/>
                <col width="60" />
                <col width="90" />
                <col width="20" />
                <col width="40" />
                <col />
              </colgroup>
              <tr>
                <td align="left"><label for="afisat">Afişat:</label></td>
                <td align="left"><?php if(!isset($data['afisat']) || $data['afisat']) { ?><input name="afisat" type="checkbox" id="afisat" value="1" checked="checked" /><?php } else { ?><input name="afisat" type="checkbox" id="afisat" value="1" /><?php } ?></td>
                <td align="left"><label for="noutati">Noutăţi:</label></td>
                <td align="left"><?php if(!empty($data) && $data['noutati']) { ?><input name="noutati" type="checkbox" id="noutati" value="1" checked="checked" /><?php } else { ?><input name="noutati" type="checkbox" id="noutati" value="1" /><?php } ?></td>
                <td align="left" colspan="2"><label for="recomandari">Recomandări:</label></td>
                <td align="left"><?php if(!empty($data) && $data['recomandari']) { ?><input name="recomandari" type="checkbox" id="recomandari" value="1" checked="checked" /><?php } else { ?><input name="recomandari" type="checkbox" id="recomandari" value="1" /><?php } ?></td>
              </tr>
              <tr>
                <td colspan="7" style="padding: 0px"><hr /></td>
              </tr>
              <tr>
                <td align="left">Pret:</td>
                <td colspan="2" align="left"><input name="pret" type="text" class="inputcol right" id="pret" size="15" value="<?php if(!empty($data)) echo $data['pret'];?>" /></td>
                <td colspan="2" align="right">Stoc:</td>
                <td colspan="2"  align="left"><input name="stoc_disponibil" type="text" class="inputcol right" id="stoc_disponibil" size="10" value="<?php if(!empty($data)) echo $data['stoc_disponibil'];?>" /></td>
              </tr>
            </table>
            </fieldset>
          </div>
          <div class="fRight" style="width:482px">
            <fieldset class="box_content">
            <legend class="legend_box_content">Caracteristici produs </legend>
			<div style="height:24px">
			  <a href="javascript:addCaracteristica()" class="blueButton fLeft"><img src="images/icons/add_mic.png" alt="" width="16" height="16" /> Adaugă</a>
			  <a href="javascript:stergeCaracteristici()" class="blueButton fRight"><img src="images/icons/delete.png" alt="" width="16" height="16" /> Şterge</a>
              <a href="javascript:editCaracteristica()" class="blueButton fRight marginRight"><img src="images/icons/edit.png" alt="" width="16" height="16" /> Editează</a>
			</div>
			<table width="100%" border="0" cellpadding="3" cellspacing="0" class="table_border clear">
			  <colgroup>
                <col width="25" />
                <col width="100" />
                <col width="40%" />
                <col />
			  </colgroup>
			  <tr>
			    <td class="table_header">#</td>
			    <td align="left" class="table_header">Secţiune</td>
				<td align="left" class="table_header">Caracteristica</td>
				<td align="left" class="table_header">Valoare</td>
			  </tr>
			</table>
            <div id="caracteristici" class="clear table_border"><div class="loader">Preluare informatii...<br /> <img src="images/loading.gif" width="226" height="19" /></div></div>
            </fieldset>
            <br />
			<fieldset class="box_content">
            <legend class="legend_box_content">Imagini </legend>
			<div style="height:24px">
			  <a href="javascript:addImagine()" class="blueButton fLeft"><img src="images/icons/add_mic.png" alt="" width="16" height="16" /> Adaugă</a>
			  <a href="javascript:stergeImagini()" class="blueButton fRight"><img src="images/icons/delete.png" alt="" width="16" height="16" /> Şterge</a>
			  <a href="javascript:setDefault()" class="blueButton fRight marginRight"><img src="images/icons/salveaza.png" alt="" width="16" height="16" /> Default</a>
              <a href="javascript:editImagine()" class="blueButton fRight marginRight"><img src="images/icons/edit.png" alt="" width="16" height="16" /> Editează</a>
			</div>
			<div class="clear"></div>
			<table width="100%" border="0" cellpadding="3" cellspacing="0" class="table_border">
			  <colgroup>
                <col width="25" />
                <col />
                <col width="100" />
			  </colgroup>
			  <tr>
			    <td class="table_header">#</td>
				<td class="table_header">Imagine</td>
				<td class="table_header">Default</td>
			  </tr>
			</table>
			<div id="imagini" class="clear table_border"><div class="loader">Preluare informatii...<br /> <img src="images/loading.gif" width="226" height="19" /></div></div>
            </fieldset>
          </div>
          <div class="clear"></div>
        </div>
        <table width="100%" border="0" cellspacing="0" cellpadding="3" style="margin-top:5px">
          <tr>
            <td align="center" style="background-color:#888888;"><table width="500" border="0" cellspacing="0" cellpadding="3">
              <colgroup>
                <col width="50%" />
                <col />
              </colgroup>
              <tr>
                <td align="center"><input name="Submit" type="submit" class="button_win" value="Salveaz&#259;" /></td>
                <td align="center"><input name="butt" type="button" class="button_win" value="Renun&#355;" onclick="window.close();" /></td>
              </tr>
            </table></td>
          </tr>
        </table>
        </fieldset>
      </form>
    </td>
  </tr>
 </tbody>
</table>
<?php if(!empty($error)) { $keys = array_keys($err); ?><script type="text/javascript">alert('<?php echo (str_replace("'", "\'", $message));?>'); document.forms['edit'].<?php echo $err[$keys[0]];?>.focus();</script><?php } ?>
<div class="hidden" id="_winCaracteristici">
  <img src="images/spacer.gif" height="20" width="100%" alt="" />
  <div class="marginBottom"><select name="sectiune" id="sectiune" class="fRight inputcol" style="width:339px" onchange="_filterCaracteristici(this)"></select><span class="fRight">Secţiune:&nbsp;</span></div>
  <div class="clear marginBottom"><select name="caracteristica" id="caracteristica" class="fRight inputcol" style="width:339px" disabled="disabled" onchange="_caracteristicaSelectata(this)"></select><span class="fRight">Caracteristica:&nbsp;</span></div>
  <div class="clear marginBottom" style="height:55px"><textarea name="valoare" id="valoare" class="fRight inputcol" rows="4" cols="51" disabled="disabled"></textarea><span class="fRight">Valoare:&nbsp;</span></div>
  <p class="center clear">
    <img src="images/spacer.gif" height="15" width="100%" alt="" />
    <input type="button" name="caracteristici_continua" id="caracteristici_continua" value="  Salvează  " class="button_win" /> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <input type="button" name="caracteristici_renunt" id="caracteristici_renunt" value="  Renunţ  " class="button_win" />
  </p>
</div>
<div class="hidden" id="_winImagini">
  <form action="ajax.php?sectiune=produse&actiune=uploadImagine" target="_upload" method="post" enctype="multipart/form-data">
    <img src="images/spacer.gif" height="20" width="100%" alt="" />
    <div style="height:18px;"><div class="marginBottom"><div class="fRight" style="width: 283px" id="imgContainer"><span style="color:#666666; font-style:italic">neîncărcat</span></div><span class="fRight">Imagine:&nbsp;</span></div></div>
    <img src="images/spacer.gif" height="5" width="100%" alt="" />
    <div class="clear marginBottom" style="height:25px"><input type="submit" name="upload" id="upload" value="Upload" class="inputcol fRight" style="width:60px; margin-left:10px" /><input type="file" name="imagine" id="imagine" class="inputcol fRight" size="25px" /><span class="fRight">Upload:&nbsp;</span></div>
    <p class="center clear" style="height:20px">
      <input type="button" name="imagini_continua" id="imagini_continua" value="  Salvează  " class="button_win" /> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      <input type="button" name="imagini_renunt" id="imagini_renunt" value="  Renunţ  " class="button_win" />
    </p>
    <input type="hidden" name="source" id="source" value="" />
  </form>
</div>
</body>
</html>