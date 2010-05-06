<?php
/**
 * Zend_Controller_Front Plugin
 *
 * @author Rares Vlasceanu
 * @version 1.0
 * @package MyShop
 * @subpackage Plugin
 */
class MyShop_Plugin_Init extends Zend_Controller_Plugin_Abstract
{
    protected $_view;

    /**
     * Called before Zend_Controller_Front enters its dispatch loop.
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        $this->_setViewAndLayout($request);
        $this->_setDbConnection();
        $this->_checkLogin();
        $this->_setMenuCategories();

        $this->_view->assign('basket', MyShop_Basket::getInstance());
    }

    /**
     * Set Layout decorator and Smarty base view
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return void
     */
    private function _setViewAndLayout($request)
    {
        //set the view renderer (Smarty based)
        $this->_view = new MyShop_Smarty();
        $this->_view->setBasePath('..\\application\\views\\scripts\\');
        $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer($this->_view);
        $viewRenderer->setViewBasePathSpec('views/scripts');
        $viewRenderer->setViewScriptPathSpec(':controller/:action.:suffix');
        $viewRenderer->setViewSuffix('html');

        //set layout
        Zend_Controller_Action_HelperBroker::addPrefix('MyShop_Helper');
        $layout = Zend_Controller_Action_HelperBroker::getStaticHelper('Layout');
        Zend_Controller_Action_HelperBroker::getStack()->offsetSet(-99, $layout);
        Zend_Controller_Action_HelperBroker::getStack()->offsetSet(1, $viewRenderer);
        
        if($request->isXmlHttpRequest()) {
            $layout->disable();
        }
    }

    /**
     * Set Doctrine Lazy Connection
     *
     * @return void
     */
    private function _setDbConnection()
    {
        $config = MyShop_Config::getInstance()->load('db.ini');
        $pdoUri = "mysql://{$config->database->mysql->user}"
                . ":{$config->database->mysql->pass}@{$config->database->mysql->host}"
                . "/{$config->database->mysql->db}";
        $conn = Doctrine_Manager::connection($pdoUri);
        $conn->setAttribute(Doctrine::ATTR_USE_NATIVE_ENUM, true);
        Doctrine_Manager::getInstance()->setAttribute(Doctrine_Core::ATTR_AUTOLOAD_TABLE_CLASSES, true);

        if($config->database->mysql->charset) {
            $conn->setCharset($config->database->mysql->charset);
        }
        $config->loadDatabaseConfig();
    }

    /**
     * Chek user's login state
     *
     * @return void
     */
    private function _checkLogin()
    {
        $acl = Zend_Controller_Action_HelperBroker::getStaticHelper('Acl');
        if($acl->getRole() != 'guest') {
            $this->_view->assign('loggedIn', true);
            MyShop_Basket::getInstance()->setUserId($_SESSION['profile']['id']);
        }
    }

    /**
     * Get and parse menu categories
     *
     * @return void
     */
    private function _setMenuCategories()
    {
        $sql = Doctrine_Query::create();
        $sql->select('*');
        $sql->from('Categorii');
        $sql->orderBy('parent_id asc, ordine asc');
        $categories = $sql->execute(array(), Doctrine::HYDRATE_ARRAY);

        $productCategories = array();
        foreach ($categories as $category) {
            if(!$category['parent_id']) {
                $productCategories[$category['id']] = $category;
            }
            else {
                if(array_key_exists($category['parent_id'], $productCategories)) {
                    $productCategories[$category['parent_id']]['subcategories'][$category['id']] = $category;
                }
            }
        }

        $this->_view->assign('categories', $productCategories);
    }
}