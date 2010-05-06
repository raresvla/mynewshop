<?php
/**
 * Index Controller
 *
 * @author Rares Vlasceanu
 */
class IndexController extends Zend_Controller_Action
{
    public function init()
    {
        $this->view->assign('section', $this->_getParam('controller'));
    }

    /**
     * Default action
     */
    public function indexAction()
    {
        
    }

    /**
     * Example action
     */
    public function exampleAction()
    {

    }

    /**
     * Debug action
     */
    public function dumpAction()
    {
        echo '<pre>';
        print_r($_SESSION);
        print_r($_SERVER);
        print_r($_COOKIE);
        echo '</pre>';
        die();
    }

    public function generateModelsAction()
    {
        $res = Doctrine_Core::generateModelsFromDb('../application/models', array('myshop'), array('generateTableClasses' => true));
        print_r($res);
        die();
    }
}

