<?php
class AjaxActionProduse
{
    private $_user;
    private $_solr;

    function __construct ($details, User $user)
    {
        $method = $details['sectiune'] . "_" . $details['actiune'];
        if (method_exists($this, $method)) {
            $this->_user = $user;
            $this->_solr = new MyShop_Solr();
            $this->$method($details);
        } else {
            throw new Exception('Metoda asociata nu este definita (' + $method + ')!');
        }
    }
    
    public function produse_getCategories ()
    {
        $sql = "SELECT `id`, `parent_id`, `denumire`, (SELECT COUNT(*) FROM `categorii` AS c2 WHERE c2.parent_id = c1.id) AS `hasChilds`, (SELECT COUNT(*) FROM `produse` WHERE `categorie_id` = c1.id) AS `nrProduse` FROM `categorii` AS c1 WHERE 1";
        $categorii = mysql_query($sql, db_c());
        
        echo '<script type="text/javascript">' . "\n";
        echo "\td = new dTree('d'); d.config.target = '';\n";
        echo "\td.add(0, -1, 'MyShop - by Rareş Vlăsceanu');\n";
        
        $i = 0;
        while ($row = mysql_fetch_assoc($categorii)) {
            echo "\td.add({$row['id']}, " . intval($row['parent_id']) . ", '" . addslashes($row['denumire']) . ($row['nrProduse'] ? " ({$row['nrProduse']})" : '') . "'" . (!$row['hasChilds'] ? ",'javascript:getProduse({$row['id']})'" : '') . ");\n";
            $i ++;
        }
        
        echo "\t$('tree').innerHTML = d.toString();\n";
        echo "</script>";
    }
    
    private function _listing ($totalRezultate, $rezultatePePagina, $pagina, $handler)
    {
        $buffer = '';
        $total = ceil($totalRezultate / $rezultatePePagina);
        
        if($total < 2) {
            return null;
        }
        
        $next = ($pagina < $total ? true : false);
        $prev = ($pagina > 1 ? true : false);
        $pages = pageListing($total, 3, $pagina);
        
        $buffer .= ($prev ? '<a href="javascript:' . $handler . '(' . ($pagina - 1) . ')" class="left" title="Pagina precedenta">&laquo;&lsaquo;</a>' : '<span class="left inactive">&laquo;&lsaquo;</span>');
      	if(!in_array(1, $pages)) {
      	    //prima pagina
      		$buffer .= '<a href="javascript:' . $handler . '(1)" title="Salt la pagina 1">1</a>';
      		if($pages[0] > 2)
      		    $buffer .= "&#8230;&nbsp;";
      	}
      	foreach ($pages as $key => $i) {
      	    $buffer .= ($i != $pagina ? '<a href="javascript:' . $handler . '(' . $i . ')" title="Salt la pagina ' . $i . '">' . $i . '</a>' : '<span class="current">' . $i . '</span>');
      	}
        if(!in_array($total, $pages)) {
            //ultima pagina
            if(($total - $pages[sizeof($pages) - 1] - 1) > 1)
      		    $buffer .= "&#8230;&nbsp;";
      		$buffer .= '<a href="javascript:' . $handler . '(' . $total . ')" title="Salt la pagina ' . $total . '">' . $total . '</a>';
      	}
      	$buffer .= ($next ? '<a href="javascript:' . $handler . '(' . ($pagina + 1) . ')" class="right" title="Pagina urmatoare">&rsaquo;&raquo;</a>' : '<span class="right inactive">&rsaquo;&raquo;</span>');
        
        return '<div id="pagination">' . $buffer . '<div>';
    }
    
    public function produse_getProduse ()
    {
        global $browser;
        $rezultatepepagina = 15;
        $pagina = isset($_GET['pagina']) ? $_GET['pagina'] : 1;
        $dela = ($pagina - 1) * $rezultatepepagina;
        
        $sql = "SELECT `denumire` FROM `categorii` WHERE `id` = {$_GET['categorieId']}";
        $categorie = mysql_result(mysql_query($sql, db_c()), 0);
        $sql = "SELECT COUNT(*) FROM `produse` WHERE `categorie_id` = {$_GET['categorieId']}";
        $totalRezultate = mysql_result(mysql_query($sql, db_c()), 0);
        
        $sql = "SELECT * FROM `produse` WHERE `categorie_id` = {$_GET['categorieId']} LIMIT $dela, $rezultatepepagina";
        $produse = mysql_query($sql, db_c());
        
        $buffer = '';
        $buffer .= '<fieldset style="' . ($browser == "IE" ? 'padding:7px;' : '') . '">' . "\n";
        $buffer .= '  <legend>' . $categorie . '</legend>' . "\n";
        $buffer .= '  <div style="margin-top:10px">' . "\n";
        if($this->_user->hasRight('produse', 'insert')) {
            $buffer .= '    <a href="javascript:addProdus()" class="blueButton fLeft"><img src="images/icons/add_mic.png" alt="" width="16" height="16" /> Adaugă</a>' . "\n";
        }
        if($this->_user->hasRight('produse', 'delete')) {
            $buffer .= '    <a href="javascript:stergeProdus()" class="blueButton fRight"><img src="images/icons/delete.png" alt="" width="16" height="16" /> Şterge</a>' . "\n";
        }
        if($this->_user->hasRight('produse', 'edit')) {
            $buffer .= '    <a href="javascript:editProdus()" class="blueButton fLeft' . ($this->_user->hasRight('produse', 'insert') ? ' marginLeft' : '') .'"><img src="images/icons/edit.png" alt="" width="16" height="16" /> Editează</a>' . "\n";
        }
        $buffer .= '  </div>' . "\n";
        $buffer .= '  <div class="clear"></div>' . "\n";
        $buffer .= '  <table width="100%" cellpadding="3" cellspacing="0" class="table_border clear" id="products">' . "\n";
        $buffer .= '  <colgroup><col width="30" /> <col /> <col width="10%" /> <col width="6%" /> <col width="6%" /> <col width="6%" /> <col width="6%" /> <col width="6%" /> </colgroup>' . "\n";
        $buffer .= '  <thead>' . "\n";
        $buffer .= '    <tr>' . "\n";
        $buffer .= '      <td rowspan="2" class="table_header" align="center">#</td>' . "\n";
        $buffer .= '      <td rowspan="2" class="table_header" align="center">Denumire produs</td>' . "\n";
        $buffer .= '      <td rowspan="2" class="table_header" align="right" style="padding-right:10px">Pret</td>' . "\n";
        $buffer .= '      <td colspan="2" class="table_header" align="center" style="border-bottom:1px solid #888888">Stocuri</td>' . "\n";
        $buffer .= '      <td colspan="3" class="table_header" align="center" style="border-bottom:1px solid #888888">Flags</td>' . "\n";
        $buffer .= '    </tr>' . "\n";
        $buffer .= '    <tr>' . "\n";
        $buffer .= '      <td class="table_header" align="center">Disp.</td>' . "\n";
        $buffer .= '      <td class="table_header" align="center">Rez.</td>' . "\n";
        $buffer .= '      <td class="table_header" style="border-left:1px solid #888888" align="center">Afis.</td>' . "\n";
        $buffer .= '      <td class="table_header" align="center">Nou</td>' . "\n";
        $buffer .= '      <td class="table_header" align="center">Rec.</td>' . "\n";
        $buffer .= '    </tr>' . "\n";
        $buffer .= '  </thead>' . "\n";
        $buffer .= '  <tbody>' . "\n";
        
        if(mysql_num_rows($produse)) {
            $i = 0;
            while ($row = mysql_fetch_assoc($produse)) {
                $buffer .= "    <tr id=\"row_{$row['id']}\" onmouseover=\"showHide({$row['id']}, 'on')\" onmouseout=\"showHide({$row['id']}, 'off')\" style=\"height:20px\">\n";
                $buffer .= '      <td align="center"><span id="span_' . $row['id'] . '">' . ($i+$dela+1) . '.</span><input type="checkbox" name="produse[]" id="check_' . $row['id'] . '" value="' . $row['id'] . '" onclick="selectDeselect(this, ' . $row['id'] . ');" style="display:none" /></td>' . "\n";
                $buffer .= "      <td>" . stripslashes($row['denumire']) . "<br /><span class=\"subtitle\">{$row['marca']} {$row['cod_produs']}</span></td>\n";
                $buffer .= "      <td align=\"right\" style=\"padding-right:10px\">" . number_format($row['pret'], 2, ",", ".") . "</td>\n";
                $buffer .= "      <td align=\"center\">{$row['stoc_disponibil']}</td>\n";
                $buffer .= "      <td align=\"center\">{$row['stoc_rezervat']}</td>\n";
                $buffer .= "      <td align=\"center\">" . ($row['afisat'] ? '<img src="images/icons/ok.png" width="16" height="16" alt="" />' : '<img src="images/icons/lipsa.png" width="16" height="16" alt="" />') . "</td>\n";
                $buffer .= "      <td align=\"center\">" . ($row['noutati'] ? '<img src="images/icons/ok.png" width="16" height="16" alt="" />' : '<img src="images/icons/lipsa.png" width="16" height="16" alt="" />') . "</td>\n";
                $buffer .= "      <td align=\"center\">" . ($row['recomandari'] ? '<img src="images/icons/ok.png" width="16" height="16" alt="" />' : '<img src="images/icons/lipsa.png" width="16" height="16" alt="" />') . "</td>\n";
                $i++;
            }
        }
        else {
            $buffer .= "<tr><td colspan=\"8\"><div style=\"text-align:center; padding:10px;\"><img src=\"images/icons/info.png\" width=\"25\" height=\"25\" class=\"valignMiddle\" /> Niciun produs în această categorie.</div></td></tr>";
        }
        $buffer .= '  </tbody>' . "\n";
        $buffer .= '</table>' . "\n";
        $buffer .= $this->_listing($totalRezultate, $rezultatepepagina, $pagina, 'goTo') . "\n";
        $buffer .= '</fieldset>';
        
        echo $buffer;
    }
    
    public function produse_stergeProduse() {
        $ids = json_decode(stripslashes($_POST['ids']), true);
        $sql = "DELETE FROM `produse` WHERE `id` IN (" . implode(', ', $ids) . ")";
        mysql_query($sql, db_c());

        $this->_solr->deleteByQuery('id: ' . implode(' OR id:', $ids));
        $this->_solr->commit(true);
        $this->produse_getProduse();
    }
    
    public function produse_getCaracteristici() {
        if(!empty($_GET['firstRun'])) {
            $_SESSION['_caracteristici'] = array();
            if($_GET['produsId']) {
                $sql = "SELECT pc.id, pc.id_caracteristica, s.sectiune, c.caracteristica, REPLACE(pc.valoare, '|', '<br />') AS `valoare` FROM `caracteristici` AS `c` LEFT JOIN `produse_caracteristici` AS `pc` ON c.id = pc.id_caracteristica LEFT JOIN `sectiuni_caracteristici` AS `s` ON s.id = c.sectiune_id WHERE pc.id_produs = {$_GET['produsId']} ORDER BY s.ordine ASC";
                $result = mysql_query($sql, db_c());
                
                while ($row = mysql_fetch_assoc($result)) {
                    $_SESSION['_caracteristici'][] = array('source' => 'db', 'sectiune' => $row['sectiune'], 'caracteristica' => array('id' => $row['id_caracteristica'], 'name' => $row['caracteristica']), 'valoare' => $row['valoare']);
                }
            }
        }
        
        if(!sizeof($_SESSION['_caracteristici'])) {
            echo '<div class="loader"><img src="images/icons/info.png" width="25" height="25" class="valignMiddle" /> Nicio caracteristica definita.</div>';
        }
        else {
            $i = 0;
            $buffer = '<table width="100%" cellpadding="2" cellspacing="0"><colgroup><col width="30" /><col width="100" /><col width="40%" /><col /></colgroup>';
            foreach ($_SESSION['_caracteristici'] as $c) {
                $buffer .= "<tr id=\"row_caracteristici_{$i}\" onmouseover=\"showHide('caracteristici', {$i}, 'on')\" onmouseout=\"showHide('caracteristici', {$i}, 'off')\" style=\"height:20px\">";
                $buffer .= "<td align=\"center\"><span id=\"span_caracteristici_{$i}\" style=\"display: block; padding:3.5px 0px;\">" . ($i+1) . ".</span><input type=\"checkbox\" name=\"caracteristici[]\" id=\"check_caracteristici_{$i}\" value=\"{$c['caracteristica']['id']}\" style=\"display: none\" onclick=\"selectDeselect(this, 'caracteristici', {$i});\" /></td>";
                $buffer .= "<td align=\"left\">{$c['sectiune']}</td><td align=\"left\">{$c['caracteristica']['name']}</td><td align=\"left\">" . ($c['source'] == 'new' || !empty($c['modified']) ? str_replace('|', '<br />', $c['valoare']) : $c['valoare']) . "</td></tr>";
                $i++;
            }
            $buffer .= "</table>";
            
            echo $buffer;
        }
    }
    
    public function produse_adaugaCaracteristica() {
        $data = json_decode(stripslashes(urldecode($_POST['data'])), true);
        $_SESSION['_caracteristici'][] = array('source' => 'new', 'sectiune' => $data['sectiune'], 'caracteristica' => array('id' => $data['caracteristica']['id'], 'name' => $data['caracteristica']['name']), 'valoare' => str_replace(array("\r\n", "\r", "\n"), '|', $data['valoare']));
        
        $this->produse_getCaracteristici();
    }
    
    public function produse_editeazaCaracteristica() {
        $valoare = stripslashes(urldecode($_POST['valoare']));
        $key = $_POST['caracteristicaId'];
        $_SESSION['_caracteristici'][$key]['valoare'] = str_replace(array("\r\n", "\r", "\n"), '|', $valoare);
        
        if($_SESSION['_caracteristici'][$key]['source'] == 'db') {
            $_SESSION['_caracteristici'][$key]['modified'] = true;
        }
        
        $this->produse_getCaracteristici();
    }
        
    public function produse_stergeCaracteristici() {
        $deleted = array();
        $selected = json_decode(stripslashes(urldecode($_POST['selected'])), true);
        foreach($_SESSION['_caracteristici'] as $key => $c) {
            if(in_array($key, $selected)) {
                unset($_SESSION['_caracteristici'][$key]);
            }
        }
        $this->produse_getCaracteristici();
    }
    
    public function produse_filterSectiuni() {
        $sectiuni = array();
        $filter = array();
        foreach($_SESSION['_caracteristici'] as $c) {
            $filter[] = $c['caracteristica']['id'];
        }
        $sql = "SELECT DISTINCT(s.id), s.sectiune AS `text` FROM caracteristici AS `c` LEFT JOIN `sectiuni_caracteristici` AS `s` ON c.sectiune_id = s.id WHERE c.categorie_id = {$_GET['categorieId']}" . (sizeof($filter) ? " AND c.id NOT IN (" . implode(', ', $filter) . ")" : '') . " ORDER BY s.ordine ASC";
        $result = mysql_query($sql, db_c());
        
        while($row = mysql_fetch_assoc($result)) {
            $sectiuni[] = $row;
        }
        array_unshift($sectiuni, array('id' => 0, 'text' => 'selectaţi...'));
        echo json_encode($sectiuni);
    }
    
    public function produse_filterCaracteristici() {
        $caracteristici = array();
        $filter = array();
        foreach($_SESSION['_caracteristici'] as $c) {
            $filter[] = $c['caracteristica']['id'];
        }
        $sql = "SELECT `id`, `caracteristica` AS `text` FROM `caracteristici` WHERE `categorie_id` = {$_GET['categorieId']} AND `sectiune_id` = {$_GET['sectiuneId']}" . (sizeof($filter) ? " AND `id` NOT IN (" . implode(', ', $filter) . ")" : '');
        $result = mysql_query($sql, db_c());

        while($row = mysql_fetch_assoc($result)) {
            $caracteristici[] = $row;
        }
        array_unshift($caracteristici, array('id' => 0, 'text' => 'selectaţi...'));
        echo json_encode($caracteristici);
    }
    
    public function produse_getImagini() {
        if(!empty($_GET['firstRun'])) {
            if(isset($_SESSION['_uploadImagini'])) {
                $_SESSION['_imagini'][] = $_SESSION['_uploadImagini'];
            }
            if(isset($_SESSION['_imagini'])) {
                foreach ($_SESSION['_imagini'] as $img) {
                    if($img['source'] == 'new') {
                        @unlink('../public/imagini/produse/' . $img['foto']);
                        @unlink('../public/thumbs/50/' . $img['foto']);
                        @unlink('../public/thumbs/100/' . $img['foto']);
                        @unlink('../public/thumbs/200/' . $img['foto']);
                    }
                }
            }
            $_SESSION['_imagini'] = array();
            if($_GET['produsId']) {
                $sql = "SELECT `id`, `foto`, `main` FROM `galerie_foto` WHERE `produs_id` = {$_GET['produsId']}";
                $result = mysql_query($sql, db_c());
                
                while ($row = mysql_fetch_assoc($result)) {
                    $_SESSION['_imagini'][] = array('source' => 'db', 'id' => $row['id'], 'foto' => $row['foto'], 'main' => (bool) intval($row['main']));
                }
            }
        }
        
        if(!sizeof($_SESSION['_imagini'])) {
            echo '<div class="loader"><img src="images/icons/info.png" width="25" height="25" class="valignMiddle" /> Nicio imagine incarcata.</div>';
        }
        else {
            $i = 0;
            $buffer = '<table width="100%" cellpadding="2" cellspacing="0"><colgroup><col width="25" /><col /><col width="50" /></colgroup>';
            foreach ($_SESSION['_imagini'] as $img) {
                $_thumb = verificaThumb('../public/imagini/produse/', $img['foto'], 50);
                
                $buffer .= "<tr id=\"row_imagini_{$i}\" onmouseover=\"showHide('imagini', {$i}, 'on')\" onmouseout=\"showHide('imagini', {$i}, 'off')\">";
                $buffer .= "<td align=\"center\"><span id=\"span_imagini_{$i}\" style=\"display: block; padding:3.25px 0px;\">" . ($i+1) . ".</span><input type=\"checkbox\" name=\"imagini[]\" id=\"check_imagini_{$i}\" value=\"{$i}\" style=\"display: none\" onclick=\"selectDeselect(this, 'imagini', {$i});\" /></td>";
                $buffer .= "<td align=\"left\"><img src=\"{$_thumb['thumb']}\" {$_thumb['details'][3]} class=\"valignMiddle\" id=\"img_{$i}\" style=\"border: 1px solid #FFFFFF\" /> {$img['foto']}</td><td align=\"center\">" . (!empty($img['main']) ? '<img src="images/icons/ok.png" width="16" height="16" alt="" />' : '&nbsp;') . "</td></tr>";
                
                $i++;
            }
            $buffer .= "</table>";
            
            echo $buffer;
        }
    }
    
    public function produse_uploadImagine() {
        if (($imagine = $_FILES['imagine'])) {
            if(isset($_SESSION['_uploadImagini'])) {
                //delete image already uploaded
                @unlink('../public/imagini/produse/' . $_SESSION['_uploadImagini']['foto']);
                @unlink('../public/thumbs/small/' . $_SESSION['_uploadImagini']['foto']);
                @unlink('../public/thumbs/medium/' . $_SESSION['_uploadImagini']['foto']);
                @unlink('../public/thumbs/big/' . $_SESSION['_uploadImagini']['foto']);
            }
            
            $newImage = 'tmp' . (sizeof($_SESSION['_imagini'])) . "-{$imagine['name']}";
            $file = "../public/imagini/produse/{$newImage}";
            if($_POST['source'] == 'new') {
                $_SESSION['_uploadImagini'] = array('foto' => $newImage, 'source' => 'new');
            }
            else {
                $_SESSION['_uploadImagini'] = array('foto' => $newImage, 'source' => 'modified');
            }
                
            move_uploaded_file($imagine['tmp_name'], $file);
            chmod($file, 0777);

            verificaThumb('../public/imagini/produse/', $newImage, 100);
            $_thumb = verificaThumb('../public/imagini/produse/', $newImage, 200);
            echo "<html><head><script type=\"text/javascript\">parent.showImage('" . $_thumb['thumb'] . "')</script></head><body></body></html>";
        }
    }
    
    public function produse_salveazaImaginea() {
        if(isset($_SESSION['_uploadImagini'])) {
            if(!isset($_GET['imagineId'])) {
                $_SESSION['_imagini'][] = $_SESSION['_uploadImagini'];
            }
            else {
                $id = $_GET['imagineId'];
                $_SESSION['_imagini'][$id]['old'] = $_SESSION['_imagini'][$id]['foto'];
                $_SESSION['_imagini'][$id]['foto'] = $_SESSION['_uploadImagini']['foto'];
                $_SESSION['_imagini'][$id]['modified'] = true;
            }
            unset($_SESSION['_uploadImagini']);
            
            $this->produse_getImagini();
        }
    }
    
    public function produse_stergeImagini() {
        $ids = json_decode(stripslashes($_POST['ids']), true);
        foreach ($ids as $id) {
            unset($_SESSION['_imagini'][$id]);
        }
        $this->produse_getImagini();
    }
    
    public function produse_setDefaultImage() {
        foreach ($_SESSION['_imagini'] as $key => $img) {
            if(!empty($img['main'])) {
                $_SESSION['_imagini'][$key]['main'] = false;
                $_SESSION['_imagini'][$key]['modified'] = true;
            }
        }
        $_SESSION['_imagini'][$_GET['id']]['main'] = true;
        $_SESSION['_imagini'][$_GET['id']]['modified'] = true;
        
        $this->produse_getImagini();
    }
}
?>