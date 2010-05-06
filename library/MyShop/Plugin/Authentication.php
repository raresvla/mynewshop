<?php
/**
 * This plugin checks the authentication cookie.
 * If found, username and password are loaded from it.
 *
 * @author Rares Vlasceanu
 * @version 1.0
 * @package MyShop
 * @subpackage Plugin
 */
class MyShop_Plugin_Authentication extends Zend_Controller_Plugin_Abstract
{
    const AUTH_COOKIE = 'LOGIN_AUTH_COOKIE';
    const EXPIRE_TIME = 2592000;

    /**
     * This plugin looks for authentication cookie.
     * If found and user is not logged in, perform login.
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        $aclHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('Acl');
        if($aclHelper->getRole() != MyShop_Helper_Acl::ROLE_GUEST) {
            return;
        }

        //forward request through login action
        if($this->_loadDataFromCookie()) {
            $request->setParam('originalRequest', array(
                'controller' => $request->getParam('controller'),
                'action' => $request->getParam('action')
            ));
            $request->setControllerName('cont');
            $request->setActionName('valideaza-autentificare');
            $request->setDispatched(false);
        }
    }

    /**
     * Try to load login data from authentication cookie
     *
     * @return boolean
     */
    private function _loadDataFromCookie()
    {
        if(!isset($_COOKIE[self::AUTH_COOKIE])) {
            return false;
        }

        //load cookie information
        list($username, $sha) = explode (';', $_COOKIE[self::AUTH_COOKIE]);
        $password = $this->_getUserPassword($username);

        //check data integrity
        if(sha1($username . $password) != $sha) {
            self::unsetAuthCookie();
            return false;
        }

        $this->getRequest()->setParam('login', array('username' => $username, 'password' => $password));
        return true;
    }

    /**
     * Get user password, using username as key
     *
     * @param string $username
     * @return string
     */
    private function _getUserPassword($username)
    {
        $sql = Doctrine_Query::create();
        $sql->select('password');
        $sql->from('Membri');
        $sql->where('username = ?', $username);
        $user = $sql->fetchOne(array(), Doctrine::HYDRATE_ARRAY);
        if(empty($user)) {
            return false;
        }

        return $user['password'];
    }

    /**
     * Save / modify authentication cookie data
     *
     * @param string $username
     * @param string $password
     * @param integer $ttl
     */
    public static function setAuthCookie($username, $password, $ttl = null)
    {
        if(empty($ttl)) {
            $ttl = time() + self::EXPIRE_TIME;
        }
        setcookie(self::AUTH_COOKIE, $username . ';' . sha1($username . $password), $ttl, '/');
    }

    /**
     * Delete authentication cookie
     */
    public static function unsetAuthCookie()
    {
        setcookie(self::AUTH_COOKIE, '', time() - 3600, '/');
    }
}