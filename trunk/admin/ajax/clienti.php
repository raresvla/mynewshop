<?php
class AjaxActionClienti
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
    
    public function clienti_getClienti()
    {
        $rezultatepepagina = 20;
        $pagina = isset($_POST['pagina']) ? $_POST['pagina'] : 1;
        $dela = ($pagina - 1) * $rezultatepepagina;
        if(empty($_POST['sorting'])) {
            $sorting = array('element' => 'numele', 'direction' => 'asc');
        }
        else {
            $sorting = json_decode(stripslashes($_POST['sorting']), true);
        }
        
        $sql = "SELECT m.*, COUNT(c.id) FROM `membri` AS `m` LEFT JOIN `comenzi` AS `c` ON m.id = c.membru_id GROUP BY m.id";
        $totalRezultate = mysql_num_rows(mysql_query($sql, db_c()));
        
        $sql = "SELECT m.*, CONCAT(m.nume, ' ', m.prenume) AS `numele`, DATE_FORMAT(m.ultimul_login, '%d/%m/%Y') AS `ult_login`, COUNT(c.id) AS `comenzi` FROM `membri` AS `m` LEFT JOIN `comenzi` AS `c` ON m.id = c.membru_id GROUP BY m.id ORDER BY `{$sorting['element']}` " . $sorting['direction'] . " LIMIT $dela, $rezultatepepagina";
        $clienti = mysql_query($sql, db_c());
        
        $header = '<tr><td align="center" class="table_header">#</td><td align="left" class="' . ($sorting['element'] == 'numele' ? ($sorting['direction'] == 'asc' ? 'sortUp' : 'sortDown') : 'table_header') . '"><a href="javascript://" onclick="getClienti(1, {element: \'numele\', direction: \'' . ($sorting['element'] == 'numele' ? ($sorting['direction'] == 'asc' ? 'desc' : 'asc') : 'asc') . '\'})">Numele şi prenumele</a></td>';
        $header .= '<td align="left" class="table_header">Contact</td>';
        $header .= '<td align="center" class="' . ($sorting['element'] == 'ultimul_login' ? ($sorting['direction'] == 'asc' ? 'sortUp' : 'sortDown') : 'table_header') . '"><a href="javascript://" onclick="getClienti(1, {element: \'ultimul_login\', direction: \'' . ($sorting['element'] == 'ultimul_login' ? ($sorting['direction'] == 'asc' ? 'desc' : 'asc') : 'desc') . '\'})">Ultimul login</a></td>';
        $header .= '<td align="center" class="' . ($sorting['element'] == 'comenzi' ? ($sorting['direction'] == 'asc' ? 'sortUp' : 'sortDown') : 'table_header') . '"><a href="javascript://" onclick="getClienti(1, {element: \'comenzi\', direction: \'' . ($sorting['element'] == 'comenzi' ? ($sorting['direction'] == 'asc' ? 'desc' : 'asc') : 'desc') . '\'})">Comenzi</a></td>';
        $header .= '<td align="center" class="table_header">Activ</td></tr>';
        
        $table = '';
        if(mysql_num_rows($clienti)) {
            $i = 0;
            while ($row = mysql_fetch_assoc($clienti)) {
                $table .= "<tr id=\"row_{$row['id']}\" onmouseover=\"showHide({$row['id']}, 'on')\" onmouseout=\"showHide({$row['id']}, 'off')\" style=\"height:20px\">";
                $table .= '<td align="center"><span id="span_' . $row['id'] . '">' . ($i+$dela+1) . '.</span><input type="checkbox" name="clienti[]" id="check_' . $row['id'] . '" value="' . $row['id'] . '" onclick="selectDeselect(this, ' . $row['id'] . ');" style="display:none" /></td>';
                $table .= "<td>{$row['nume']} {$row['prenume']}" . ($row['cnp'] ? "<br /><span class=\"subtitle\">CNP: {$row['cnp']}</span></td>" : '');
                $table .= "<td align=\"left\" >{$row['telefon']}<br />{$row['email']}</td>";
                $table .= "<td align=\"center\">{$row['ult_login']}</td>";
                $table .= "<td align=\"center\">{$row['comenzi']}</td>";
                $table .= "<td align=\"center\">" . ($row['status'] == 'activ' ? '<img src="images/icons/ok.png" width="16" height="16" alt="" />' : '<img src="images/icons/lipsa.png" width="16" height="16" alt="" />') . "</td>";
                $table .= "</tr>";
                $i++;
            }
        }
        else {
            $table .= "<tr><td colspan=\"6\"><div style=\"text-align:center; padding:10px;\"><img src=\"images/icons/info.png\" width=\"25\" height=\"25\" class=\"valignMiddle\" /> Niciun client înregistrat.</div></td></tr>";
        }
        
        echo json_encode(array('header' => $header, 'table' => $table, 'listing' => $this->_listing($totalRezultate, $rezultatepepagina, $pagina, 'getClienti')));
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
    
    public function clienti_suspendaClienti ()
    {
        $clienti = json_decode(stripslashes($_POST['clienti']), true);
        
        $sql = "UPDATE `membri` SET `status` = 'inactiv' WHERE `id` IN (" . implode(', ', $clienti) . ")";
        mysql_query($sql, db_c());
        
        $this->clienti_getClienti();
    }
    
    public function clienti_activeazaClienti ()
    {
        $clienti = json_decode(stripslashes($_POST['clienti']), true);
        
        $sql = "UPDATE `membri` SET `status` = 'activ' WHERE `id` IN (" . implode(', ', $clienti) . ")";
        mysql_query($sql, db_c());
        
        $this->clienti_getClienti();
    }
    
    public function clienti_stergeClienti ()
    {
        $clienti = json_decode(stripslashes($_POST['clienti']), true);
        
        $sql = "DELETE FROM `membri` WHERE `id` IN (" . implode(', ', $clienti) . ")";
        mysql_query($sql, db_c());
        
        $this->clienti_getClienti();
    }
}
?>