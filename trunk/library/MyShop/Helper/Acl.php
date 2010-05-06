<?php
/**
 * ACL helper class
 *
 * @author Rares Vlasceanu
 * @version 1.0
 * @package MyShop
 * @subpackage Helper
 */
class MyShop_Helper_Acl extends Zend_Controller_Action_Helper_Abstract
{   
    /**
     * @var Zend_Acl
     */
    private $_acl;
    private $_lastMessage;

    const ROLE_GUEST = 'guest';
    const ROLE_MEMBER = 'member';
    const ROLE_ADMINISTRATOR = 'administrator';

    /**
     * Class constructor.
     * Initialises the Zend_Acl object.
     */
    public function __construct()
    {
        $this->_acl = new Zend_Acl();
        $this->_createDefaultRoles();
        $this->_lastMessage = &$_SESSION['lastACLMessage'];
    }

    /**
     * This method creates the default roles
     */
    protected function _createDefaultRoles()
    {
        $this->_acl->addRole(new Zend_Acl_Role(self::ROLE_GUEST));
        $this->_acl->addRole(new Zend_Acl_Role(self::ROLE_MEMBER), self::ROLE_GUEST);
        $this->_acl->addRole(new Zend_Acl_Role(self::ROLE_ADMINISTRATOR), self::ROLE_MEMBER);
    }

    /**
     * Allow only the role specified here
     *
     * @param string $allowedRole
     */
    public function allow($allowedRole, $message = 'Not allowed!')
    {
        if(!$this->_acl->hasRole($allowedRole)) {
            $this->_acl->addRole(new Zend_Acl_Role($allowedRole));
        }

        $this->_acl->allow($allowedRole, null, $this->_getResource());
        $this->_isAllowed($message);
    }

    /**
     * Get resource to be accessed
     *
     * @return string
     */
    protected function _getResource()
    {
        return $this->_actionController->getRequest()->getParam('action');
    }

    /**
     * This method checks if the current user can access a certain section
     *
     * @param unknown_type $message
     */
    protected function _isAllowed($message)
    {
        $role = $this->getRole();
        if(!$this->_acl->hasRole($role)) {
            $this->_acl->addRole(new Zend_Acl_Role($role));
        }

        if (!$this->_acl->isAllowed($role, null, $this->_getResource())) {
            $this->restricted($message);
        }
    }

    /**
     * Getter for the user role
     *
     * @return string
     */
    public function getRole()
    {
        if(isset($_SESSION['profile'])) {
            return self::ROLE_MEMBER;
        }

        return self::ROLE_GUEST;
    }

    /**
     * This method forward the request to the error/restricted action
     * Mimics the behavior of the final protected _forward function of the controller
     */
    public function restricted($message)
    {
        $this->_lastMessage = $message;
        $this->_actionController->getHelper('Redirector')->goToUrl('/login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    }

    /**
     * Get last ACL message
     *
     * @return string
     */
    public function getLastMessage()
    {
        $message = $this->_lastMessage;
        $this->_lastMessage = null;

        return $message;
    }

    /**
     * Test if user is logged in
     *
     * @return boolean
     */
    public function loggedIn()
    {
        return $this->getRole() == self::ROLE_MEMBER;
    }
}
