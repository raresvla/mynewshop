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
    
    public function setari_editeazaSetarea()
    {
        foreach ($_POST as $key => $value) {
            ${$key} = addslashes($value);
        }
        $sql = "UPDATE `config` SET `descriere` = '{$descriere}', `valoare` = '{$valoare}' WHERE `id` = {$id} LIMIT 1";
        mysql_query($sql, db_c());
    }   
}
?>