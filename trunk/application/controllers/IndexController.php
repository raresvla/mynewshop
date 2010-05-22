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
        $this->_helper->Layout->addBreadcrumb('Home');
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
        $response = $this->getResponse();
        $response->setHeader('Content-Type', 'text/html; charset=utf-8', true);
        echo '<pre>';
        print_r($_SESSION);
        print_r($_SERVER);
        print_r($_COOKIE);
        echo '</pre>';
        $response->sendHeaders();
        die();
    }

    public function generateModelsAction()
    {
        $res = Doctrine_Core::generateModelsFromDb('../application/models', array('myshop'), array('generateTableClasses' => true));
        print_r($res);
        die();
    }
}

