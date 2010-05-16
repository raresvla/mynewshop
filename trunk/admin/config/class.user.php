<?php
/**
 * Clasa User
 * 
 * @author Rares Vlasceanu
 * @category Classes
 * @version 1.0
 */
class User
{
    private $_loginStatus = 0;
    private $_username = null;
    private $_parola = null;
    private $_userId = null;
    private $_nume = null;
    private $_tip = null;
    private $_scriptAccess = array('index.php' , 'ajax.php', 'logout.php' , 'change_password.php');
    private $_rights = array();
    public $msg = null;
    
    private static $_cookieCount = "__incercari";
    private static $_incercari = 5;
    private static $_mesaje = array('loginError' => 'Accesul în această zonă a fost blocat datorită depăşirii numărului de autentificări permise!' , 'zoneError' => 'Această zonă nu vă este accesibilă, conform drepturilor asociate contului dumneavoastră! Contactaţi administratorul pentru detalii.');
    
    /**
     * Constructorul clasei.
     * Primeste ca parametru, atunci cand este apelat in contextul trimiterii formularului de login, datele din $_POST.
     *
     * @param array $post
     */
    public function __construct ($post = null)
    {
        if ($this->_loginPermitted()) {
            if ($post) {
                //formular de logare trimis
                $this->_countSubmit();
                $this->_username = trim($post['username']);
                $this->_username = (! get_magic_quotes_gpc() ? addslashes($this->_username) : $this->_username);
                $this->_parola = md5($post['password']);
                $this->_checkLogin(true);
            } else {
                if (isset($_SESSION['username']) && isset($_SESSION['parola'])) {
                    //daca logat -> datele din cookie-ul de sesiune
                    $this->_username = $_SESSION['username'];
                    $this->_parola = $_SESSION['parola'];
                    $this->_checkLogin();
                }
            }
        } else {
            $this->_dispatchError('loginError');
        }
    }
    
    /**
     * Verifica daca se permite procesarea datelor.
     * Pentru mai mult de <<$_incercari>> incercari nereusite de login, se blocheaza accesul.
     *
     * @return boolean
     */
    private function _loginPermitted ()
    {
        return $_COOKIE[self::$_cookieCount] < self::$_incercari ? true : false;
    }
    
    /**
     * Aceasta metoda preia din baza de date, in array-ul "_scriptAccess", numele tuturor scripturilor
     * pe care userul le poate accesa.
     */
    private function _getPermitedScripts ()
    {
        $sql = "SELECT `link`, `pagini_derivate` FROM `admin_drepturi` AS `d` LEFT JOIN `admin_zone` AS `z` ON d.zone_id = z.id WHERE d.user_id = '{$this->_userId}'" . ($this->_tip == "powerUser" ? " UNION SELECT `link`, `pagini_derivate` FROM `admin_zone` WHERE `powerUserZone` = '1'" : "");
        $result = mysql_query($sql, db_c());
        while ($row = mysql_fetch_assoc($result)) {
            array_push($this->_scriptAccess, $row['link']);
            foreach (explode(",", $row['pagini_derivate']) as $key => $page) {
                if ($page)
                    array_push($this->_scriptAccess, $page);
            }
        }
    }
    
    /**
     * Verifica daca scriptul deschis spre vizualizare se afla in lista celor permise conform
     * drepturilor asociate contului utilizatorului.
     *
     * @return boolean
     */
    private function _checkAccessPermitted ()
    {
        $this->_getPermitedScripts();
        if (array_search(basename($_SERVER['PHP_SELF']), $this->_scriptAccess) === false) {
            $ip = (getenv(HTTP_X_FORWARDED_FOR) ? getenv(HTTP_X_FORWARDED_FOR) : getenv(REMOTE_ADDR));
            $sql = "INSERT INTO `_log` (`date`, `activity`, `info`) VALUES ('$date', 'admin_access_not_granted', 'user={$this->_username};IP={$ip};script=" . basename($_SERVER['PHP_SELF']) . "')";
            mysql_query($sql, db_c());
            
            $this->_dispatchError('zoneError');
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * Metoda determina afisarea unui mesaj de eroare in cazul depasirii numarului de autentificari permise
     * sau in cazul lipsei accesului pentru o anumita zona / pagina.
     *
     * @param string $errorCode
     * @return void
     */
    private function _dispatchError ($errorCode)
    {
        echo str_replace('{$mesaj}', self::$_mesaje[$errorCode], file_get_contents("_accesBlocat.html"));
        exit();
    }
    
    /**
     * Contorizeaza fiecare incercare de login intr-un cookie retinut local.
     *
     */
    private function _countSubmit ()
    {
        if (! isset($_COOKIE[self::$_cookieCount])) {
            setcookie(self::$_cookieCount, 1, time() + 3600, "/");
        } else {
            setcookie(self::$_cookieCount, $_COOKIE[self::$_cookieCount] + 1, time() + 3600, "/");
        }
    }
    
    /**
     * Contorizeaza fiecare login reusit. Retine, intr-o tabela, data si IP-ul host-ului de unde s-a efectuat loginul.
     *
     */
    private function _countLogin ()
    {
        $date = date("Y-m-d H:i:s");
        $sql = "UPDATE `admin` SET `last_login` = '$date', `number_logins` = `number_logins` + 1 WHERE `username` = '{$this->_username}'";
        mysql_query($sql, db_c());
        
        $ip = (getenv(HTTP_X_FORWARDED_FOR) ? getenv(HTTP_X_FORWARDED_FOR) : getenv(REMOTE_ADDR));
        $sql = "INSERT INTO `_log` (`date`, `activity`, `info`) VALUES ('$date', 'admin_login', 'user={$this->_username};IP={$ip}')";
        mysql_query($sql, db_c());
    }
    
    /**
     * Sterge cookie-ul pentru contorizarea incercarilor de logare si redirecteaza catre <<Home Page>>
     *
     */
    private function _redirect ()
    {
        global $CALE_VIRTUALA_SERVER;
        
        setcookie(self::$_cookieCount, "", time() - 3600, "/");
        header("Location: " . $CALE_VIRTUALA_SERVER);
        die();
    }
    
    /**
     * Verifica daca informatiile furnizate corespund celor din DB.
     * Se apeleaza cu parametru de tip bool si valoarea true atunci cand se proceseaza datele din formular (pentru retinere informatii in cookie-ul de sesiune). 
     *
     * @param bool $firstLogin
     */
    private function _checkLogin ($firstLogin = false)
    {
        if ($this->_username && $this->_parola) {
            $sql = "SELECT `id`, `parola`, CONCAT(`nume`, ' ', `prenume`) AS `nume`, `tip` FROM `admin` WHERE `username` = '{$this->_username}' AND `id` > 0";
            $data = mysql_fetch_assoc(mysql_query($sql, db_c()));
            if (! sizeof($data) || ($data['parola'] !== $this->_parola)) {
                $this->_loginStatus = 0;
                unset($_SESSION['username']);
                unset($_SESSION['parola']);
                if ($firstLogin) {
                    $this->msg = "Username invalid sau parola introdusă greşit!";
                } else {
                    $this->msg = "Introduceţi numele de utilizator şi parola pentru acces!";
                }
            } else {
                $this->_loginStatus = 1;
                $this->_userId = $data['id'];
                $this->_tip = $data['tip'];
                $this->_nume = $data['nume'];
                if ($firstLogin) {
                    $_SESSION['username'] = $this->_username;
                    $_SESSION['parola'] = $this->_parola;
                    $this->_countLogin();
                    $this->_redirect();
                }
                else {
                    if($this->_checkAccessPermitted()) {
                        $this->_getRights();
                    }
                }
            }
        } else {
            $this->_loginStatus = 0;
        }
    }
    
    /**
     * Aceasta metoda inregistreaza in array-ul '_rights' drepturile pe care le are utilizatorul, pentru fiecare sectiune in parte
     * 
     * @return null
     */
    private function _getRights ()
    {
        $sql = "SELECT * FROM `admin_zone` AS `z` LEFT JOIN `admin_drepturi` AS `d` ON (d.zone_id = z.id) WHERE d.user_id = '{$this->_userId}' AND `afisat` = 1";
        $result = mysql_query($sql, db_c());
        
        while ($row = mysql_fetch_assoc($result)) {
            $this->_rights[strtolower(str_replace('.php', '', $row['link']))] = array('edit' => $row['edit'] , 'insert' => $row['insert'] , 'delete' => $row['delete']);
        }
    }
    
    /**
     * Aceasta metoda verifica daca o anumite actiune asociata sectiunii {$section} este disponibila utilizatorului curent.
     *
     * @param string $section
     * @param string $right
     * @return boolean
     */
    public function hasRight ($section, $right)
    {
        return (boolean) $this->_rights[strtolower($section)][$right];
    }
    
    /**
     * Aceasta metoda returneaza un array cu drepturile userului pentru fiecare sectiune
     *
     * @return array
     */
    public function getAllRights ()
    {
        if(!$this->_loginStatus) {
            return false;
        }
        return $this->_rights;
    }
    
    /**
     * Printeaza meniul, in functie de zonele la care are acces user-ul
     *
     * @param string $currentSection
     * @return string
     */
    public function printMenu ($currentSection)
    {
        $menu = null;
        $sql = "(SELECT 'Home' AS zona, 'home.png' AS icon, 'index.php' AS link, 'index.php' AS scripts) UNION (SELECT z.zona, z.icon, z.link, CONCAT(z.link, IF(z.pagini_derivate, CONCAT(',', z.pagini_derivate), '')) AS scripts FROM `admin_drepturi` AS `d` LEFT JOIN `admin_zone` AS `z` ON d.zone_id = z.id WHERE d.user_id = '{$this->_userId}' AND `afisat` = 1)" . ($this->_tip == "powerUser" ? " UNION (SELECT `zona`, `icon`, `link`, CONCAT(`link`, IF(`pagini_derivate`, CONCAT(',', `pagini_derivate`), '')) AS `scripts` FROM `admin_zone` WHERE `powerUserZone` = 1  AND `afisat` = 1)" : "");
        $result = mysql_query($sql, db_c());
        while ($row = mysql_fetch_assoc($result)) {
            $scripts = explode(",", $row['scripts']);
            $icon = @getimagesize("images/menu/{$row['icon']}");
            $classOff = (in_array($currentSection, $scripts) ? 'selected' : 'off');
            $menu .= ($menu ? "\n\t\t" : "\t\t") . '<td align="center" class="menu_' . $classOff . '" onmouseover="this.className=\'menu_on\'" onmouseout="this.className=\'menu_' . $classOff . '\'" onclick="window.location.href=\'' . $CALE_VIRTUALA_SERVER . $row['link'] . '\'"><img src="images/menu/' . $row['icon'] . '" ' . $icon[3] . ' style="vertical-align: middle" /> ' . $row['zona'] . '</td>';
        }
        return '<table border="0" cellspacing="2" cellpadding="0" align="center">' . "\n\t    <tr>\n" . $menu . '</tr></table>';
    }
    
    /**
     * Returneaza status-ul userului (logat / nelogat)
     *
     * @return integer
     */
    public function loginStatus ()
    {
        return $this->_loginStatus;
    }
    
    /**
     * Returneaza numele de utilizator
     *
     * @return string
     */
    public function getUsername ()
    {
        return $this->_username;
    }
    
    /**
     * Returneaza ID-ul utilizatorului
     *
     * @return integer
     */
    public function getUserId ()
    {
        return $this->_userId;
    }
    
    /**
     * Functia folosita la delogare, sterge informatiile retinute in cookie-uri.
     */
    public function logOff ()
    {
        unset($_SESSION['username']);
        unset($_SESSION['parola']);
        $_SESSION = array();
        session_destroy();
    }
}
?>