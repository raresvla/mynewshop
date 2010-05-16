<?php
class AjaxActionComenzi
{
    private  $_user;
    function __construct ($details, User $user)
    {
        $method = $details['sectiune'] . "_" . $details['actiune'];
        if (method_exists($this, $method)) {
            $this->_user = $user;
            $this->$method($details);
        } else {
            throw new Exception('Metoda asociata nu este definita (' + $method + ')!');
        }
    }
    
    public function comenzi_getComenzi()
    {
        $rezultatepepagina = 20;
        $pagina = isset($_POST['pagina']) ? $_POST['pagina'] : 1;
        $dela = ($pagina - 1) * $rezultatepepagina;
        $sorting = json_decode(stripslashes($_POST['sorting']), true);
        
        $sql = "SELECT COUNT(*) FROM `comenzi` WHERE 1";
        $totalRezultate = mysql_num_rows(mysql_query($sql, db_c()));
        
        $sql = "SELECT c.*, DATE_FORMAT(c.data, '%d/%m/%Y %H:%i:%s') AS `data`, c.total_fara_tva + c.total_tva AS `total_comanda`, CONCAT(m.nume, ' ', m.prenume) AS `nume_client` FROM `comenzi` AS `c` LEFT JOIN `membri` AS `m` ON c.membru_id = m.id ORDER BY `{$sorting['element']}` " . $sorting['direction'] . " LIMIT $dela, $rezultatepepagina";
        $comenzi = mysql_query($sql, db_c());
        
        $header = '<tr><td align="center" class="table_header">#</td>';
        $header .= '<td align="center" class="' . ($sorting['element'] == 'data' ? ($sorting['direction'] == 'asc' ? 'sortUp' : 'sortDown') : 'table_header') . '"><a href="javascript://" onclick="getComenzi(1, {element: \'data\', direction: \'' . ($sorting['element'] == 'data' ? ($sorting['direction'] == 'asc' ? 'desc' : 'asc') : 'desc') . '\'})">Data</a></td>';
        $header .= '<td align="center" class="table_header">Cod comanda</td>';
        $header .= '<td align="left" class="' . ($sorting['element'] == 'nume_client' ? ($sorting['direction'] == 'asc' ? 'sortUp' : 'sortDown') : 'table_header') . '"><a href="javascript://" onclick="getComenzi(1, {element: \'nume_client\', direction: \'' . ($sorting['element'] == 'nume_client' ? ($sorting['direction'] == 'asc' ? 'desc' : 'asc') : 'asc') . '\'})">Client</a></td>';
        $header .= '<td align="right" class="' . ($sorting['element'] == 'total_comanda' ? ($sorting['direction'] == 'asc' ? 'sortUp' : 'sortDown') : 'table_header') . '"><a href="javascript://" onclick="getComenzi(1, {element: \'total_comanda\', direction: \'' . ($sorting['element'] == 'total_comanda' ? ($sorting['direction'] == 'asc' ? 'desc' : 'asc') : 'asc') . '\'})" style="margin-right:20px;">Total comanda</a></td>';
        $header .= '<td align="right" class="table_header">Total taxe</td>';
        $header .= '<td align="center" class="table_header">Modalitate de plata</td>';
        $header .= '<td align="center" class="table_header">Status plata</td>';
        $header .= '<td align="center" class="table_header">Status comanda</td></tr>';
        
        $table = '';
        if(mysql_num_rows($comenzi)) {
            $i = 0;
            while ($row = mysql_fetch_assoc($comenzi)) {
                $table .= "<tr id=\"row_{$row['id']}\" onmouseover=\"showHide({$row['id']}, 'on')\" onmouseout=\"showHide({$row['id']}, 'off')\"" . (!$row['new'] ? ' class="viewed"' : '') . " style=\"height:20px\">";
                $table .= '<td align="center"><span id="span_' . $row['id'] . '" style="display: block; padding: 3px 0px">' . ($i+$dela+1) . '.</span><input type="checkbox" name="comenzi[]" id="check_' . $row['id'] . '" value="' . $row['id'] . '" onclick="selectDeselect(this, ' . $row['id'] . ');" style="display:none" /></td>';
                $table .= "<td align=\"center\">{$row['data']}</td>";
                $table .= "<td align=\"center\">{$row['cod_comanda']}</td>";
                $table .= "<td align=\"left\">{$row['nume_client']}</td>";
                $table .= "<td align=\"right\" style=\"padding-right:20px;\">" . number_format($row['total_comanda'], 2, ".", ",") . "</td>";
                $table .= "<td align=\"right\">" . number_format($row['total_taxe'], 2, ".", ",") . "</td>";
                $table .= "<td align=\"center\">" . ($row['mod_plata'] == 'op' ? 'OP' : 'Ramburs') . "</td>";
                $table .= "<td align=\"center\"><img src=\"images/icons/_{$row['status_plata']}.png\" width=\"16\" height=\"16\" alt=\"\" /></td>";
                $table .= "<td align=\"center\"><img src=\"images/icons/_{$row['status']}.png\" width=\"16\" height=\"16\" alt=\"\" /></td>";
                $table .= "</tr>";
                $i++;
            }
        }
        else {
            $table .= "<tr><td colspan=\"9\"><div style=\"text-align:center; padding:10px;\"><img src=\"images/icons/info.png\" width=\"25\" height=\"25\" class=\"valignMiddle\" /> Nicio comandă înregistrată.</div></td></tr>";
        }
        
        echo json_encode(array('header' => $header, 'table' => $table, 'listing' => $this->_listing($totalRezultate, $rezultatepepagina, $pagina, 'getComenzi')));
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
    
    private function _adresaCumparator($date) {
    	if($date['tip_client'] == "fizica") {
    		$adresa = $date['adresa'] . "; " . $date['oras'] . ($date['cod_postal'] ? ", {$date['cod_postal']}" : "") . "<br />" . (strpos($date['judet'], "Sector") === false ? 'Judet ' : "") . $date['judet'];
    	}
    	else {
    		$adresa = $date['adresa_sediu'] . "; " . $date['oras_sediu'] . ($date['cod_postal_sediu'] ? ", {$date['cod_postal_sediu']}" : "") . "<br />" . (strpos($date['judet_sediu'], "Sector") === false ? 'Judet ' : "") . $date['judet_sediu'];
    	}
    
    	return $adresa;
    }

    private function _adresaDestinatar($date) {
    	$adresa = $date['destinatar_adresa'] . "; " . $date['destinatar_oras'] . ($date['destinatar_cod_postal'] ? ", {$date['destinatar_cod_postal']}" : "") . "<br />" . (strpos($date['destinatar_judet'], "Sector") === false ? 'Judet ' : "") . $date['destinatar_judet'];
    
    	return $adresa;
    }
    
    public function comenzi_detaliiComanda ()
    {
        $sql = "UPDATE `comenzi` SET `new` = 0 WHERE `id` = {$_POST['comandaId']} LIMIT 1";
        mysql_query($sql, db_c());
        
        $sql = "SELECT co.*, cl.*, a.*, c.*, CONCAT(cl.nume, ' ', cl.prenume) AS `cumparator`, (co.total_fara_tva + co.total_tva) AS `totalComanda`, CONCAT(destinatar_nume, ' ', destinatar_prenume) AS `destinatar` FROM `comenzi` AS `co` RIGHT JOIN `clienti` AS `cl` ON co.id = cl.comanda_id LEFT JOIN `adrese` AS `a` ON cl.adresa_id = a.id LEFT JOIN `companii` AS `c` ON cl.companie_id = c.id WHERE co.id = {$_POST['comandaId']}";
        $data = mysql_fetch_assoc(mysql_query($sql, db_c()));
        
        $buffer = '<img src="images/spacer.gif" height="20" width="100%" alt="" />';
        $buffer .= '<div class="box blue_box">';
        $buffer .= '<div class="marginBottom clear"><label class="fLeft">&nbsp;&nbsp;Comanda ID:&nbsp;</label><strong class="fLeft">' . $data['cod_comanda'] . '</strong></div>';
        $buffer .= '<div class="marginBottom clear"><label class="fLeft">&nbsp;&nbsp;Cumpărător:&nbsp;</label><strong class="fLeft">' . ($data['tip_client'] == 'fizica' ? $data['cumparator'] : $data['denumire'] . '</strong>, CUI: <strong>' . $data['cod_fiscal'] . '</strong>, Reg. Com: <strong>' . $data['reg_com']) . '</strong></div>';
        $buffer .= '<div class="marginBottom clear"><label class="fLeft">&nbsp;&nbsp;Contact:&nbsp;</label><strong class="fLeft">' . ($data['tip_client'] == 'fizica' ? ($data['telefon'] ? $data['telefon'] . '; ' : '') . ($data['fax'] ? 'Fax: ' . $data['fax'] . '; ' : '') . ($data['email'] ? 'Email: ' . $data['email'] : '') :  $data['persoana_contact'] . ', ' . $data['email_sediu']) . '</strong></div>';
        $buffer .= '</div>';
        $buffer .= '<div class="marginBottom clear"><label class="fLeft">&nbsp;&nbsp;Total comandă:&nbsp;</label><strong class="fLeft">' . number_format($data['totalComanda'], 2, ',', '.') . ' RON</strong>&nbsp;&nbsp;&nbsp;(TVA: <strong>' . number_format($data['total_tva'], 2, ',', '.') . ' RON)</strong></div>';
        $buffer .= '<div class="marginBottom clear"><label class="fLeft">&nbsp;&nbsp;Modalitate de plată:&nbsp;</label><strong class="fLeft">' . ($data['mod_plata'] == 'op' ? 'Ordin de plată' : $data['mod_plata']) . ' </strong></div>';
        $buffer .= '<div class="marginBottom clear" style="margin-bottom:15px;"><label class="fLeft">&nbsp;&nbsp;Adresa cumpărător:&nbsp;</label><span style="float:left">' . $this->_adresaCumparator($data) . '</span></div>';
        $buffer .= '<div class="marginBottom clear"><label class="fLeft">&nbsp;&nbsp;Destinatar:&nbsp;</label><span style="float:left">' . ($data['destinatar_nume'] && $data['destinatar_prenume'] ? $data['destinatar'] : $data['denumire']) . '</span></div>';
        $buffer .= '<div class="marginBottom clear"><label class="fLeft">&nbsp;&nbsp;Adresa destinatar:&nbsp;</label><span style="float:left">' . $this->_adresaDestinatar($data) . '</span></div>';
        $buffer .= '<div class="clear"><img src="images/spacer.gif" height="10" width="100%" alt="" /></div>';
        
        $buffer .= '
        <div class="table_border" style="margin: 0 20px">
        <div align="center" id="products_header">
          <table width="100%" cellpadding="3" cellspacing="0">
            <colgroup><col width="25" /> <col /> <col width="60" /> <col width="80" /> <col width="100" /> </colgroup>
            <thead>
              <tr>
                <td class="table_header" align="center">#</td> <td class="table_header" align="left">Produs</td> <td class="table_header" align="center">Cantitate</td> <td class="table_header" align="right">Preţ</td> <td class="table_header" align="right" style="padding-right:20px">Valoare</td>
              </tr>
            </thead>
          </table>
        </div>
        <div id="products_content" align="center">
          <table width="640px" cellpadding="3" cellspacing=0>
            <colgroup><col width="25" /> <col /> <col width="60" /> <col width="80" /> <col width="80" /> </colgroup>
            <tbody>';
        
        $sql = "SELECT p.denumire, f.cantitate, f.pret FROM `facturi` AS `f` LEFT JOIN `produse` AS `p` ON f.produs_id = p.id WHERE `comanda_id` = {$_POST['comandaId']}";
        $result = mysql_query($sql, db_c());
        $i = 1;
        while($row = mysql_fetch_assoc($result)) {
            $buffer .= '<tr onmouseover="this.style.backgroundColor=\'#CCD9F2\'" onmouseout="this.style.backgroundColor=\'\'"><td align="center">' . $i . '.</td><td align="left">' . $row['denumire'] . '</td><td align="center">' . $row['cantitate'] . '</td><td align="right">' . number_format($row['pret'], 2, ',', '.') . '</td><td align="right">' . number_format($row['pret'] *  $row['cantitate'], 2, ',', '.') . '</td></tr>';
            $i++;
        }
        
        $buffer .= '
            </tbody>
          </table>
        </div>
        </div>
        <p class="center clear">
          <img src="images/spacer.gif" height="5" width="100%" alt="" />
          <input type="button" name="detaliiComanda_renunt" id="detaliiComanda_renunt" value="  Închide  " class="button_win" />
        </p>';
        
        echo $buffer;
    }
    
    public function comenzi_statusPlata ()
    {
        $sql = "UPDATE `comenzi` SET `new` = 0 WHERE `id` = {$_POST['comandaId']} LIMIT 1";
        mysql_query($sql, db_c());
        
        $sql = "UPDATE `comenzi` SET `status_plata` = '{$_POST['status']}'" . ($_POST['status'] == 'platita' ? ', `data_platii` = DATE(NOW()) ' : '') . " WHERE `id` = {$_POST['comandaId']} LIMIT 1";
        mysql_query($sql, db_c());
        
        $this->comenzi_getComenzi();
    }
    
    public function comenzi_statusComanda ()
    {
        $sql = "UPDATE `comenzi` SET `new` = 0 WHERE `id` = {$_POST['comandaId']} LIMIT 1";
        mysql_query($sql, db_c());
        
        $sql = "UPDATE `comenzi` SET `status` = '{$_POST['status']}'" . ($_POST['status'] == 'livrata' ? ', `data_livrarii` = DATE(NOW()) ' : '') . " WHERE `id` = {$_POST['comandaId']} LIMIT 1";
        mysql_query($sql, db_c());
        
        $this->comenzi_getComenzi();
    }    
    
    public function comenzi_stergeComenzi ()
    {
        $comenzi = json_decode(stripslashes($_POST['comenzi']), true);
        
        foreach ($comenzi as $comanda) {
            $sql = "SELECT `produs_id`, `cantitate` FROM `facturi` WHERE `comanda_id` = {$comanda}";
            $produse = mysql_query($sql, db_c());
            
            while($row = mysql_fetch_assoc($produse)) {
                $sql = "UPDATE `produse` AS `p` SET p.stoc_disponibil = p.stoc_disponibil + {$row['cantitate']}, p.stoc_rezervat = p.stoc_rezervat - {$row['cantitate']} WHERE `id` = {$row['produs_id']} LIMIT 1";
                mysql_query($sql, db_c()); 
            }
        }
        
        $sql = "DELETE FROM `comenzi` WHERE `id` IN (" . implode(', ', $comenzi) . ")";
        mysql_query($sql, db_c());
        
        $this->comenzi_getComenzi();
    }
}
?>