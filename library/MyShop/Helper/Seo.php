<?php
/**
 * Search Engines Optimization Helper
 *
 * @package MyShop
 * @subpackage Helper
 * @author Rares Vlasceanu
 * @version 1.0
 *
 * @property-read array $breadCrumbs
 * @property string $title
 * @property string $description
 */
class MyShop_Helper_Seo extends Zend_Controller_Action_Helper_Abstract
{
    private $_data = array();

    /**
     * Class constructor
     */
    public function  __construct()
    {
        $this->_data = array(
            'breadCrumbs' => array(),
            'title' => null,
            'description' => null,
            'keywords' => array()
        );
    }

    /**
     * Post dispatch hook
     */
    public function postDispatch()
    {
        if(!$this->getActionController()) {
            return;
        }
        if(!$this->getRequest()->isDispatched()) {
            return false;
    	}

        $view = $this->getActionController()->view;
        $view->assign('breadcrumbs', $this->breadCrumbs);
        $view->assign('title', $this->_buildTitle());
        //$view->assign('description', $this->_buildDescription());
        //$view->assign('keywords', $this->_buildKeywords());
    }

    /**
     * Set attribute value
     *
     * @param string $name
     * @param mixed $value
     */
    public function  __set($name, $value)
    {
        if(!isset($this->_data[$name])) {
            return;
        }

        $this->_data[$name] = $value;
    }
    
    /**
     * Get attribute value
     *
     * @param string $name
     */
    public function  __get($name)
    {
        if(!isset($this->$name)) {
            return null;
        }
        
        return $this->_data[$name];
    }

    /**
     * Check if attribute is set
     *
     * @param string $name
     * @return boolean
     */
    public function  __isset($name)
    {
        return isset($this->_data[$name]);
    }

    /**
     * Add new breadcrumb item
     *
     * @param string $pageTitle
     * @param string $href
     * @return MyShop_Helper_Layout
     */
    public function addBreadcrumb($pageTitle, $href = '/', $mode = 'push')
    {
        $item = array(
            'href' => $href,
            'title' => $pageTitle
        );
        $mode = 'array_' . $mode;
        call_user_func_array($mode, array(&$this->_data['breadCrumbs'], $item));
        return $this;
    }

    /**
     * Build page title
     */
    private function _buildTitle()
    {
        if(isset($this->title)) {
            return $this->title;
        }

        $data = array_slice($this->breadCrumbs, -2, 2);
        foreach($data as &$item) {
            $item = $item['title'];
        }
        return implode(' › ', $data);
    }
}