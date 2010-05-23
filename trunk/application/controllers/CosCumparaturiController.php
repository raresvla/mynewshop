<?php
/**
 * CosCumparaturi Controller
 *
 * @author Rares Vlasceanu
 */
class CosCumparaturiController extends Zend_Controller_Action
{
    /**
     * Actions init
     */
    public function init()
    {
        $this->_helper->Layout->addBreadCrumb('Coş de cumpărături', '/cos-cumparaturi');
        $this->view->assign('title', 'Cos de cumparaturi');
    }

    /**
     * Display basket status
     */
    public function indexAction()
    {
        $backUrl = $_SERVER['HTTP_REFERER'];
        if(empty($backUrl) || strpos($backUrl, 'cos-cumparaturi') !== false) {
            $backUrl = '/';
        }
        $this->view->assign('backUrl', $backUrl);
    }

    /**
     * Add new product in basket
     */
    public function adaugaAction()
    {
        $basket = MyShop_Basket::getInstance();
        $basket->add($this->_getParam('pid'));
        $this->_redirect('/cos-cumparaturi');
    }

    /**
     * Empty basket
     */
    public function golesteCosulAction()
    {
        MyShop_Basket::getInstance()->removeAll();
        $this->_redirect('/cos-cumparaturi');
    }

    /**
     * Update basket content
     */
    public function actualizeazaAction()
    {
        $basket = MyShop_Basket::getInstance();
        $toRemove = $this->_getParam('remove', array());
        $toUpdate = array_diff_key(
            $this->_getParam('quantity', array()),
            array_flip($toRemove)
        );

        foreach($toUpdate as $productId => $quantity) {
            if($quantity < 1) {
                $toRemove[] = $productId;
                continue;
            }
            $basket[$productId] = $quantity;
        }
        foreach($toRemove as $productId) {
            $basket->remove($productId);
        }

        $this->_redirect('/cos-cumparaturi');
    }
}