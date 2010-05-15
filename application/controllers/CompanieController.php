<?php
/**
 * Companie Controller
 *
 * @author Rares Vlasceanu
 */
class CompanieController extends Zend_Controller_Action
{
    public function init()
    {
        $this->view->assign('section', $this->_getParam('action'));
    }

    /**
     * Default action
     */
    public function indexAction()
    {
        $this->_forward('contact');
    }

    /**
     * Display informations page
     */
    public function informatiiAction()
    {
    }

    /**
     * Display contact page
     */
    public function contactAction()
    {
        
    }
}